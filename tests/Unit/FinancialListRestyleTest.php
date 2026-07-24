<?php

/**
 * Spec-less UI packet: restyle the FINANCIAL list page
 * (resources/views/dashboard/financial/view.blade.php + its DataTables
 * server-side partial tbl_financial.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .fin-log wrapper CSS reusing --sn-* tokens,
 * subtle animation with prefers-reduced-motion opt-out).
 *
 * These tests are source-level (not full HTTP/DB renders) on purpose, mirroring
 * tests/Unit/PurchaseListRestyleTest.php: the page depends on Perm::/auth()
 * gates that would need heavy fixture setup to render end-to-end, and the risk
 * this restyle must guard against is "did the DataTables/JS wiring survive the
 * markup edit" — which is fully verifiable by asserting the exact hook strings
 * are still present in the raw blade source, plus a structural <th>
 * column-count check so no column was silently added/removed.
 * BladeCompileSmokeTest already proves every live view (including these two)
 * still compiles.
 */
uses(Tests\TestCase::class);

function financialViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/financial/view.blade.php'));
}

function financialTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/financial/tbl_financial.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in view.blade.php intact', function () {
    $raw = financialViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.financial.tbl') }}\"");
    expect($raw)->toContain('id="worker_id_v"');
    expect($raw)->toContain('id="from"');
    expect($raw)->toContain('id="to"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="result_financial_tbl"');
    expect($raw)->toContain("route('dashboard.financial.del_financial')");
    expect($raw)->toContain('view_all_financial(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain("route('dashboard.report.print_fnancial_pdf')");
    expect($raw)->toContain("route('dashboard.report.print_fnancial_xlsx')");
    expect($raw)->toContain('print_fnancial_pdf(');
    expect($raw)->toContain('print_fnancial_xlsx(');
    expect($raw)->toContain("route('dashboard.financial.cronadd')");
    expect($raw)->toContain("route('dashboard.financial.ai_insights')");
});

it('keeps every DataTables wiring hook in tbl_financial.blade.php intact', function () {
    $raw = financialTblSource();

    expect($raw)->toContain('id="financial_tbl"');
    expect($raw)->toContain("route('dashboard.financial.ajax_search_financial')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('targets: 1');
    expect($raw)->toContain("id='sum_c1'");
    expect($raw)->toContain("id='sum_sum_det_financial_month_pay_All'");
    expect($raw)->toContain("id='sum_xx'");
    expect($raw)->toContain('api.column(5)');
    expect($raw)->toContain('api.column(6');
    expect($raw)->toContain('api.column(7');
    expect($raw)->toContain('api.column(8');
    expect($raw)->toContain('Perm::get_function_access(22)');
    expect($raw)->toContain('Perm::get_function_access(23)');
    expect($raw)->toContain('Perm::get_function_access(24)');
    expect($raw)->toContain('Perm::get_function_access(25)');
    expect($raw)->toContain('d.financial_month_desc =financial_month_desc;');
    expect($raw)->toContain('d.worker_id = worker_id;');
    expect($raw)->toContain('d.manager_id = manager_id;');
    expect($raw)->toContain('d.from = from ;');
    expect($raw)->toContain('d.to = to ;');
});

it('keeps the thead column count in tbl_financial.blade.php at exactly 14 <th tokens (13 columns + the <thead> tag itself)', function () {
    $raw = financialTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(14);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps view.blade.php content in the page-scoped .fin-log wrapper carrying emerald-token CSS', function () {
    $raw = financialViewSource();

    expect($raw)->toContain('class="fin-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the financial table for the page-scoped solid-emerald header treatment', function () {
    $raw = financialTblSource();

    expect($raw)->toContain('sn-thead');
});
