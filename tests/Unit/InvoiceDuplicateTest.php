<?php

use App\Services\InvoiceExtractionService;

it('normalizes invoice numbers for comparison (trim, spaces, case)', function () {
    expect(InvoiceExtractionService::normNumber('  dn 123 '))->toBe('DN123');
    expect(InvoiceExtractionService::normNumber('DN123'))->toBe('DN123');
});

it('finds invoice numbers that repeat', function () {
    $dups = InvoiceExtractionService::duplicateNumbers(['DN1', 'DN2', 'DN1', 'DN3']);

    expect($dups)->toContain('DN1');
    expect($dups)->not->toContain('DN2');
    expect($dups)->not->toContain('DN3');
});

it('treats spacing/case variants as the same number', function () {
    $dups = InvoiceExtractionService::duplicateNumbers(['DN 123', 'dn123']);

    expect($dups)->toBe(['DN123']);
});

it('ignores empty / null numbers', function () {
    expect(InvoiceExtractionService::duplicateNumbers([null, '', '  ', null]))->toBe([]);
});

it('returns nothing when all numbers are unique', function () {
    expect(InvoiceExtractionService::duplicateNumbers(['A', 'B', 'C']))->toBe([]);
});
