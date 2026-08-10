<?php

namespace App\Support;

/**
 * Single source of truth for the VAT split shown on a purchase row.
 *
 * The purchase screen, the Excel report and the PDF report each used to decide
 * this for themselves, and they disagreed: the screen back-computed 15% for
 * every row while the Excel printed only a stored breakdown and left the cell
 * blank when there was none. Client video 2026-08-10 — the same six invoices
 * totalled 18,273.99 / 2,741.10 on screen and 14,569.09 / 2,185.36 in the
 * export, because three legacy rows were keyed in as a single figure and carry
 * no amount_before_vat / vat_amount / vat_rate at all.
 *
 * Resolution order, applied identically everywhere:
 *   1. the breakdown the invoice actually states (AI-extracted or hand-entered)
 *   2. the rate the invoice states, applied to the tax-inclusive total
 *   3. the KSA standard rate, applied to the tax-inclusive total
 *
 * Step 1 comes first on purpose: a real invoice may round its own VAT a halala
 * away from total/1.15, and the stored figure is the one that matches the paper.
 */
class VatBreakdown
{
    /** KSA standard VAT rate, as a percentage. */
    public const STANDARD_RATE = 15.0;

    /**
     * Resolve [amount before VAT, VAT amount] for one purchase row.
     *
     * @param  mixed  $total   tax-inclusive amount (purchase_price)
     * @param  mixed  $before  stored amount_before_vat, if any
     * @param  mixed  $vat     stored vat_amount, if any
     * @param  mixed  $rate    stored vat_rate as a percentage, if any
     * @return array{0: float, 1: float}
     */
    public static function split($total, $before = null, $vat = null, $rate = null): array
    {
        $total = (float) $total;

        // A row is only "stated" when both halves are present — one half alone
        // would make the two columns fail to add up to the total on screen.
        if ($before !== null && $vat !== null) {
            return [round((float) $before, 2), round((float) $vat, 2)];
        }

        $rate = (! empty($rate) && (float) $rate > 0) ? (float) $rate : self::STANDARD_RATE;

        $beforeVat = round($total / (1 + ($rate / 100)), 2);

        // Derive VAT by subtraction rather than by multiplying the rate, so the
        // two columns always sum back to the printed total.
        return [$beforeVat, round($total - $beforeVat, 2)];
    }
}
