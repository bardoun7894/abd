<?php

// Pure merge logic — no app boot, no DB.

use App\Services\LeaseFieldMerger;

beforeEach(function () {
    $this->merger = new LeaseFieldMerger();
});

// The three page shapes below are the REAL rows produced on صباح النور on
// 2026-07-28 (lease_extractions, batches 1-3). Before the merge, approve()
// rejected every one of them: page 1 had the number but no rent, page 3 had the
// rent but no number, and it demands start_date AND rent_value together.

test('folds an EJAR contract whose number and rent block sit on different pages', function () {
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => '10617607378 / 0-1', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30'],
        ['page_number' => 2, 'rent_value' => 24000, 'num_payments' => 2, 'payment_value' => 12000],
        ['page_number' => 3, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'rent_value' => 24000, 'num_payments' => 2, 'payment_value' => 12000],
        ['page_number' => 4, 'contract_no' => null],
    ]);

    expect($merged['values']['contract_no'])->toBe('10617607378 / 0-1')
        ->and($merged['values']['start_date'])->toBe('2026-07-01')
        ->and($merged['values']['rent_value'])->toBe(24000)
        ->and($merged['values']['num_payments'])->toBe(2)
        ->and($merged['values']['payment_value'])->toBe(12000)
        ->and($merged['conflicts'])->toBeEmpty()
        ->and($merged['missing'])->toBeEmpty()
        ->and($this->merger->needsReview($merged))->toBeFalse();
});

test('a page restating the same rent block corroborates rather than conflicts', function () {
    // batch 3: pages 3 and 4 both carried 50000 / 2 / 25000.
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => '20868450462 / 1-0', 'start_date' => '2026-07-01'],
        ['page_number' => 3, 'rent_value' => 50000, 'num_payments' => 2, 'payment_value' => 25000],
        ['page_number' => 4, 'rent_value' => '50000.00', 'num_payments' => 2, 'payment_value' => 25000],
    ]);

    expect($merged['conflicts'])->toBeEmpty()
        ->and($this->merger->needsReview($merged))->toBeFalse();
});

test('keeps a contract in review when the rent block never appeared in full', function () {
    // batch 1: page 4 gave rent_value but no num_payments/payment_value anywhere.
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => '20918798871/1-0', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30'],
        ['page_number' => 4, 'rent_value' => 50000],
    ]);

    expect($merged['values']['rent_value'])->toBe(50000)
        ->and($merged['missing'])->toContain('num_payments')
        ->and($merged['missing'])->toContain('payment_value')
        ->and($this->merger->needsReview($merged))->toBeTrue();
});

test('reports disagreeing pages instead of silently taking the first', function () {
    // Guards the financial case: a Gemini misread on one page must never be
    // folded away into the ledger without a human seeing both numbers.
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => 'X-1', 'start_date' => '2026-07-01'],
        ['page_number' => 3, 'rent_value' => 50000, 'num_payments' => 2, 'payment_value' => 25000],
        ['page_number' => 5, 'rent_value' => 40000],
    ]);

    expect($merged['values']['rent_value'])->toBe(50000)
        ->and($merged['conflicts'])->toHaveCount(1)
        ->and($merged['conflicts'][0])->toContain('rent_value')
        ->and($merged['conflicts'][0])->toContain('50000')
        ->and($merged['conflicts'][0])->toContain('40000')
        ->and($this->merger->needsReview($merged))->toBeTrue();
});

test('treats blank strings as absent so a later real value still wins', function () {
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => '   ', 'start_date' => '2026-07-01'],
        ['page_number' => 2, 'contract_no' => 'REAL-99', 'rent_value' => 1200, 'num_payments' => 1, 'payment_value' => 1200],
    ]);

    expect($merged['values']['contract_no'])->toBe('REAL-99')
        ->and($merged['conflicts'])->toBeEmpty();
});

test('matches dates across the two formats Eloquent hands back', function () {
    $merged = $this->merger->merge([
        ['page_number' => 1, 'start_date' => '2026-07-01 00:00:00', 'contract_no' => 'A'],
        ['page_number' => 3, 'start_date' => '2026-07-01', 'rent_value' => 900, 'num_payments' => 1, 'payment_value' => 900],
    ]);

    expect($merged['conflicts'])->toBeEmpty()
        ->and($this->merger->needsReview($merged))->toBeFalse();
});

test('does not flag the same Arabic word spelled with ى on one page and ي on the next', function () {
    // Real false positive from صباح النور batch 2 (2026-07-28): "نصف سنوى" on
    // page 2 vs "نصف سنوي" on page 3 pushed a clean contract into manual review.
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => 'F-1', 'start_date' => '2026-07-01'],
        ['page_number' => 2, 'payment_frequency' => 'نصف سنوى', 'rent_value' => 24000, 'num_payments' => 2, 'payment_value' => 12000],
        ['page_number' => 3, 'payment_frequency' => 'نصف سنوي'],
    ]);

    expect($merged['conflicts'])->toBeEmpty()
        ->and($this->merger->needsReview($merged))->toBeFalse()
        ->and($merged['values']['payment_frequency'])->toBe('نصف سنوى'); // stores what the winning page said
});

test('still flags genuinely different Arabic values', function () {
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => 'G-1', 'start_date' => '2026-07-01', 'property_type' => 'محل'],
        ['page_number' => 2, 'property_type' => 'تجاري', 'rent_value' => 900, 'num_payments' => 1, 'payment_value' => 900],
    ]);

    expect($merged['conflicts'])->toHaveCount(1)
        ->and($merged['conflicts'][0])->toContain('property_type');
});

test('quotes conflicting pages verbatim rather than in the folded form', function () {
    // The reviewer compares the note against the PDF, so "المنطقة" must not be
    // shown back to them as "المنطقه" just because that is how we compared it.
    $merged = $this->merger->merge([
        ['page_number' => 1, 'contract_no' => 'H-1', 'start_date' => '2026-07-01', 'address' => 'الخبر, المنطقة الشرقية'],
        ['page_number' => 2, 'address' => 'الدمام', 'rent_value' => 900, 'num_payments' => 1, 'payment_value' => 900],
    ]);

    expect($merged['conflicts'][0])->toContain('المنطقة الشرقية');
});

test('ignores page order in the input', function () {
    $merged = $this->merger->merge([
        ['page_number' => 3, 'rent_value' => 700, 'num_payments' => 1, 'payment_value' => 700],
        ['page_number' => 1, 'contract_no' => 'ORD-1', 'start_date' => '2026-07-01'],
    ]);

    expect($merged['values']['contract_no'])->toBe('ORD-1')
        ->and($merged['conflicts'])->toBeEmpty();
});
