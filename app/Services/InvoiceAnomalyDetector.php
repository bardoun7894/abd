<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Deterministic, rule-based anomaly detection for extracted invoices — no AI
 * calls, pure rules plus one optional/guarded DB lookup for the supplier
 * average. Runs AFTER InvoiceExtractionService::validate() and only ADDS
 * anomaly notes (each prefixed "شذوذ:") on top of the existing validation.
 */
class InvoiceAnomalyDetector
{
    /** Saudi standard VAT rate. */
    private const STANDARD_VAT_RATE = 0.15;

    /** Allowed deviation from the standard VAT rate before flagging (2 percentage points). */
    private const VAT_RATE_TOLERANCE = 0.02;

    /** Below this effective rate we treat the invoice as zero-rated (not an anomaly). */
    private const ZERO_RATE_THRESHOLD = 0.01;

    /** Years before an invoice_date is considered stale (likely an OCR misread). */
    private const STALE_YEARS = 3;

    /** Minimum number of prior invoices needed before comparing against the supplier average. */
    private const MIN_SUPPLIER_HISTORY = 3;

    private const SUPPLIER_HIGH_MULTIPLIER = 4.0;

    private const SUPPLIER_LOW_MULTIPLIER = 0.25;

    /**
     * Run all rules against a normalized invoice array. Returns a list of
     * Arabic anomaly notes; an empty array means no anomalies were found.
     *
     * @param  array  $inv  Normalized invoice fields (same shape persisted on the Invoice model).
     * @param  string|null  $connection  DB connection to use for the supplier-average lookup
     *                                   (defaults to the `invoices` connection).
     */
    public function detect(array $inv, ?string $connection = null): array
    {
        $notes = [];

        $this->checkVatRate($inv, $notes);
        $this->checkTotalMismatch($inv, $notes);
        $this->checkFutureDate($inv, $notes);
        $this->checkStaleDate($inv, $notes);
        $this->checkZeroOrNegativeTotal($inv, $notes);
        $this->checkSupplierAverage($inv, $notes, $connection);

        return $notes;
    }

    /** Rule 1 — effective VAT rate should be ~15% (zero-rated invoices are allowed). */
    private function checkVatRate(array $inv, array &$notes): void
    {
        $base = $inv['amount_before_vat'] ?? null;
        $vat = $inv['vat_amount'] ?? null;

        if (! is_numeric($base) || (float) $base <= 0 || ! is_numeric($vat) || (float) $vat <= 0) {
            return;
        }

        $rate = (float) $vat / (float) $base;

        if ($rate < self::ZERO_RATE_THRESHOLD) {
            return; // effectively zero-rated — not an anomaly
        }

        if (abs($rate - self::STANDARD_VAT_RATE) > self::VAT_RATE_TOLERANCE) {
            $pct = round($rate * 100, 1);
            $notes[] = "شذوذ: نسبة الضريبة الفعلية {$pct}% تنحرف عن النسبة القياسية 15%";
        }
    }

    /** Rule 2 — total_incl_vat should reconcile with amount_before_vat + vat_amount. */
    private function checkTotalMismatch(array $inv, array &$notes): void
    {
        $base = $inv['amount_before_vat'] ?? null;
        $vat = $inv['vat_amount'] ?? null;
        $total = $inv['total_incl_vat'] ?? null;

        if (! is_numeric($base) || ! is_numeric($vat) || ! is_numeric($total)) {
            return;
        }

        $expected = (float) $base + (float) $vat;
        $delta = abs($expected - (float) $total);
        $tolerance = max(0.02 * abs((float) $base), 0.10);

        if ($delta > $tolerance) {
            $notes[] = 'شذوذ: فرق بين الإجمالي المسجل ('.round((float) $total, 2).
                ') والمجموع المتوقع ('.round($expected, 2).') بمقدار '.round($delta, 2).' ريال';
        }
    }

    /** Rule 3 — invoice_date in the future. */
    private function checkFutureDate(array $inv, array &$notes): void
    {
        $date = $this->parseDate($inv['invoice_date'] ?? null);
        if ($date === null) {
            return;
        }

        if ($date->greaterThan(Carbon::today())) {
            $notes[] = 'شذوذ: تاريخ الفاتورة في المستقبل ('.$date->toDateString().')';
        }
    }

    /** Rule 4 — invoice_date older than ~3 years (likely OCR misread). */
    private function checkStaleDate(array $inv, array &$notes): void
    {
        $date = $this->parseDate($inv['invoice_date'] ?? null);
        if ($date === null) {
            return;
        }

        if ($date->lessThan(Carbon::today()->subYears(self::STALE_YEARS))) {
            $notes[] = 'شذوذ: تاريخ الفاتورة قديم جدًا (أقدم من 3 سنوات) — يُحتمل وجود خطأ في قراءة التاريخ ('.$date->toDateString().')';
        }
    }

    /** Rule 5 — total_incl_vat is zero or negative. */
    private function checkZeroOrNegativeTotal(array $inv, array &$notes): void
    {
        $total = $inv['total_incl_vat'] ?? null;

        if (is_numeric($total) && (float) $total <= 0) {
            $notes[] = 'شذوذ: الإجمالي شامل الضريبة صفر أو سالب ('.$total.')';
        }
    }

    /**
     * Rule 6 — the one DB lookup. Compares this invoice's total against the
     * historical average for the same supplier (name or tax number), among
     * prior approved (done, not needs_review) invoices. Guarded: any DB
     * failure or missing history is silently skipped, never thrown.
     */
    private function checkSupplierAverage(array $inv, array &$notes, ?string $connection): void
    {
        $total = $inv['total_incl_vat'] ?? null;
        if (! is_numeric($total) || (float) $total <= 0) {
            return;
        }

        $supplierName = $inv['supplier_name'] ?? null;
        $taxNumber = $inv['supplier_tax_number'] ?? null;
        if (! filled($supplierName) && ! filled($taxNumber)) {
            return;
        }

        try {
            $base = DB::connection($connection ?: 'invoices')->table('invoices')
                ->where('status', 'done')
                ->where('needs_review', false)
                ->whereNotNull('total_incl_vat')
                ->where(function ($q) use ($supplierName, $taxNumber) {
                    if (filled($supplierName)) {
                        $q->orWhere('supplier_name', $supplierName);
                    }
                    if (filled($taxNumber)) {
                        $q->orWhere('supplier_tax_number', $taxNumber);
                    }
                });

            if (! empty($inv['id'])) {
                $base->where('id', '!=', $inv['id']);
            }

            $count = (clone $base)->count();
            if ($count < self::MIN_SUPPLIER_HISTORY) {
                return;
            }

            $avg = (float) (clone $base)->avg('total_incl_vat');
            if ($avg <= 0) {
                return;
            }

            $ratio = (float) $total / $avg;
            if ($ratio > self::SUPPLIER_HIGH_MULTIPLIER || $ratio < self::SUPPLIER_LOW_MULTIPLIER) {
                $notes[] = 'شذوذ: إجمالي الفاتورة ('.round((float) $total, 2).
                    ') يختلف بشكل كبير عن متوسط فواتير هذا المورد ('.round($avg, 2).')';
            }
        } catch (\Throwable $e) {
            // DB not reachable / not booted / table missing — skip silently.
        }
    }

    private function parseDate($value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
