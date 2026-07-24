<?php

/**
 * Spec-less UI packet: restyle the purchases LIST page
 * (resources/views/dashboard/purchase/view.blade.php + its DataTables
 * server-side partial tbl_purchase.blade.php) to match the already-shipped
 * invoices extraction-log look (solid emerald header, dark high-contrast
 * ink, scoped .pur-log wrapper CSS reusing --sn-* tokens, subtle animation
 * with prefers-reduced-motion opt-out).
 *
 * These tests are source-level (not full HTTP/DB renders) on purpose: the
 * page depends on Perm::/auth() gates that would need heavy fixture setup
 * to render end-to-end, and the risk this restyle must guard against is
 * "did the DataTables/JS wiring survive the markup edit" — which is fully
 * verifiable by asserting the exact hook strings are still present in the
 * raw blade source, plus a structural <th> column-count check so no column
 * was silently added/removed. BladeCompileSmokeTest already proves every
 * live view (including these two) still compiles.
 */
uses(Tests\TestCase::class);

function purchaseViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/purchase/view.blade.php'));
}

function purchaseTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/purchase/tbl_purchase.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in view.blade.php intact', function () {
    $raw = purchaseViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.purchase.tbl') }}\"");
    expect($raw)->toContain('id="purchase_no_v"');
    expect($raw)->toContain('id="purchase_dt_from"');
    expect($raw)->toContain('id="purchase_dt_to"');
    expect($raw)->toContain('id="purchase_respon_v"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="create_users"');
    expect($raw)->toContain('id="shop_id"');
    expect($raw)->toContain('id="result_purchase_tbl"');
    expect($raw)->toContain("route('dashboard.purchase.del_purchase')");
    expect($raw)->toContain('view_all_purchase(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
});

it('keeps every DataTables wiring hook in tbl_purchase.blade.php intact', function () {
    $raw = purchaseTblSource();

    expect($raw)->toContain('id="purchase_tbl"');
    expect($raw)->toContain("route('dashboard.purchase.ajax_search_purchase')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('targets: 1');
    expect($raw)->toContain('id="tex"');
    expect($raw)->toContain('id="without_tex"');
    expect($raw)->toContain('api.column(3)');
    expect($raw)->toContain('api.column(4');
    expect($raw)->toContain('api.column(5');
    expect($raw)->toContain('Perm::get_function_access(57)');
    expect($raw)->toContain('Perm::get_function_access(58)');
    expect($raw)->toContain('d.purchase_no =purchase_no;');
    expect($raw)->toContain('d.shops = shops;');
});

it('keeps the thead column count in tbl_purchase.blade.php at exactly 15 <th> tags', function () {
    $raw = purchaseTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(15);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps view.blade.php content in the page-scoped .pur-log wrapper carrying emerald-token CSS', function () {
    $raw = purchaseViewSource();

    expect($raw)->toContain('class="pur-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the purchases table for the page-scoped solid-emerald header treatment', function () {
    $raw = purchaseTblSource();

    expect($raw)->toContain('sn-thead');
});
