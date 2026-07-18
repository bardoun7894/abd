<?php

namespace App\Services;

use App\Models\LeaseBatch;
use App\Models\LeaseExtraction;

/**
 * The single, shared extraction pipeline used by BOTH the CLI and the web
 * background job. Mirrors InvoicePipeline: rasterize each page → one AI read
 * per page → persist rows to lease_extractions. Leases are one-contract-per-batch
 * (no invoice-style grouping/dedup — each page is its own extraction row and the
 * user picks which page/version to approve into a LeaseContract).
 */
class LeasePipeline
{
    private int $inTokens = 0;

    private int $outTokens = 0;

    public function __construct(
        private PdfPageRasterizer $rasterizer,
        private PdfPageSplitter $splitter,
        private LeaseExtractionService $service,
    ) {}

    /**
     * Run the pipeline for a batch. $onProgress($done,$total) is called as pages finish
     * so the UI/CLI can show progress. Returns the number of extraction rows stored.
     */
    public function run(LeaseBatch $batch, string $pdfPath, ?string $model = null, ?callable $onProgress = null, ?float $deadline = null): int
    {
        $model = $model ?: config('services.gemini.default_model');

        $pagesDir = public_path('uploads/leases/pages/batch_'.$batch->id);
        if (! is_dir($pagesDir)) {
            @mkdir($pagesDir, 0775, true);
        }
        $savedPdf = $pagesDir.'/source.pdf';
        @copy($pdfPath, $savedPdf);
        $savedRel = str_replace(public_path().'/', '', $savedPdf);

        $this->inTokens = 0;
        $this->outTokens = 0;

        try {
            $pages = $this->rasterizer->rasterize($pdfPath, $pagesDir);
        } catch (\Throwable $e) {
            try {
                $pages = $this->splitter->split($pdfPath, $pagesDir);
            } catch (PdfSplitException $e2) {
                // Neither rasterization nor split worked — read the whole PDF as page 1.
                $pages = [$savedPdf];
            }
        }

        [$light, $hard, $escalate] = $this->thinkingTiers();
        $total = count($pages);
        $batch->update(['total_pages' => $total, 'status' => 'processing']);

        $made = 0;
        foreach ($pages as $i => $pagePath) {
            $pageNo = $i + 1;
            $rel = str_replace(public_path().'/', '', $pagePath);
            if ($this->deadlineExceeded($deadline)) {
                for ($j = $i; $j < $total; $j++) {
                    $remainingRel = str_replace(public_path().'/', '', $pages[$j]);
                    $this->persist($batch, $j + 1, ['_error' => 'Job deadline exceeded before AI call'], $remainingRel);
                }
                break;
            }
            try {
                $data = $this->service->extractLease($pagePath, $model, $light);
                $this->inTokens += (int) ($data['_in'] ?? 0);
                $this->outTokens += (int) ($data['_out'] ?? 0);

                if ($escalate && $hard !== $light && ! empty($data['needs_review'])) {
                    $deep = $this->service->extractLease($pagePath, $model, $hard);
                    $this->inTokens += (int) ($deep['_in'] ?? 0);
                    $this->outTokens += (int) ($deep['_out'] ?? 0);
                    $data = $deep;
                }

                $this->persist($batch, $pageNo, $data, $rel);
                $made++;
            } catch (\Throwable $e) {
                $this->persist($batch, $pageNo, ['_error' => $e->getMessage()], $rel);
            }
            if ($onProgress) {
                $onProgress($pageNo, $total);
            }
        }

        $batch->update([
            'processed_pages' => $batch->extractions()->count(),
            'status' => 'done',
            'input_tokens' => $this->inTokens,
            'output_tokens' => $this->outTokens,
            'est_cost_usd' => round($this->service->costUsd($this->inTokens, $this->outTokens), 5),
        ]);

        return $made;
    }

    /** [lightThinking, hardThinking, escalateEnabled] from config (shared with invoices). */
    private function thinkingTiers(): array
    {
        return [
            config('services.gemini.thinking_level', 'minimal'),
            config('services.gemini.thinking_level_hard', 'low'),
            (bool) config('services.gemini.escalate_on_review', true),
        ];
    }

    /** Returns true if less than page_timeout seconds remain before $deadline. */
    private function deadlineExceeded(?float $deadline): bool
    {
        if ($deadline === null) {
            return false;
        }
        $buffer = (int) config('services.gemini.page_timeout', 120);

        return microtime(true) + $buffer >= $deadline;
    }

    public function persist(LeaseBatch $batch, int $pageNo, array $data, ?string $imagePath): LeaseExtraction
    {
        if (isset($data['_error'])) {
            return LeaseExtraction::updateOrCreate(
                ['batch_id' => $batch->id, 'page_number' => $pageNo],
                ['image_path' => $imagePath, 'status' => 'failed', 'needs_review' => true, 'error_message' => $data['_error']]
            );
        }

        return LeaseExtraction::updateOrCreate(
            ['batch_id' => $batch->id, 'page_number' => $pageNo],
            [
                'image_path' => $imagePath,
                'contract_no' => $data['contract_no'] ?? null,
                'tenant_name' => $data['tenant_name'] ?? null,
                'tenant_id_no' => $data['tenant_id_no'] ?? null,
                'landlord_name' => $data['landlord_name'] ?? null,
                'landlord_id_no' => $data['landlord_id_no'] ?? null,
                'property_no' => $data['property_no'] ?? null,
                'unit' => $data['unit'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'address' => $data['address'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'duration' => $data['duration'] ?? null,
                'rent_value' => $data['rent_value'] ?? null,
                'num_payments' => $data['num_payments'] ?? null,
                'payment_value' => $data['payment_value'] ?? null,
                'payment_frequency' => $data['payment_frequency'] ?? null,
                'deposit' => $data['deposit'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'renewal_terms' => $data['renewal_terms'] ?? null,
                'cancellation_terms' => $data['cancellation_terms'] ?? null,
                'increase_terms' => $data['increase_terms'] ?? null,
                'extra_terms' => $data['extra_terms'] ?? null,
                'confidence' => $data['confidence'] ?? null,
                'raw_json' => $data['raw_json'] ?? null,
                'field_confidence' => $data['field_confidence'] ?? null,
                'needs_review' => $data['needs_review'] ?? false,
                'validation_notes' => isset($data['validation_notes']) && is_array($data['validation_notes'])
                    ? implode(' | ', $data['validation_notes']) : null,
                'status' => 'done',
            ]
        );
    }
}
