<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceBatch;

/**
 * The single, shared extraction pipeline used by BOTH the CLI command and the
 * web background job. Default flow: free page count → split → one AI read per
 * page → group by invoice number → persist rows → grand total.
 */
class InvoicePipeline
{
    private int $inTokens = 0;

    private int $outTokens = 0;

    public function __construct(
        private PdfPageSplitter $splitter,
        private InvoiceExtractionService $service,
        private PdfPageRasterizer $rasterizer,
    ) {}

    /**
     * Run the pipeline for a batch. $onProgress($done,$total) is called as pages finish
     * so the UI/CLI can show progress. Returns the number of invoices stored.
     */
    public function run(InvoiceBatch $batch, string $pdfPath, ?string $model = null, ?callable $onProgress = null, string $mode = 'whole'): int
    {
        $model = $model ?: config('services.gemini.default_model');

        // Keep a copy of the source PDF with the batch.
        $pagesDir = public_path('uploads/invoices/pages/batch_'.$batch->id);
        if (! is_dir($pagesDir)) {
            @mkdir($pagesDir, 0775, true);
        }
        $savedPdf = $pagesDir.'/source.pdf';
        @copy($pdfPath, $savedPdf);
        $savedRel = str_replace(public_path().'/', '', $savedPdf);

        $this->inTokens = 0;
        $this->outTokens = 0;

        if ($mode === 'whole') {
            $made = $this->wholeDocument($batch, $pdfPath, $savedRel, $model);
        } else {
            $made = $this->perPage($batch, $pdfPath, $pagesDir, $model, $onProgress, $mode === 'grouped');
        }

        // Flag repeated invoice numbers (within this batch and against earlier ones) for review.
        $this->flagDuplicates($batch);

        $batch->update([
            'processed_pages' => $batch->invoices()->count(),
            'status' => 'done',
            'input_tokens' => $this->inTokens,
            'output_tokens' => $this->outTokens,
            'est_cost_usd' => round($this->service->costUsd($this->inTokens, $this->outTokens), 5),
        ]);
        $batch->recomputeGrandTotal();

        return $made;
    }

    private function perPage(InvoiceBatch $batch, string $pdfPath, string $pagesDir, string $model, ?callable $onProgress, bool $group): int
    {
        // Spec 002 FR-101 — a directly-uploaded image (JPG/PNG/WEBP) is already a
        // single "page": copy it into the batch dir and use it as-is, no rasterize/split.
        if (preg_match('/\.(png|jpe?g|webp)$/i', $pdfPath)) {
            $dest = $pagesDir.'/page-1.'.strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION));
            @copy($pdfPath, $dest);
            $pages = [is_file($dest) ? $dest : $pdfPath];
        } else {
            // Prefer rasterizing each page to a PNG (renders reliably on every model AND
            // gives a per-invoice image attachment). Fall back to FPDI sub-PDF split, then
            // to whole-document mode if neither works.
            try {
                $pages = $this->rasterizer->rasterize($pdfPath, $pagesDir);
            } catch (\Throwable $e) {
                try {
                    $pages = $this->splitter->split($pdfPath, $pagesDir);
                } catch (PdfSplitException $e2) {
                    return $this->wholeDocument($batch, $pdfPath, str_replace(public_path().'/', '', $pagesDir.'/source.pdf'), $model);
                }
            }
        }

        [$light, $hard, $escalate] = $this->thinkingTiers();
        $total = count($pages);
        $batch->update(['total_pages' => $total, 'status' => 'processing']);

        $rows = [];
        foreach ($pages as $i => $pagePath) {
            $pageNo = $i + 1;
            $rel = str_replace(public_path().'/', '', $pagePath);
            $t0 = microtime(true);
            try {
                // Pass 1 — cheap. Escalate THIS page to deeper thinking only if it's a bad scan.
                $data = $this->service->extractInvoice($pagePath, $model, $light);
                $this->inTokens += (int) ($data['_in'] ?? 0);
                $this->outTokens += (int) ($data['_out'] ?? 0);

                if ($escalate && $hard !== $light && ! empty($data['needs_review'])) {
                    $deep = $this->service->extractInvoice($pagePath, $model, $hard);
                    $this->inTokens += (int) ($deep['_in'] ?? 0);
                    $this->outTokens += (int) ($deep['_out'] ?? 0);
                    $deep['_escalated'] = true;
                    if (($deep['invoice_number'] ?? null) !== ($data['invoice_number'] ?? null)) {
                        $deep['needs_review'] = true;
                        $deep['validation_notes'] = array_merge(
                            (array) ($deep['validation_notes'] ?? []),
                            ['اختلفت قراءة رقم الفاتورة بين المحاولتين — تحقّق يدويًا من الرقم']
                        );
                    }
                    $data = $deep;
                }

                $data['page_number'] = $pageNo;
                $data['_image'] = $rel;
                $data['processing_ms'] = (int) round((microtime(true) - $t0) * 1000); // Spec 001 FR-009 avg-time metric
                $rows[] = $data;
            } catch (\Throwable $e) {
                $rows[] = ['page_number' => $pageNo, 'invoice_number' => null, '_image' => $rel, '_error' => $e->getMessage()];
            }
            if ($onProgress) {
                $onProgress($pageNo, $total);
            }
        }

        $invoices = $group ? $this->service->groupByInvoiceNumber($rows) : $rows;
        foreach ($invoices as $inv) {
            $this->persist($batch, $inv['page_number'] ?? 1, $inv, $inv['_image'] ?? null);
        }

        return count($invoices);
    }

    private function wholeDocument(InvoiceBatch $batch, string $pdfPath, string $savedRel, string $model): int
    {
        [$light, $hard, $escalate] = $this->thinkingTiers();

        // Pass 1 — cheap (no/low thinking). image_quality + validation flag the bad scans.
        $result = $this->service->extractInvoicesFromDocument($pdfPath, $model, $light);
        $this->inTokens += (int) ($result['in'] ?? 0);
        $this->outTokens += (int) ($result['out'] ?? 0);
        $invoices = $result['invoices'] ?? [];

        // Pass 2 — re-read the WHOLE doc with deeper thinking only if a bad scan was found.
        if ($escalate && $hard !== $light && $this->anyFlagged($invoices)) {
            $deep = $this->service->extractInvoicesFromDocument($pdfPath, $model, $hard);
            $this->inTokens += (int) ($deep['in'] ?? 0);
            $this->outTokens += (int) ($deep['out'] ?? 0);
            if (! empty($deep['invoices'])) {
                $invoices = $this->mergeEscalated($invoices, $deep['invoices']);
            }
        }

        $batch->update(['total_pages' => count($invoices), 'status' => 'processing']);

        // Rasterize pages (NO AI cost) so each invoice gets its OWN page image as the
        // attachment instead of a link into the whole PDF.
        $pageImages = $this->rasterizePages($pdfPath, $batch);

        $made = 0;
        foreach ($invoices as $idx => $inv) {
            $pageNo = $inv['page_number'] ?: ($idx + 1);
            $image = $pageImages[$pageNo] ?? ($savedRel.'#page='.$pageNo);
            $this->persist($batch, $pageNo, $inv, $image);
            $made++;
        }

        return $made;
    }

    /**
     * Flag invoices whose number repeats — within this batch OR already present in an
     * earlier batch — as needs_review. Never merges or drops; the human decides whether
     * it's a true duplicate or a misread collision.
     */
    private function flagDuplicates(InvoiceBatch $batch): void
    {
        $invoices = $batch->invoices()->get();
        $dupInBatch = InvoiceExtractionService::duplicateNumbers($invoices->pluck('invoice_number')->all());

        foreach ($invoices as $inv) {
            if (! filled($inv->invoice_number)) {
                continue;
            }
            $add = [];

            if (in_array(InvoiceExtractionService::normNumber($inv->invoice_number), $dupInBatch, true)) {
                $add[] = 'رقم فاتورة مكرر داخل نفس الدفعة';
            }
            $existsElsewhere = Invoice::on($batch->getConnectionName())
                ->where('invoice_number', $inv->invoice_number)
                ->where('batch_id', '!=', $batch->id)
                ->exists();
            if ($existsElsewhere) {
                $add[] = 'رقم فاتورة موجود في دفعة أخرى — قد تكون مكررة أو مُدخلة سابقًا';
            }

            if ($add) {
                $notes = trim((string) $inv->validation_notes);
                $inv->forceFill([
                    'needs_review' => true,
                    'validation_notes' => $notes !== '' ? $notes.' | '.implode(' | ', $add) : implode(' | ', $add),
                ])->save();
            }
        }
    }

    /** Best-effort PNG-per-page map (pageNo => public-relative path); empty if pdftoppm unavailable. */
    private function rasterizePages(string $pdfPath, InvoiceBatch $batch): array
    {
        try {
            $dir = public_path('uploads/invoices/pages/batch_'.$batch->id);
            $map = [];
            foreach ($this->rasterizer->rasterize($pdfPath, $dir) as $i => $png) {
                $map[$i + 1] = str_replace(public_path().'/', '', $png);
            }

            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** [lightThinking, hardThinking, escalateEnabled] from config. */
    private function thinkingTiers(): array
    {
        return [
            config('services.gemini.thinking_level', 'minimal'),
            config('services.gemini.thinking_level_hard', 'low'),
            (bool) config('services.gemini.escalate_on_review', true),
        ];
    }

    private function anyFlagged(array $invoices): bool
    {
        foreach ($invoices as $i) {
            if (! empty($i['_error']) || ! empty($i['needs_review'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Take the deeper-thinking re-read as the result, but if the two passes read a
     * DIFFERENT invoice number for the same page, the scan is unreliable — keep it
     * flagged for manual correction (don't let the deep pass "confidently" hide it).
     */
    private function mergeEscalated(array $light, array $deep): array
    {
        $lightNumByPage = [];
        foreach ($light as $i) {
            $lightNumByPage[$i['page_number'] ?? 0] = $i['invoice_number'] ?? null;
        }

        return array_map(function ($i) use ($lightNumByPage) {
            $i['_escalated'] = true;
            $pg = $i['page_number'] ?? 0;
            if (array_key_exists($pg, $lightNumByPage) && $lightNumByPage[$pg] !== ($i['invoice_number'] ?? null)) {
                $i['needs_review'] = true;
                $i['validation_notes'] = array_merge(
                    (array) ($i['validation_notes'] ?? []),
                    ['اختلفت قراءة رقم الفاتورة بين المحاولتين — تحقّق يدويًا من الرقم']
                );
            }

            return $i;
        }, $deep);
    }

    public function persist(InvoiceBatch $batch, int $pageNo, array $data, ?string $imagePath): Invoice
    {
        if (isset($data['_error'])) {
            return Invoice::updateOrCreate(
                ['batch_id' => $batch->id, 'page_number' => $pageNo],
                ['image_path' => $imagePath, 'status' => 'failed', 'needs_review' => true, 'error_message' => $data['_error']]
            );
        }

        // Governance — before a reprocess overwrites this page, snapshot its prior
        // extraction so no version is ever lost. New rows start at version 1.
        $prior = Invoice::where('batch_id', $batch->id)->where('page_number', $pageNo)->first();
        $newVersion = 1;
        if ($prior) {
            \App\Models\InvoiceVersion::create([
                'invoice_id' => $prior->id,
                'batch_id' => $batch->id,
                'page_number' => $pageNo,
                'version' => (int) ($prior->version ?? 1),
                'snapshot' => $prior->getAttributes(),
                'created_at' => now(),
            ]);
            $newVersion = (int) ($prior->version ?? 1) + 1;
        }

        $invoice = Invoice::updateOrCreate(
            ['batch_id' => $batch->id, 'page_number' => $pageNo],
            [
                'version' => $newVersion,
                'image_path' => $imagePath,
                'supplier_name' => $data['supplier_name'] ?? null,
                'supplier_tax_number' => $data['supplier_tax_number'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'invoice_date_raw' => $data['invoice_date_raw'] ?? null,
                'amount_before_vat' => $data['amount_before_vat'] ?? null,
                'vat_amount' => $data['vat_amount'] ?? null,
                'total_incl_vat' => $data['total_incl_vat'] ?? null,
                'confidence' => $data['confidence'] ?? null,
                'image_quality' => $data['image_quality'] ?? null,
                'raw_json' => $data['raw_json'] ?? null,
                'needs_review' => $data['needs_review'] ?? false,
                'validation_notes' => isset($data['validation_notes']) && is_array($data['validation_notes'])
                    ? implode(' | ', $data['validation_notes']) : null,
                'status' => 'done',
                // Spec 002/001 — extended fields (null-safe; absent on legacy paths).
                'invoice_type' => $data['invoice_type'] ?? null,
                'currency' => $data['currency'] ?? null,
                'discount_total' => $data['discount_total'] ?? null,
                'vat_rate' => $data['vat_rate'] ?? null,
                'commercial_registration' => $data['commercial_registration'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'issuer_establishment_name' => $data['issuer_establishment_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'field_confidence' => $data['field_confidence'] ?? null,
                // Spec 002 FR-106 — per-image fingerprint for duplicate detection.
                'file_hash' => $imagePath ? \App\Services\DuplicateDetector::fileHash(public_path($imagePath)) : null,
                'processing_ms' => $data['processing_ms'] ?? null,
            ]
        );

        // Spec 001 FR-006 — audit the extraction (actor = batch owner; no session in the job).
        \App\Services\AuditLogger::log('invoice', (int) $invoice->id, \App\Services\AuditLogger::EXTRACT, [
            'batch_id' => $batch->id,
            'user' => $batch->user_id,
            'note' => 'استخراج تلقائي (صفحة '.$pageNo.')',
        ]);

        // Spec 002 FR-102 — replace line items for this invoice (idempotent re-persist).
        if (array_key_exists('line_items', $data)) {
            \App\Models\InvoiceItem::on($invoice->getConnectionName())
                ->where('invoice_id', $invoice->id)->delete();
            foreach ((array) $data['line_items'] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                \App\Models\InvoiceItem::create($item + [
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                ]);
            }
        }

        return $invoice;
    }
}
