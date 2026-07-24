<?php

/**
 * Spec-less UI packet: restyle the SHOPS list page
 * (resources/views/dashboard/shop/view.blade.php + its DataTables
 * server-side partial tbl_shop.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .shop-log wrapper CSS reusing --sn-* tokens,
 * subtle animation with prefers-reduced-motion opt-out).
 *
 * Mirrors tests/Unit/PurchaseListRestyleTest.php. Source-level (not full
 * HTTP/DB renders) on purpose: the page depends on Perm::/auth() gates
 * that would need heavy fixture setup to render end-to-end, and the risk
 * this restyle must guard against is "did the DataTables/JS wiring
 * survive the markup edit" — which is fully verifiable by asserting the
 * exact hook strings are still present in the raw blade source, plus a
 * structural <th> column-count check so no column was silently
 * added/removed. BladeCompileSmokeTest already proves every live view
 * (including these two) still compiles.
 */
uses(Tests\TestCase::class);

function shopViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/shop/view.blade.php'));
}

function shopTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/shop/tbl_shop.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in shop/view.blade.php intact', function () {
    $raw = shopViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.shop.tbl') }}\"");
    expect($raw)->toContain('id="shop_name_v"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="shop_respon_v"');
    expect($raw)->toContain('id="shop_mobile_v"');
    expect($raw)->toContain('id="city_id_v"');
    expect($raw)->toContain('id="comme_no_v"');
    expect($raw)->toContain('id="municip_no_v"');
    expect($raw)->toContain('id="rentpay_price_v"');
    expect($raw)->toContain('id="order_date"');
    expect($raw)->toContain('id="rentpay_year"');
    expect($raw)->toContain('id="rentpay_month"');
    expect($raw)->toContain('id="municip_year"');
    expect($raw)->toContain('id="municip_month"');
    expect($raw)->toContain('id="comme_year"');
    expect($raw)->toContain('id="comme_month"');
    expect($raw)->toContain('id="result_shop_tbl"');
    expect($raw)->toContain("route('dashboard.shop.del_shop')");
    expect($raw)->toContain('view_all_shop(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
});

it('keeps every DataTables wiring hook in shop/tbl_shop.blade.php intact', function () {
    $raw = shopTblSource();

    expect($raw)->toContain('id="shop_table"');
    expect($raw)->toContain("route('dashboard.shop.ajax_search_shop')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('"className": "dt-center", "targets": "_all"');
    expect($raw)->toContain('responsivePriority: 1');
    expect($raw)->toContain('responsivePriority: 2');
    expect($raw)->toContain('responsivePriority: 3');
    expect($raw)->toContain('responsivePriority: 4');
    expect($raw)->toContain('Perm::get_function_access(32)');
    expect($raw)->toContain('Perm::get_function_access(33)');
    expect($raw)->toContain('Perm::get_function_access(34)');
    expect($raw)->toContain('Perm::get_function_access(35)');
    expect($raw)->toContain('Perm::get_function_access(36)');
    expect($raw)->toContain('Perm::get_function_access(37)');
    expect($raw)->toContain('Perm::get_function_access(38)');
    expect($raw)->toContain('d.shop_name =shop_name;');
    expect($raw)->toContain('d.rentpay_price=rentpay_price;');
    expect($raw)->toContain('d.comme_month = comme_month;');
});

it('keeps the thead column count in shop/tbl_shop.blade.php at exactly 18 <th> tags', function () {
    $raw = shopTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(18);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps shop/view.blade.php content in the page-scoped .shop-log wrapper carrying emerald-token CSS', function () {
    $raw = shopViewSource();

    expect($raw)->toContain('class="shop-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the shop table for the page-scoped solid-emerald header treatment', function () {
    $raw = shopTblSource();

    expect($raw)->toContain('sn-thead');
});
