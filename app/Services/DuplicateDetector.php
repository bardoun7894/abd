<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * Detects duplicate invoices before a purchase record is created (Spec 002 FR-106).
 * Matches across invoice number, tax number, supplier name, date, amount, and a file
 * fingerprint (sha256). The scoring is pure + unit-testable; the DB lookup wraps it.
 */
class DuplicateDetector
{
    /** Similarity at/above this blocks auto-creation and requires an override reason. */
    public const BLOCK_THRESHOLD = 0.75;

    /** sha256 of a file's bytes — the fingerprint stored on upload (FR-106). */
    public static function fileHash(string $path): ?string
    {
        return is_file($path) ? hash_file('sha256', $path) : null;
    }

    /**
     * Pure similarity score (0..1) between two invoice-like arrays. An identical file
     * hash is an immediate 1.0. Otherwise a weighted blend of the key criteria.
     */
    public static function score(array $a, array $b): float
    {
        // Identical bytes = certain duplicate.
        if (! empty($a['file_hash']) && ! empty($b['file_hash']) && $a['file_hash'] === $b['file_hash']) {
            return 1.0;
        }

        $weights = [
            'invoice_number' => 0.40,
            'supplier_tax_number' => 0.25,
            'total_incl_vat' => 0.15,
            'invoice_date' => 0.10,
            'supplier_name' => 0.10,
        ];
        $score = 0.0;
        $applied = 0.0;

        foreach ($weights as $field => $w) {
            $av = $a[$field] ?? null;
            $bv = $b[$field] ?? null;
            if ($av === null || $av === '' || $bv === null || $bv === '') {
                continue; // can't compare — skip and re-weight
            }
            $applied += $w;
            $score += $w * self::fieldSim($field, $av, $bv);
        }

        return $applied > 0 ? round($score / $applied, 4) : 0.0;
    }

    /** Per-field 0..1 similarity used by score(). */
    private static function fieldSim(string $field, $a, $b): float
    {
        switch ($field) {
            case 'invoice_number':
                return InvoiceExtractionService::normNumber($a) === InvoiceExtractionService::normNumber($b) ? 1.0 : 0.0;
            case 'supplier_tax_number':
                return preg_replace('/\D+/', '', (string) $a) === preg_replace('/\D+/', '', (string) $b) ? 1.0 : 0.0;
            case 'total_incl_vat':
                $fa = (float) $a;
                $fb = (float) $b;
                return abs($fa - $fb) <= max(0.01 * max(1, abs($fa)), 0.10) ? 1.0 : 0.0;
            case 'invoice_date':
                return substr((string) $a, 0, 10) === substr((string) $b, 0, 10) ? 1.0 : 0.0;
            case 'supplier_name':
                return SupplierMatcher::nameSimilarity((string) $a, (string) $b);
            default:
                return 0.0;
        }
    }

    /**
     * Find the most similar prior invoice (in the isolated invoices DB) to the given
     * extracted data, excluding a batch if provided. Returns the best candidate +
     * score, or null when nothing crosses the block threshold.
     *
     * @return array{invoice: Invoice, score: float}|null
     */
    public function findDuplicate(array $data, ?int $excludeBatchId = null): ?array
    {
        $candidates = collect();

        // Authoritative matches: identical file hash or supplier tax number.
        if (! empty($data['file_hash']) || ! empty($data['supplier_tax_number'])) {
            $query = Invoice::query()
                ->where('status', '!=', 'failed')
                ->where(function ($q) use ($data) {
                    $q->when(! empty($data['file_hash']), fn ($q) => $q->orWhere('file_hash', $data['file_hash']))
                      ->when(! empty($data['supplier_tax_number']), fn ($q) => $q->orWhere('supplier_tax_number', $data['supplier_tax_number']));
                });
            if ($excludeBatchId) {
                $query->where('batch_id', '!=', $excludeBatchId);
            }
            $candidates = $candidates->merge($query->limit(200)->get());
        }

        // Invoice-number matches after normalization (spacing/case tolerant).
        // We do the normalization in PHP because the table stores the raw number.
        if (! empty($data['invoice_number'])) {
            $norm = InvoiceExtractionService::normNumber($data['invoice_number']);
            // Exact raw match: unbounded — an identical stored number is always a candidate.
            $exact = Invoice::query()
                ->where('status', '!=', 'failed')
                ->where('invoice_number', $data['invoice_number'])
                ->when($excludeBatchId, fn ($q) => $q->where('batch_id', '!=', $excludeBatchId))
                ->get();
            // Normalized match: bounded recent window, compared in PHP.
            $byNumber = Invoice::query()
                ->where('status', '!=', 'failed')
                ->whereNotNull('invoice_number')
                ->when($excludeBatchId, fn ($q) => $q->where('batch_id', '!=', $excludeBatchId))
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->filter(fn ($c) => InvoiceExtractionService::normNumber($c->invoice_number) === $norm);
            $candidates = $candidates->merge($exact)->merge($byNumber)->unique('id');
        }

        $best = null;
        foreach ($candidates as $cand) {
            $s = self::score($data, $cand->getAttributes());
            if ($s >= self::BLOCK_THRESHOLD && (! $best || $s > $best['score'])) {
                $best = ['invoice' => $cand, 'score' => $s];
            }
        }

        return $best;
    }
}
