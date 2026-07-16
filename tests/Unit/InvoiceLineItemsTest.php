<?php

use App\Services\InvoiceExtractionService;

beforeEach(function () {
    $this->svc = new InvoiceExtractionService();
});

// ---- line items (Spec 002 FR-102) -------------------------------------------

it('normalizes line items with typed values', function () {
    $rows = $this->svc->normalizeLineItems([
        ['name' => 'أسمنت', 'quantity' => '١٠', 'unit' => 'كيس', 'unit_price' => 'SAR 1,250.00', 'line_total' => '12500', 'vat_amount' => '1875'],
    ]);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['line_no'])->toBe(1);
    expect($rows[0]['name'])->toBe('أسمنت');
    expect($rows[0]['quantity'])->toBe(10.0);        // Arabic-Indic digits coerced
    expect($rows[0]['unit_price'])->toBe(1250.0);    // currency + thousands separator stripped, '.' = decimal
    expect($rows[0]['line_total'])->toBe(12500.0);
});

it('drops entirely empty line-item rows and handles non-array input', function () {
    expect($this->svc->normalizeLineItems(null))->toBe([]);
    expect($this->svc->normalizeLineItems('nope'))->toBe([]);
    $rows = $this->svc->normalizeLineItems([
        ['name' => null, 'quantity' => null, 'unit' => null, 'unit_price' => null, 'line_total' => null],
        ['name' => 'خرسانة', 'line_total' => 500],
    ]);
    expect($rows)->toHaveCount(1);
    expect($rows[0]['name'])->toBe('خرسانة');
});

// ---- per-field confidence (Spec 001 FR-002) ---------------------------------

it('keeps only 0..1 numeric per-field confidences', function () {
    $fc = $this->svc->normalizeFieldConfidence([
        'invoice_number' => 0.92,
        'supplier_name' => 1.5,     // clamped to 1
        'vat_amount' => -0.2,       // clamped to 0
        'invoice_date' => 'high',   // dropped (non-numeric)
    ]);

    expect($fc['invoice_number'])->toBe(0.92);
    expect($fc['supplier_name'])->toBe(1.0);
    expect($fc['vat_amount'])->toBe(0.0);
    expect($fc)->not->toHaveKey('invoice_date');
});

// ---- extended header fields (Spec 002 FR-101) -------------------------------

it('maps invoice type synonyms to tax|simplified', function () {
    expect($this->svc->normalize(['invoice_type' => 'فاتورة ضريبية'])['invoice_type'])->toBe('tax');
    expect($this->svc->normalize(['invoice_type' => 'مبسطة'])['invoice_type'])->toBe('simplified');
    expect($this->svc->normalize(['invoice_type' => 'simplified'])['invoice_type'])->toBe('simplified');
    expect($this->svc->normalize(['invoice_type' => 'weird'])['invoice_type'])->toBeNull();
});

it('normalizes the new header fields', function () {
    $n = $this->svc->normalize([
        'currency' => ' SAR ',
        'discount_total' => '١٠.٥٠',
        'vat_rate' => '15',
        'commercial_registration' => 'CR-1010',
        'payment_method' => 'نقدًا',
        'due_date' => '15-May-26',
    ]);

    expect($n['currency'])->toBe('SAR');
    expect($n['discount_total'])->toBe(10.50);
    expect($n['vat_rate'])->toBe(15.0);
    expect($n['commercial_registration'])->toBe('1010');   // digits only
    expect($n['payment_method'])->toBe('نقدًا');
    expect($n['due_date'])->toBe('2026-05-15');
});
