<?php

use App\Services\DuplicateDetector;
use App\Services\InvoiceExtractionService;
use App\Services\SupplierMatcher;

// ---- SupplierMatcher (Spec 002 FR-105) --------------------------------------

it('scores identical supplier names as 1 and different names low', function () {
    expect(SupplierMatcher::nameSimilarity('شركة نهلة الوادي للتجارة', 'شركة نهلة الوادي للتجارة'))->toBe(1.0);
    expect(SupplierMatcher::nameSimilarity('Gulf Supplies Co.', 'Al Rajhi Bank'))->toBeLessThan(0.5);
});

it('is tolerant of company qualifier noise words', function () {
    // Same core name, different qualifiers/spacing -> high similarity.
    $s = SupplierMatcher::nameSimilarity('شركة نهلة الوادي للتجارة', 'نهلة الوادي');
    expect($s)->toBeGreaterThan(0.8);
});

it('ranks candidates by name similarity descending', function () {
    $ranked = SupplierMatcher::rankByName([
        ['id' => 1, 'name' => 'Al Rajhi Trading'],
        ['id' => 2, 'name' => 'Gulf Supplies Co.'],
        ['id' => 3, 'name' => 'Gulf Supplies Company'],
    ], 'Gulf Supplies Co.');

    expect($ranked[0]['id'])->toBeIn([2, 3]);          // the two Gulf entries rank top
    expect($ranked[0]['score'])->toBeGreaterThan($ranked[2]['score']);
});

// ---- DuplicateDetector (Spec 002 FR-106) ------------------------------------

it('treats an identical file hash as a certain duplicate', function () {
    $a = ['file_hash' => 'abc', 'invoice_number' => 'X', 'total_incl_vat' => 1];
    $b = ['file_hash' => 'abc', 'invoice_number' => 'Y', 'total_incl_vat' => 999];
    expect(DuplicateDetector::score($a, $b))->toBe(1.0);
});

it('scores same invoice number + tax + total as a high duplicate', function () {
    // Same invoice number (case/spacing tolerant via normNumber), same tax/total/date/supplier.
    $a = ['invoice_number' => 'INV-1', 'supplier_tax_number' => '300097525940003', 'total_incl_vat' => 115.00, 'invoice_date' => '2026-06-15', 'supplier_name' => 'Gulf'];
    $b = ['invoice_number' => ' inv-1 ', 'supplier_tax_number' => '300097525940003', 'total_incl_vat' => 115.00, 'invoice_date' => '2026-06-15', 'supplier_name' => 'Gulf'];
    expect(DuplicateDetector::score($a, $b))->toBeGreaterThanOrEqual(DuplicateDetector::BLOCK_THRESHOLD);
});

it('scores unrelated invoices low', function () {
    $a = ['invoice_number' => 'INV-1', 'supplier_tax_number' => '300097525940003', 'total_incl_vat' => 115.00];
    $b = ['invoice_number' => 'ZZ-9', 'supplier_tax_number' => '311223344550003', 'total_incl_vat' => 9999.00];
    expect(DuplicateDetector::score($a, $b))->toBeLessThan(DuplicateDetector::BLOCK_THRESHOLD);
});

it('computes a sha256 file hash', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'inv');
    file_put_contents($tmp, 'hello');
    expect(DuplicateDetector::fileHash($tmp))->toBe(hash('sha256', 'hello'));
    @unlink($tmp);
    expect(DuplicateDetector::fileHash('/no/such/file'))->toBeNull();
});

// ---- extended validation (Spec 002 FR-105) ----------------------------------

it('flags negative monetary values', function () {
    $svc = new InvoiceExtractionService();
    $r = $svc->validate(['amount_before_vat' => -100, 'vat_amount' => 15, 'total_incl_vat' => 115]);
    expect(implode(' ', $r['notes']))->toContain('قيمة سالبة');
});

it('flags line items that do not reconcile with the invoice total', function () {
    $svc = new InvoiceExtractionService();
    $r = $svc->validate([
        'amount_before_vat' => 100, 'vat_amount' => 15, 'total_incl_vat' => 115,
        'line_items' => [['line_total' => 40], ['line_total' => 20]],   // sum 60 != 100
    ]);
    expect(implode(' ', $r['notes']))->toContain('مجموع البنود');
});

it('does not flag a missing tax number for a simplified invoice', function () {
    $svc = new InvoiceExtractionService();
    $r = $svc->validate([
        'invoice_type' => 'simplified',
        'supplier_name' => 'x', 'invoice_number' => 'i', 'invoice_date' => '2026-01-01',
        'amount_before_vat' => 100, 'vat_amount' => 15, 'total_incl_vat' => 115,
    ]);
    expect(implode(' ', $r['notes']))->not->toContain('supplier_tax_number');
});
