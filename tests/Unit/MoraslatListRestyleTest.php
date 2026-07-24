<?php

/**
 * Spec-less UI packet: restyle the correspondence (moraslat) LIST page
 * (resources/views/dashboard/moraslat/view.blade.php + its DataTables
 * server-side partial tbl_moraslat.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .mor-log wrapper CSS reusing --sn-* tokens,
 * subtle animation with prefers-reduced-motion opt-out).
 *
 * These tests are source-level (not full HTTP/DB renders) on purpose,
 * mirroring tests/Unit/PurchaseListRestyleTest.php: the page depends on
 * Perm::/auth() gates and select2 AJAX wiring that would need heavy
 * fixture setup to render end-to-end, and the risk this restyle must
 * guard against is "did the DataTables/JS wiring survive the markup
 * edit" — which is fully verifiable by asserting the exact hook strings
 * are still present in the raw blade source, plus a structural <th>
 * column-count check so no column was silently added/removed.
 */
uses(Tests\TestCase::class);

function moraslatViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/moraslat/view.blade.php'));
}

function moraslatTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/moraslat/tbl_moraslat.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in view.blade.php intact', function () {
    $raw = moraslatViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.moraslat.tbl') }}\"");
    expect($raw)->toContain('id="moraslat_id_v"');
    expect($raw)->toContain('id="moraslat_type_id_v"');
    expect($raw)->toContain('id="moraslat_categoty_id_v"');
    expect($raw)->toContain('id="moraslat_dt_from"');
    expect($raw)->toContain('id="moraslat_dt_to"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="worker_id_v"');
    expect($raw)->toContain('id="shop_id_v"');
    expect($raw)->toContain('id="moraslat_status_id_v"');
    expect($raw)->toContain('id="result_moraslat_tbl"');
    expect($raw)->toContain("route('dashboard.moraslat.del_moraslat')");
    expect($raw)->toContain('view_all_moraslat(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
    expect($raw)->toContain("route('dashboard.general.sel_worker_list')");
    expect($raw)->toContain("route('dashboard.general.sel_shop_list')");
});

it('keeps every DataTables wiring hook in tbl_moraslat.blade.php intact', function () {
    $raw = moraslatTblSource();

    expect($raw)->toContain('id="moraslat_tbl"');
    expect($raw)->toContain("route('dashboard.moraslat.ajax_search_moraslat')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('"className": "dt-center", "targets": "_all"');
    expect($raw)->toContain('responsivePriority: 1');
    expect($raw)->toContain('responsivePriority: 2');
    expect($raw)->toContain('responsivePriority: 3');
    expect($raw)->toContain('responsivePriority: 4');
    expect($raw)->toContain('Perm::get_function_access(51)');
    expect($raw)->toContain('Perm::get_function_access(52)');
    expect($raw)->toContain('Perm::get_function_access(53)');
    expect($raw)->toContain('Perm::get_function_access(54)');
    expect($raw)->toContain('d.moraslat_type_id =moraslat_type_id;');
    expect($raw)->toContain('d.moraslat_status_id = moraslat_status_id;');
});

it('keeps the thead column count in tbl_moraslat.blade.php at exactly 14 <th> tags', function () {
    $raw = moraslatTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(14);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps view.blade.php content in the page-scoped .mor-log wrapper carrying emerald-token CSS', function () {
    $raw = moraslatViewSource();

    expect($raw)->toContain('class="mor-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the moraslat table for the page-scoped solid-emerald header treatment', function () {
    $raw = moraslatTblSource();

    expect($raw)->toContain('sn-thead');
});
