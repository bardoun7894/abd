<?php

namespace App\Services;

use App\Support\ArabicText;

/**
 * Folds the per-page extraction rows of ONE lease batch into a single contract.
 *
 * WHY THIS EXISTS
 * ---------------
 * LeasePipeline reads one page at a time and writes one row per page, because
 * that is how the invoice pipeline works. Leases are not invoices: a batch is
 * one contract, and a Saudi EJAR/REGA document scatters that contract's fields
 * across pages — the contract number sits on page 1, the rent block on page 3
 * or 4. So no single row ever held a whole contract.
 *
 * LeaseController::approve() requires start_date AND rent_value together, so
 * the page-1 row (number, dates, no money) and the page-3 row (money, no
 * number) were each individually unapprovable. Client, 2026-07-28: uploading a
 * contract on صباح النور produced 8-9 rows all marked "بحاجة مراجعة" and no
 * دفعات at all.
 *
 * Merge rule: for each field, the first non-blank value in page order wins.
 * When a later page carries a DIFFERENT non-blank value for a field already
 * taken, that is not silently dropped — it is reported as a conflict so the row
 * stays flagged for review. Financial fields disagreeing across pages is
 * exactly the case a human must look at, never one we guess at.
 */
class LeaseFieldMerger
{
    /** Contract fields folded across pages, in the order they are reported. */
    public const FIELDS = [
        'contract_no', 'tenant_name', 'tenant_id_no', 'landlord_name', 'landlord_id_no',
        'property_no', 'unit', 'property_type', 'address',
        'start_date', 'end_date', 'duration',
        'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
        'deposit', 'payment_method',
        'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
    ];

    /**
     * Fields approve() and the schedule generator need before a contract can
     * produce دفعات. Anything missing here keeps the row in review.
     */
    public const REQUIRED = ['start_date', 'rent_value'];

    /** Needed for a payment schedule that reconciles, but recoverable by hand. */
    public const SCHEDULE_FIELDS = ['num_payments', 'payment_value'];

    /**
     * @param  array<int,array<string,mixed>>  $pages  page rows, each with a 'page_number' key
     * @return array{values: array<string,mixed>, conflicts: array<int,string>, missing: array<int,string>}
     */
    public function merge(array $pages): array
    {
        usort($pages, fn ($a, $b) => ($a['page_number'] ?? 0) <=> ($b['page_number'] ?? 0));

        $values = [];
        $sourcePage = [];
        $conflicts = [];

        foreach ($pages as $row) {
            $pageNo = $row['page_number'] ?? 0;

            foreach (self::FIELDS as $field) {
                $candidate = $row[$field] ?? null;
                if ($this->isBlank($candidate)) {
                    continue;
                }

                if (! array_key_exists($field, $values)) {
                    $values[$field] = $candidate;
                    $sourcePage[$field] = $pageNo;

                    continue;
                }

                // Same value repeated on a later page is corroboration, not conflict —
                // EJAR restates the rent block on consecutive pages routinely.
                if (! $this->sameValue($values[$field], $candidate)) {
                    $conflicts[] = sprintf(
                        '%s: صفحة %d = %s / صفحة %d = %s',
                        $field,
                        $sourcePage[$field],
                        $this->display($values[$field]),
                        $pageNo,
                        $this->display($candidate)
                    );
                }
            }
        }

        $missing = [];
        foreach (array_merge(self::REQUIRED, self::SCHEDULE_FIELDS) as $field) {
            if ($this->isBlank($values[$field] ?? null)) {
                $missing[] = $field;
            }
        }

        return ['values' => $values, 'conflicts' => $conflicts, 'missing' => $missing];
    }

    /**
     * A merged contract is clean enough to approve only when nothing required is
     * missing and no page disagreed with another.
     */
    public function needsReview(array $merged): bool
    {
        return ! empty($merged['conflicts']) || ! empty($merged['missing']);
    }

    private function isBlank(mixed $v): bool
    {
        return $v === null || (is_string($v) && trim($v) === '');
    }

    /** Numeric fields must compare by value — "50000" and 50000.0 are the same rent. */
    private function sameValue(mixed $a, mixed $b): bool
    {
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.01;
        }

        return $this->normalize($a) === $this->normalize($b);
    }

    private function normalize(mixed $v): string
    {
        $s = trim((string) $v);
        // Dates arrive as both "2026-07-01" and "2026-07-01 00:00:00" depending on
        // whether the row came back through Eloquent casts or raw.
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ]00:00:00/', $s, $m)) {
            return $m[1];
        }

        // Real case (2026-07-28): page 2 of a contract said "نصف سنوى" and page 3
        // said "نصف سنوي" — one word, final ya written two ways — and that alone
        // put an otherwise clean contract into manual review.
        return ArabicText::fold($s);
    }

    /**
     * Conflict messages must quote the page verbatim. normalize() folds Arabic
     * spelling for comparison; showing that folded form back to the reviewer
     * would misquote the document they are about to check against.
     */
    private function display(mixed $v): string
    {
        return trim((string) $v);
    }
}
