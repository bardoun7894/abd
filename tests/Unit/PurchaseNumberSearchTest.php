<?php

// Client video 2026-09-09 22:27: typing an invoice number into «بحث في سجل
// المشتريات» → «رقم الفاتورة» returns an empty table. Two causes, both here:
//   1. the field carried data-inputmask="'alias':'decimal'", so the 2,655 of
//      10,216 invoice numbers that contain letters or "/" (NHD252439396,
//      INV/2026/17095, 02944-3419-0031) could not even be typed into it;
//   2. the query matched with `= '<value> '`, so a partial number found nothing.
uses(Tests\TestCase::class);

use App\Models\Purchase;

it('does not put a numeric inputmask on a field that holds alphanumeric invoice numbers', function () {
    $blade = file_get_contents(__DIR__.'/../../resources/views/dashboard/purchase/view.blade.php');
    $pos = mb_strpos($blade, 'id="purchase_no_v"');
    expect($pos)->not->toBeFalse();

    $field = mb_substr($blade, $pos - 300, 600);
    expect($field)->not->toContain('inputmask');
});

it('matches a full invoice number, letters and slashes included', function () {
    expect(Purchase::purchaseNoWhere('p.', '2670343'))->toBe(" and p.purchase_no like '%2670343%' ");
    expect(Purchase::purchaseNoWhere('', 'NHD252439396'))->toBe(" and purchase_no like '%NHD252439396%' ");
    expect(Purchase::purchaseNoWhere('p.', 'INV/2026/17095'))->toBe(" and p.purchase_no like '%INV/2026/17095%' ");
});

it('matches a partial number — the whole point of a search box', function () {
    expect(Purchase::purchaseNoWhere('p.', '267034'))->toContain("like '%267034%'");
});

it('trims what the user typed and ignores an empty box', function () {
    expect(Purchase::purchaseNoWhere('p.', '  2670343  '))->toBe(" and p.purchase_no like '%2670343%' ");
    expect(Purchase::purchaseNoWhere('p.', ''))->toBe('');
    expect(Purchase::purchaseNoWhere('p.', '   '))->toBe('');
    expect(Purchase::purchaseNoWhere('p.', null))->toBe('');
});

// The page has two modes: «المشتريات» lists purchases attached to a group leader
// (manager_id, 1,063 rows) and «مشتريات المحلات» those attached to a shop
// (shop_id, 9,153 rows — and 3,344 of the 3,379 AI-pushed invoices). Searching a
// number in the wrong mode silently found nothing, which is what the video shows.
it('applies the shop/manager mode filter while listing', function () {
    expect(Purchase::modeWhere('p.', 'on', ''))
        ->toBe(" and p.manager_id is NULL and p.shop_id is not NULL ");
    expect(Purchase::modeWhere('', '', ''))
        ->toBe(" and manager_id is not NULL and shop_id is NULL ");
});

it('drops the mode filter when an invoice number is typed, so the invoice is found from either page', function () {
    expect(Purchase::modeWhere('p.', 'on', '2670343'))->toBe('');
    expect(Purchase::modeWhere('p.', '', '2670343'))->toBe('');
    expect(Purchase::modeWhere('p.', '', 'NHD2524'))->toBe('');
    // whitespace only is still "nothing typed"
    expect(Purchase::modeWhere('p.', '', '   '))->toBe(" and p.manager_id is not NULL and p.shop_id is NULL ");
});

it('escapes quotes and LIKE wildcards so a typed % cannot widen the search or break the SQL', function () {
    expect(Purchase::purchaseNoWhere('p.', "o'brien"))->toBe(" and p.purchase_no like '%o\\'brien%' ");
    expect(Purchase::purchaseNoWhere('p.', '100%'))->toBe(" and p.purchase_no like '%100\\%%' ");
    expect(Purchase::purchaseNoWhere('p.', 'a_b'))->toBe(" and p.purchase_no like '%a\\_b%' ");
    expect(Purchase::purchaseNoWhere('p.', "x' OR '1'='1"))->not->toContain("OR '1'='1'");
});
