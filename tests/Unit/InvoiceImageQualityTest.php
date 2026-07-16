<?php

use App\Services\InvoiceExtractionService;

beforeEach(function () {
    $this->svc = new InvoiceExtractionService();
});

/** A fully valid invoice so the only review trigger under test is image quality. */
function qInvoice(array $overrides = []): array
{
    return array_merge([
        'supplier_name' => 'مورد',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'X1',
        'invoice_date' => '2026-06-15',
        'amount_before_vat' => 100.0,
        'vat_amount' => 15.0,
        'total_incl_vat' => 115.0,
    ], $overrides);
}

it('normalizes image_quality to the three allowed levels', function () {
    expect($this->svc->normalize(['image_quality' => 'clear'])['image_quality'])->toBe('clear');
    expect($this->svc->normalize(['image_quality' => 'MEDIUM'])['image_quality'])->toBe('medium');
    expect($this->svc->normalize(['image_quality' => 'unclear'])['image_quality'])->toBe('unclear');
});

it('maps quality synonyms and leaves unknown/empty as null', function () {
    expect($this->svc->normalize(['image_quality' => 'good'])['image_quality'])->toBe('clear');
    expect($this->svc->normalize(['image_quality' => 'high'])['image_quality'])->toBe('clear');
    expect($this->svc->normalize(['image_quality' => 'blurry'])['image_quality'])->toBe('unclear');
    expect($this->svc->normalize(['image_quality' => 'low'])['image_quality'])->toBe('unclear');
    expect($this->svc->normalize(['image_quality' => null])['image_quality'])->toBeNull();
    expect($this->svc->normalize([])['image_quality'])->toBeNull();
});

it('flags an unclear image for review even when all fields are valid', function () {
    $r = $this->svc->validate(qInvoice(['image_quality' => 'unclear']));

    expect($r['needs_review'])->toBeTrue();
    expect(collect($r['notes'])->contains(fn ($n) => str_contains($n, 'غير واضحة')))->toBeTrue();
});

it('flags a medium image for review too — numbers on imperfect scans must be verified', function () {
    $r = $this->svc->validate(qInvoice(['image_quality' => 'medium']));

    expect($r['needs_review'])->toBeTrue();
    expect(collect($r['notes'])->contains(fn ($n) => str_contains($n, 'متوسطة')))->toBeTrue();
});

it('does not force review for a clear image when data is valid', function () {
    expect($this->svc->validate(qInvoice(['image_quality' => 'clear']))['needs_review'])->toBeFalse();
});
