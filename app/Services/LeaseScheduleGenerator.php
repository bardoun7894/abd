<?php

namespace App\Services;

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

    /**
     * Generate the payment rows for a contract. Returns an array of rows shaped like
     * lease_payments (minus contract_id, which the caller adds on persist):
     * [payment_no, due_date (Y-m-d), amount, status, remaining].
     *
     * @throws InvalidArgumentException when start_date is missing/unparseable.
     */
    public function generate(array $contract): array
    {
        $startDate = $contract['start_date'] ?? null;
        if (! $startDate) {
            throw new InvalidArgumentException('start_date is required to generate a payment schedule');
        }
        $start = Carbon::parse($startDate);

        $numPayments = max(1, (int) ($contract['num_payments'] ?? 1));
        $rentValue = (float) ($contract['rent_value'] ?? 0);
        $intervalMonths = $this->intervalMonths($contract['payment_frequency'] ?? null, $numPayments);

        $amounts = $this->amounts($contract['payment_value'] ?? null, $rentValue, $numPayments);

        $rows = [];
        for ($i = 0; $i < $numPayments; $i++) {
            $due = (clone $start)->addMonths($intervalMonths * $i);
            $rows[] = [
                'payment_no' => $i + 1,
                'due_date' => $due->format('Y-m-d'),
                'amount' => $amounts[$i],
                'status' => 'pending',
                'remaining' => $amounts[$i],
            ];
        }

        return $rows;
    }

    /**
     * Per-installment amounts. Prefers the extracted payment_value verbatim for every
     * row; otherwise splits rent_value evenly, absorbing the rounding remainder into
     * the last installment so the sum always equals rent_value exactly.
     *
     * @return float[]
     */
    private function amounts($paymentValue, float $rentValue, int $numPayments): array
    {
        if (is_numeric($paymentValue) && (float) $paymentValue > 0) {
            return array_fill(0, $numPayments, round((float) $paymentValue, 2));
        }

        $base = round($rentValue / $numPayments, 2);
        $amounts = array_fill(0, $numPayments, $base);

        $diff = round($rentValue - round($base * $numPayments, 2), 2);
        if ($diff !== 0.0) {
            $amounts[$numPayments - 1] = round($amounts[$numPayments - 1] + $diff, 2);
        }

        return $amounts;
    }

    /** Months between installments for a given frequency label; falls back sensibly. */
    private function intervalMonths(?string $frequency, int $numPayments): int
    {
        if ($frequency) {
            $key = strtolower(trim($frequency));
            if (isset(self::FREQUENCY_MONTHS[$key])) {
                return self::FREQUENCY_MONTHS[$key];
            }
        }

        // Unknown/blank frequency: spread evenly monthly if there's more than one
        // payment, otherwise treat it as a single one-time payment.
        return $numPayments > 1 ? 1 : 0;
    }
}
