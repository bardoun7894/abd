<?php

use App\Services\LeaseExtractionService;

beforeEach(function () {
    $this->svc = new LeaseExtractionService();
});

/** A fully valid extracted lease (raw Gemini-shaped fields, before normalize()). */
function validLease(): array
{
    return [
        'contract_no' => 'LC-2026-014',
        'tenant_name' => 'شركة الأفق التجارية',
        'tenant_id_no' => '1012345678',
        'landlord_name' => 'محمد العتيبي',
        'landlord_id_no' => '1098765432',
        'property_no' => '55',
        'unit' => '7',
        'property_type' => 'محل تجاري',
        'address' => 'الرياض، حي العليا',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'duration' => 'سنة واحدة',
        'rent_value' => 12000,
        'num_payments' => 12,
        'payment_value' => 1000,
        'payment_frequency' => 'شهري',
        'deposit' => 2000,
        'payment_method' => 'نقدًا',
    ];
}

it('normalizes Arabic digits, currency and whitespace on a lease', function () {
    $norm = $this->svc->normalize([
        'contract_no' => '  LC-2026-014  ',
        'tenant_name' => '  شركة الأفق  ',
        'rent_value' => '١٢٬٠٠٠.٠٠ ر.س',
        'num_payments' => '١٢',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);

    expect($norm['contract_no'])->toBe('LC-2026-014');
    expect($norm['tenant_name'])->toBe('شركة الأفق');
    expect($norm['rent_value'])->toBe(12000.0);
    expect($norm['num_payments'])->toBe(12);
    expect($norm['start_date'])->toBe('2026-01-01');
    expect($norm['end_date'])->toBe('2026-12-31');
});

it('passes a fully valid lease', function () {
    $r = $this->svc->validate($this->svc->normalize(validLease()));
    expect($r['needs_review'])->toBeFalse();
    expect($r['notes'])->toBe([]);
});

it('flags a missing rent_value', function () {
    $d = validLease();
    $d['rent_value'] = null;
    $r = $this->svc->validate($this->svc->normalize($d));
    expect($r['needs_review'])->toBeTrue();
});

it('flags a rent_value that is not positive', function () {
    $d = validLease();
    $d['rent_value'] = 0;
    $r = $this->svc->validate($this->svc->normalize($d));
    expect($r['needs_review'])->toBeTrue();
});

it('flags end_date before start_date', function () {
    $d = validLease();
    $d['start_date'] = '2026-12-31';
    $d['end_date'] = '2026-01-01';
    $r = $this->svc->validate($this->svc->normalize($d));
    expect($r['needs_review'])->toBeTrue();
    expect($r['notes'])->toContain('تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
});

it('flags a missing tenant_name or landlord_name', function () {
    $d = validLease();
    $d['tenant_name'] = null;
    $r = $this->svc->validate($this->svc->normalize($d));
    expect($r['needs_review'])->toBeTrue();

    $d2 = validLease();
    $d2['landlord_name'] = null;
    $r2 = $this->svc->validate($this->svc->normalize($d2));
    expect($r2['needs_review'])->toBeTrue();
});

it('flags a missing start_date or end_date', function () {
    $d = validLease();
    $d['start_date'] = null;
    $r = $this->svc->validate($this->svc->normalize($d));
    expect($r['needs_review'])->toBeTrue();
});

it('keeps only 0..1 numeric per-field confidences', function () {
    $fc = $this->svc->normalizeFieldConfidence([
        'contract_no' => 0.9, 'rent_value' => 1.5, 'tenant_name' => -0.2, 'unit' => 'high',
    ]);

    expect($fc['contract_no'])->toBe(0.9);
    expect($fc['rent_value'])->toBe(1.0);
    expect($fc['tenant_name'])->toBe(0.0);
    expect($fc)->not->toHaveKey('unit');
});
