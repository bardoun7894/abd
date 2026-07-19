<?php

use App\Models\InvoiceBatch;
use App\Services\InvoicePurchaseMapper;

/**
 * The mapper's field-mapping core is a pure function over an invoice's raw
 * attributes, so it can be tested without the (remote) main DB. The actual
 * cross-connection insert into `purchase` is thin glue verified against prod.
 */
function mappableInvoice(array $overrides = []): array
{
    return array_merge([
        'batch_id' => 7,
        'page_number' => 2,
        'image_path' => 'uploads/invoices/pages/batch_7/p2.png',
        'supplier_name' => 'شركة نهلة الوادي للتجارة',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'NHD2522236491',
        'invoice_date' => '2026-06-15',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'status' => 'done',
        'needs_review' => false,
        'purchase_id' => null,
    ], $overrides);
}

it('maps invoice fields onto the purchase columns', function () {
    $row = InvoicePurchaseMapper::buildPurchaseRow(mappableInvoice(), 12, null, 3);

    expect($row['purchase_no'])->toBe('NHD2522236491');
    expect($row['purchase_dt'])->toBe('2026-06-15');
    expect((float) $row['purchase_price'])->toBe(115.0); // total incl VAT — "based in facture"
    expect($row['tax_number'])->toBe('300097525940003');
    expect($row['purchase_respon'])->toBe('شركة نهلة الوادي للتجارة');
    expect($row['shop_id'])->toBe(12);
    expect($row['manager_id'])->toBeNull();
    expect($row['create_user'])->toBe(3);
});

it('preserves the VAT breakdown in the note (purchase has no VAT column)', function () {
    $row = InvoicePurchaseMapper::buildPurchaseRow(mappableInvoice(), null, 4, 3);

    expect($row['note'])->toContain('15');  // vat amount
    expect($row['note'])->toContain('100'); // amount before vat
    expect($row['manager_id'])->toBe(4);
    expect($row['shop_id'])->toBeNull();
});

it('normalises the invoice number and date', function () {
    $row = InvoicePurchaseMapper::buildPurchaseRow(
        mappableInvoice(['invoice_number' => '  ABC123  ', 'invoice_date' => '2026-06-15 00:00:00']),
        1, null, 1
    );

    expect($row['purchase_no'])->toBe('ABC123');
    expect($row['purchase_dt'])->toBe('2026-06-15');
});

it('treats a clean done invoice as eligible', function () {
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice()))->toBeTrue();
});

it('rejects invoices that need review, are unfinished, already mapped, or missing key fields', function () {
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['needs_review' => true])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['status' => 'failed'])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['status' => 'processing'])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['purchase_id' => 99])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['invoice_number' => null])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['invoice_date' => null])))->toBeFalse();
    expect(InvoicePurchaseMapper::isEligible(mappableInvoice(['total_incl_vat' => null])))->toBeFalse();
});

it('refuses to push when neither shop nor manager is chosen', function () {
    expect(fn () => (new InvoicePurchaseMapper())->push(new InvoiceBatch(), null, null, 1))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses to push when both shop and manager are chosen', function () {
    expect(fn () => (new InvoicePurchaseMapper())->push(new InvoiceBatch(), 5, 6, 1))
        ->toThrow(InvalidArgumentException::class);
});

it('detects the purchase_attach fk and file columns by common naming', function () {
    $m = InvoicePurchaseMapper::detectAttachColumns(
        ['purchase_attach_id', 'purchase_id', 'attach_url', 'create_user', 'created_at']
    );

    expect($m['fk'])->toBe('purchase_id');
    expect($m['file'])->toBe('attach_url');
    expect($m['create_user'])->toBeTrue();
    expect($m['created_at'])->toBeTrue();
});

it('returns null when the purchase_attach schema has no recognizable file column', function () {
    expect(InvoicePurchaseMapper::detectAttachColumns(['purchase_attach_id', 'purchase_id', 'foo', 'bar']))->toBeNull();
    expect(InvoicePurchaseMapper::detectAttachColumns(['id', 'name']))->toBeNull();
});

it('classifies a duplicate-key DB violation as a duplicate (blocked, not an error)', function () {
    $prev = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'NHD-1' for key 'purchase_no'");
    $prev->errorInfo = ['23000', 1062, "Duplicate entry 'NHD-1'"];
    $qe = new \Illuminate\Database\QueryException('mysql', 'insert into `purchase` ...', [], $prev);

    expect(\App\Services\InvoicePurchaseMapper::isDuplicateKeyViolation($qe))->toBeTrue();
});

it('does NOT misclassify an unrelated DB error as a duplicate', function () {
    $prev = new \PDOException("SQLSTATE[HY000]: General error: 1364 Field 'x' doesn't have a default value");
    $prev->errorInfo = ['HY000', 1364, "Field 'x' has no default"];
    $qe = new \Illuminate\Database\QueryException('mysql', 'insert into `purchase` ...', [], $prev);

    expect(\App\Services\InvoicePurchaseMapper::isDuplicateKeyViolation($qe))->toBeFalse();
});
