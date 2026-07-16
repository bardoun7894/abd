<?php

use App\Services\InvoiceExtractionService;

beforeEach(function () {
    $this->svc = new InvoiceExtractionService();
});

/** A fully valid Saudi VAT invoice (sample fields from the client). */
function validInvoice(): array
{
    return [
        'supplier_name' => 'شركة نهلة الوادي للتجارة',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'NHD2522236491',
        'invoice_date' => '2026-06-15',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
    ];
}

it('passes a fully valid invoice', function () {
    $r = $this->svc->validate(validInvoice());
    expect($r['needs_review'])->toBeFalse();
    expect($r['notes'])->toBe([]);
});

it('flags a tax number that is not 15 digits', function () {
    $d = validInvoice();
    $d['supplier_tax_number'] = '12345';
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeTrue();
});

it('flags a tax number that does not start and end with 3', function () {
    $d = validInvoice();
    $d['supplier_tax_number'] = '100097525940001'; // 15 digits but starts/ends with 1
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeTrue();
});

it('flags VAT that is not ~15% of the base', function () {
    $d = validInvoice();
    $d['vat_amount'] = 12; // should be ~15
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeTrue();
});

it('flags totals that do not reconcile', function () {
    $d = validInvoice();
    $d['total_incl_vat'] = 130; // 100 + 15 != 130
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeTrue();
});

it('flags missing required fields', function () {
    $d = validInvoice();
    $d['supplier_name'] = null;
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeTrue();
});

it('accepts small rounding differences in VAT and total', function () {
    $d = validInvoice();
    $d['amount_before_vat'] = 333.33;
    $d['vat_amount'] = 50.00;        // 333.33 * 0.15 = 49.9995
    $d['total_incl_vat'] = 383.33;
    $r = $this->svc->validate($d);
    expect($r['needs_review'])->toBeFalse();
});

it('groups per-page rows into one record per invoice number', function () {
    $rows = [
        ['invoice_number' => 'A-1', 'page_number' => 1, 'supplier_name' => 'X', 'total_incl_vat' => 115.0],
        ['invoice_number' => 'B-2', 'page_number' => 2, 'supplier_name' => 'Y', 'total_incl_vat' => 230.0],
        ['invoice_number' => 'C-3', 'page_number' => 3, 'supplier_name' => 'Z', 'total_incl_vat' => 50.0],
    ];
    $out = $this->svc->groupByInvoiceNumber($rows);
    expect($out)->toHaveCount(3);
});

it('merges multi-page rows that share one invoice number', function () {
    $rows = [
        // page 1: header, no totals yet
        ['invoice_number' => 'INV-9', 'page_number' => 1, 'supplier_name' => 'Acme', 'total_incl_vat' => null, 'amount_before_vat' => null],
        // page 2: the summary page with the totals
        ['invoice_number' => 'INV-9', 'page_number' => 2, 'supplier_name' => null, 'total_incl_vat' => 575.0, 'amount_before_vat' => 500.0],
    ];
    $out = $this->svc->groupByInvoiceNumber($rows);
    expect($out)->toHaveCount(1);
    expect($out[0]['supplier_name'])->toBe('Acme');     // filled from page 1
    expect($out[0]['total_incl_vat'])->toBe(575.0);     // taken from the summary page
    expect($out[0]['page_number'])->toBe(1);            // starts at first page
});

it('keeps rows with no invoice number as separate invoices', function () {
    $rows = [
        ['invoice_number' => null, 'page_number' => 1, 'total_incl_vat' => 10.0],
        ['invoice_number' => null, 'page_number' => 2, 'total_incl_vat' => 20.0],
    ];
    expect($this->svc->groupByInvoiceNumber($rows))->toHaveCount(2);
});

it('normalizes Arabic digits, currency symbols and thousands separators', function () {
    $norm = $this->svc->normalize([
        'supplier_name' => '  مورد  ',
        'supplier_tax_number' => '300 097 525 940003',
        'invoice_number' => 'INV-1',
        'invoice_date' => '2026-06-15',
        'amount_before_vat' => '1,234.50 ر.س',
        'vat_amount' => '١٨٥.١٨',
        'total_incl_vat' => '1419.68',
    ]);

    expect($norm['supplier_name'])->toBe('مورد');
    expect($norm['supplier_tax_number'])->toBe('300097525940003');
    expect($norm['amount_before_vat'])->toBe(1234.50);
    expect($norm['vat_amount'])->toBe(185.18);
    expect($norm['invoice_date'])->toBe('2026-06-15');
});
