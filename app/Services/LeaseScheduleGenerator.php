<?php

namespace App\Services;

use App\Support\ArabicText;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Builds a lease's `lease_payments` schedule (Spec 003 FR-203) from the extracted/approved
 * contract fields: rent_value, num_payments, payment_value, payment_frequency, start_date.
 *
 * Pure (no DB, no container) so it is unit-testable in isolation — persistence is done by
 * the caller (LeaseController::approve).
 */
class LeaseScheduleGenerator
{
    /** payment_frequency (AR/EN synonyms) -> months between installments. 0 = one-time. */
    private const FREQUENCY_MONTHS = [
        'monthly' => 1, 'شهري' => 1, 'شهريا' => 1, 'شهرية' => 1,
        'quarterly' => 3, 'ربع سنوي' => 3, 'ربعي' => 3, 'كل ثلاثة أشهر' => 3,
        'semiannual' => 6, 'semi-annual' => 6, 'نصف سنوي' => 6, 'نصف سنوية' => 6,
        'yearly' => 12, 'annual' => 12, 'annually' => 12, 'سنوي' => 12, 'سنوية' => 12,
        'one-time' => 0, 'once' => 0, 'single' => 0, 'دفعة واحدة' => 0, 'دفعة كاملة' => 0,
    ];

    /** Tolerance used when reconciling the schedule sum against the expected contract total. */
    private const RECONCILE_TOLERANCE = 0.01;

    /**
     * Generate the payment rows for a contract. Backward-compatible wrapper that
     * returns only the rows array. Call generateWithWarnings() to also receive
     * reconciliation / end_date clamp warnings.
     *
     * @throws InvalidArgumentException when start_date is missing/unparseable.
     */
    public function generate(array $contract): array
    {
        return $this->generateWithWarnings($contract)['rows'];
    }

    /**
     * Generate the payment rows plus any warnings from reconciliation or clamping.
     *
     * Returns:
     *   [
     *     'rows' => [ [payment_no, due_date (Y-m-d), amount, status, remaining], ... ],
     *     'warnings' => [ '...', ... ],
     *   ]
     *
     * @throws InvalidArgumentException when start_date is missing/unparseable.
     */
    public function generateWithWarnings(array $contract): array
    {
        // The contract's OWN printed schedule always wins over anything we derive.
        // Saudi «إيجار» contracts print جدول سداد الدفعات with each installment's real
        // due date and VAT-inclusive total; deriving them instead got both wrong
        // (VAT dropped, and due dates placed on the tenancy start rather than the
        // days-later dates the table actually states).
        $printed = $this->fromPrintedSchedule($contract);
        if ($printed !== null) {
            return $printed;
        }

        $startDate = $contract['start_date'] ?? null;
        if (! $startDate) {
            throw new InvalidArgumentException('start_date is required to generate a payment schedule');
        }
        $start = Carbon::parse($startDate);

        $numPayments = max(1, (int) ($contract['num_payments'] ?? 1));
        $rentValue = (float) ($contract['rent_value'] ?? 0);
        $intervalMonths = $this->intervalMonths($contract['payment_frequency'] ?? null, $numPayments);
        $expectedTotal = $this->expectedTotal($contract);

        $paymentValue = isset($contract['payment_value']) && is_numeric($contract['payment_value'])
            ? (float) $contract['payment_value']
            : null;

        $warnings = [];
        $amounts = $this->amounts($paymentValue, $rentValue, $numPayments, $expectedTotal, $warnings);

        $endDate = ! empty($contract['end_date']) ? Carbon::parse($contract['end_date']) : null;

        $rows = [];
        for ($i = 0; $i < $numPayments; $i++) {
            $due = (clone $start)->addMonths($intervalMonths * $i);
            if ($endDate && $due->greaterThan($endDate)) {
                $due = $endDate->copy();
                $warnings[] = "موعد الدفعة ".($i + 1)." تجاوز تاريخ نهاية العقد وتم تقييده بـ {$endDate->format('Y-m-d')}.";
            }

            $rows[] = [
                'payment_no' => $i + 1,
                'due_date' => $due->format('Y-m-d'),
                'amount' => $amounts[$i],
                'status' => 'pending',
                'remaining' => $amounts[$i],
            ];
        }

        return ['rows' => $rows, 'warnings' => $warnings];
    }

    /**
     * Validate a generated schedule against the contract's expected total and term.
     *
     * @return string[] Human-readable error messages; empty array means valid.
     */
    public function validateSchedule(array $rows, array $contract): array
    {
        $errors = [];
        $expectedTotal = $this->expectedTotal($contract);

        if ($expectedTotal > 0 && count($rows) > 0) {
            $sum = round(array_sum(array_column($rows, 'amount')), 2);
            if (abs($sum - $expectedTotal) / $expectedTotal > self::RECONCILE_TOLERANCE) {
                $errors[] = "مجموع الجدول ({$sum}) لا يطابق إجمالي العقد المتوقع ({$expectedTotal}).";
            }
        }

        $startDate = $contract['start_date'] ?? null;
        $endDate = $contract['end_date'] ?? null;
        if ($endDate) {
            $end = Carbon::parse($endDate);
            foreach ($rows as $row) {
                $due = Carbon::parse($row['due_date']);
                if ($due->greaterThan($end)) {
                    $errors[] = "تاريخ استحقاق الدفعة {$row['payment_no']} ({$row['due_date']}) يتجاوز نهاية العقد ({$endDate}).";
                }
                if ($startDate && $due->lessThan(Carbon::parse($startDate))) {
                    $errors[] = "تاريخ استحقاق الدفعة {$row['payment_no']} ({$row['due_date']}) يسبق بداية العقد ({$startDate}).";
                }
            }
        }

        return $errors;
    }

    /**
     * Per-installment amounts. Prefers the extracted payment_value verbatim when it
     * reconciles to the expected contract total within tolerance; otherwise falls back
     * to an even split of the expected total, absorbing the rounding remainder into the
     * last installment.
     *
     * @return float[]
     */
    private function amounts(?float $paymentValue, float $rentValue, int $numPayments, float $expectedTotal, array &$warnings): array
    {
        $baseForSplit = $expectedTotal > 0 ? $expectedTotal : $rentValue;

        if ($paymentValue !== null && $paymentValue > 0) {
            $verbatimTotal = round($paymentValue * $numPayments, 2);
            if ($baseForSplit > 0 && abs($verbatimTotal - $baseForSplit) / $baseForSplit > self::RECONCILE_TOLERANCE) {
                $warnings[] = "قيمة الدفعة المستخرجة ({$paymentValue}) لا تطابق إجمالي العقد المتوقع ({$baseForSplit}); تم تقسيم المبلغ بالتساوي.";
                return $this->splitEvenly($baseForSplit, $numPayments);
            }

            return array_fill(0, $numPayments, round($paymentValue, 2));
        }

        return $this->splitEvenly($baseForSplit, $numPayments);
    }

    /** Split a total evenly across N payments, absorbing rounding remainder in the last. */
    private function splitEvenly(float $total, int $numPayments): array
    {
        $numPayments = max(1, $numPayments);
        $base = round($total / $numPayments, 2);
        $amounts = array_fill(0, $numPayments, $base);

        $diff = round($total - round($base * $numPayments, 2), 2);
        if ($diff !== 0.0) {
            $amounts[$numPayments - 1] = round($amounts[$numPayments - 1] + $diff, 2);
        }

        return $amounts;
    }

    /**
     * Expected contract total: annual rent × lease years.
     * Prefers explicit total_rent / contract_total, then duration, then date range.
     */
    private function expectedTotal(array $contract): float
    {
        $explicit = $contract['total_rent'] ?? $contract['contract_total'] ?? null;
        if (is_numeric($explicit) && (float) $explicit > 0) {
            return (float) $explicit;
        }

        $rentValue = (float) ($contract['rent_value'] ?? 0);
        if ($rentValue <= 0) {
            return $rentValue;
        }

        // `rent_value` may be EITHER the whole-contract total or a single year's
        // rent, depending on where the contract came from — extraction now returns
        // the VAT-inclusive contract total, but hand-entered and legacy rows still
        // hold an annual figure. Multiplying a total by the lease length inflates
        // it: a 5-year lease of 200,000 was scored against 1,000,000 and every
        // schedule "failed" reconciliation. Seen on a real 5-year, 10-payment,
        // zero-VAT contract (2026-08-22).
        //
        // `annual_rent` is the unambiguous signal: when the extractor captured it
        // separately, `rent_value` is definitively the total, so never scale it.
        if (isset($contract['annual_rent']) && is_numeric($contract['annual_rent']) && (float) $contract['annual_rent'] > 0) {
            return $rentValue;
        }

        // A printed schedule states the total outright — trust it over any guess.
        if (! empty($contract['payments']) && is_array($contract['payments'])) {
            $sum = 0.0;
            foreach ($contract['payments'] as $row) {
                if (is_array($row) && is_numeric($row['total'] ?? null)) {
                    $sum += (float) $row['total'];
                }
            }
            if ($sum > 0) {
                return round($sum, 2);
            }
        }

        $years = $this->leaseYears($contract);
        if ($years > 0) {
            // Only scale when the figure actually looks annual. If rent_value
            // already covers the full term (total ÷ years lands on the stated
            // installment, or simply exceeds one year's worth), scaling would
            // double-count it.
            $scaled = round($rentValue * $years, 2);
            $paymentValue = $contract['payment_value'] ?? null;
            $numPayments = (int) ($contract['num_payments'] ?? 0);

            if (is_numeric($paymentValue) && $numPayments > 0) {
                $fromPayments = round((float) $paymentValue * $numPayments, 2);
                // Whichever reading matches the installments is the right one.
                if (abs($rentValue - $fromPayments) <= abs($scaled - $fromPayments)) {
                    return $rentValue;
                }
            }

            return $scaled;
        }

        return $rentValue;
    }

    /**
     * Derive lease length in years from duration field or start/end dates.
     * Returns 0 when not derivable.
     */
    private function leaseYears(array $contract): float
    {
        $duration = $contract['duration'] ?? null;
        if (is_string($duration) && preg_match('/(\d+(\.\d+)?)/', $duration, $m)) {
            return (float) $m[1];
        }
        if (is_numeric($duration) && (float) $duration > 0) {
            return (float) $duration;
        }

        $startDate = $contract['start_date'] ?? null;
        $endDate = $contract['end_date'] ?? null;
        if ($startDate && $endDate) {
            try {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                if ($end->greaterThan($start)) {
                    return round($start->diffInDays($end) / 365.25, 2);
                }
            } catch (\Throwable $e) {
                // ignore unparseable dates
            }
        }

        return 0.0;
    }

    /** Months between installments for a given frequency label; falls back sensibly. */
    private function intervalMonths(?string $frequency, int $numPayments): int
    {
        if ($frequency) {
            // Fold Arabic spelling before matching. A contract reading "نصف سنوى"
            // (alef maqsura) instead of "نصف سنوي" used to miss the table entirely
            // and fall through to the monthly default below — so a semi-annual
            // lease silently produced دفعات one month apart. Same word, real money.
            $key = ArabicText::fold($frequency);
            foreach (self::FREQUENCY_MONTHS as $name => $months) {
                if (ArabicText::fold($name) === $key) {
                    return $months;
                }
            }
        }

        // Unknown/blank frequency: spread evenly monthly if there's more than one
        // payment, otherwise treat it as a single one-time payment.
        return $numPayments > 1 ? 1 : 0;
    }

    /**
     * Build the schedule straight from the table the contract prints, when one was
     * extracted. Returns null (not an empty schedule) when there is nothing usable,
     * so the caller falls through to the derived path for contracts that print no
     * table — the common case for older/manual leases.
     *
     * Only the due date and total are taken from each row; status/remaining follow
     * the same shape the derived path produces, so downstream persistence is
     * untouched. A row's own VAT split is intentionally NOT persisted here: the
     * lease_payments table stores one amount per installment, and that amount is
     * what the tenant actually owes — the VAT-inclusive total.
     *
     * @return array{rows:array<int,array<string,mixed>>,warnings:array<int,string>}|null
     */
    private function fromPrintedSchedule(array $contract): ?array
    {
        $printed = $contract['payments'] ?? null;
        if (! is_array($printed) || $printed === []) {
            return null;
        }

        $rows = [];
        $warnings = [];
        $no = 1;
        foreach ($printed as $row) {
            if (! is_array($row)) {
                continue;
            }
            $due = $row['due_date'] ?? null;
            $total = $row['total'] ?? null;
            if (! $due || ! is_numeric($total) || (float) $total <= 0) {
                continue;
            }

            try {
                $dueDate = Carbon::parse($due)->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;   // unparseable date — skip rather than guess
            }

            $amount = round((float) $total, 2);
            $rows[] = [
                'payment_no' => $no,
                'due_date' => $dueDate,
                'amount' => $amount,
                'status' => 'pending',
                'remaining' => $amount,
            ];
            $no++;
        }

        if ($rows === []) {
            return null;
        }

        // Surface disagreements instead of silently trusting either side.
        $stated = $contract['num_payments'] ?? null;
        if (is_numeric($stated) && (int) $stated !== count($rows)) {
            $warnings[] = 'عدد الدفعات المذكور في العقد ('.(int) $stated.') لا يطابق عدد صفوف جدول السداد ('.count($rows).') — تم اعتماد الجدول المطبوع.';
        }

        $expectedTotal = $this->expectedTotal($contract);
        if ($expectedTotal > 0) {
            $sum = round(array_sum(array_column($rows, 'amount')), 2);
            if (abs($sum - $expectedTotal) / $expectedTotal > self::RECONCILE_TOLERANCE) {
                $warnings[] = "مجموع جدول السداد المطبوع ({$sum}) لا يطابق إجمالي قيمة العقد ({$expectedTotal}).";
            }
        }

        return ['rows' => $rows, 'warnings' => $warnings];
    }
}
