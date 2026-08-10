<?php

use App\Support\VatBreakdown;

/**
 * Regression cover for the client video of 2026-08-10: the purchase screen and
 * the Excel report disagreed on the VAT columns for the same six invoices,
 * because the screen back-computed 15% for every row while the export printed
 * only a stored breakdown and left the cell blank when there was none.
 *
 * The six rows below are the real ones from that report (sabah instance,
 * purchase_id 7255-7261) — three with a stored breakdown, three legacy rows
 * with amount_before_vat / vat_amount / vat_rate all NULL.
 */
function videoRows(): array
{
    return [
        // [invoice no, total, stored before, stored vat, stored rate]
        ['14023034637', 974.020, null, null, null],
        ['14023034531', 1777.130, null, null, null],
        ['14023034453', 1509.490, null, null, null],
        ['15868', 3480.000, 3026.090, 453.910, 15.000],
        ['SI-GM-2026-2607', 2528.850, 2199.000, 329.850, 15.000],
        ['SI-GM-2026-2342', 10745.600, 9344.000, 1401.600, 15.000],
    ];
}

it('derives the breakdown at the standard rate when the invoice states none', function () {
    // The row the client filmed: blank in the export, 846.97 / 127.05 on screen.
    [$before, $vat] = VatBreakdown::split(974.020, null, null, null);

    expect($before)->toBe(846.97)
        ->and($vat)->toBe(127.05);
});

it('prefers the breakdown the invoice actually states', function () {
    // A real invoice may round its own VAT away from total/1.15; the stored
    // figure is the one that matches the paper, so it must win.
    [$before, $vat] = VatBreakdown::split(1000.00, 870.00, 130.00, 15.000);

    expect($before)->toBe(870.00)
        ->and($vat)->toBe(130.00);
});

it('uses the rate the invoice states over the standard one', function () {
    [$before, $vat] = VatBreakdown::split(105.00, null, null, 5.000);

    expect($before)->toBe(100.00)
        ->and($vat)->toBe(5.00);
});

it('ignores a half-filled breakdown rather than printing columns that do not add up', function () {
    // before present but vat missing — deriving both keeps the row summable.
    [$before, $vat] = VatBreakdown::split(974.020, 846.97, null, null);

    expect(round($before + $vat, 2))->toBe(974.02);
});

it('always returns two columns that sum back to the printed total', function () {
    foreach (videoRows() as [$no, $total, $before, $vat, $rate]) {
        [$b, $v] = VatBreakdown::split($total, $before, $vat, $rate);

        expect(round($b + $v, 2))->toBe(round($total, 2), "row {$no} does not sum back");
    }
});

it('reproduces the totals the client saw on screen', function () {
    $sumBefore = 0.0;
    $sumVat = 0.0;
    $sumTotal = 0.0;

    foreach (videoRows() as [$no, $total, $before, $vat, $rate]) {
        [$b, $v] = VatBreakdown::split($total, $before, $vat, $rate);
        $sumBefore += $b;
        $sumVat += $v;
        $sumTotal += $total;
    }

    // Before the fix the export summed to 14,569.09 / 2,185.36 — the three
    // legacy rows were simply absent from both VAT columns.
    expect(round($sumBefore, 2))->toBe(18273.99)
        ->and(round($sumVat, 2))->toBe(2741.10)
        ->and(round($sumTotal, 2))->toBe(21015.09);
});
