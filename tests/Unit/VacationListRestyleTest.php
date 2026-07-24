<?php

/**
 * Spec-less UI packet: restyle the vacation LIST page
 * (resources/views/dashboard/vacation/view.blade.php + its DataTables
 * server-side partial tbl_vacation.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .vac-log wrapper CSS reusing --sn-* tokens,
 * subtle animation with prefers-reduced-motion opt-out).
 *
 * These tests are source-level (not full HTTP/DB renders) on purpose: the
 * page depends on Perm::/auth() gates that would need heavy fixture setup
 * to render end-to-end, and the risk this restyle must guard against is
 * "did the DataTables/JS wiring survive the markup edit" — which is fully
 * verifiable by asserting the exact hook strings are still present in the
 * raw blade source, plus a structural <th> column-count check so no column
 * was silently added/removed. BladeCompileSmokeTest already proves every
 * live view (including these two) still compiles.
 *
 * Mirrors tests/Unit/PurchaseListRestyleTest.php and
 * tests/Unit/ExpenseListRestyleTest.php.
 */
uses(Tests\TestCase::class);

function vacationViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/vacation/view.blade.php'));
}

function vacationTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/vacation/tbl_vacation.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in vacation view.blade.php intact', function () {
    $raw = vacationViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.vacation.tbl') }}\"");
    expect($raw)->toContain('id="vacation_month_desc_v"');
    expect($raw)->toContain('id="worker_id_v"');
    expect($raw)->toContain('id="vacation_type_id_v"');
    expect($raw)->toContain('id="result_vacation_tbl"');
    expect($raw)->toContain("route('dashboard.vacation.del_vacation')");
    expect($raw)->toContain('view_all_vacation(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
    expect($raw)->toContain("route('dashboard.report.print_vacation_pdf')");
    expect($raw)->toContain("route('dashboard.report.print_vacation_xlsx')");
});

it('keeps every DataTables wiring hook in tbl_vacation.blade.php intact', function () {
    $raw = vacationTblSource();

    expect($raw)->toContain('id="vacation_tbl"');
    expect($raw)->toContain("route('dashboard.vacation.ajax_search_vacation')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('Perm::get_function_access(65)');
    expect($raw)->toContain('Perm::get_function_access(66)');
    expect($raw)->toContain('Perm::get_function_access(67)');
    expect($raw)->toContain('d.vacation_month_desc =vacation_month_desc;');
    expect($raw)->toContain('d.worker_id = worker_id;');
    expect($raw)->toContain('d.vacation_type_id = vacation_type_id;');
});

it('keeps the thead column count in tbl_vacation.blade.php at exactly 13 <th> tags', function () {
    $raw = vacationTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(13);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps vacation view.blade.php content in the page-scoped .vac-log wrapper carrying emerald-token CSS', function () {
    $raw = vacationViewSource();

    expect($raw)->toContain('class="vac-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the vacation table for the page-scoped solid-emerald header treatment', function () {
    $raw = vacationTblSource();

    expect($raw)->toContain('sn-thead');
});
