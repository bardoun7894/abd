<?php

/**
 * Spec-less UI packet: restyle the expenses LIST page
 * (resources/views/dashboard/expense/view.blade.php + its DataTables
 * server-side partial tbl_expense.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .exp-log wrapper CSS reusing --sn-* tokens,
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
 * Mirrors tests/Unit/PurchaseListRestyleTest.php.
 */
uses(Tests\TestCase::class);

function expenseViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/expense/view.blade.php'));
}

function expenseTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/expense/tbl_expense.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in expense view.blade.php intact', function () {
    $raw = expenseViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.expense.tbl') }}\"");
    expect($raw)->toContain('id="expense_type_id_v"');
    expect($raw)->toContain('id="det_calculate_month_remain_v"');
    expect($raw)->toContain('id="expense_categoty_id_v"');
    expect($raw)->toContain('id="expense_dt_from"');
    expect($raw)->toContain('id="expense_dt_to"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="worker_id_v"');
    expect($raw)->toContain('id="shop_id_v"');
    expect($raw)->toContain('id="result_expense_tbl"');
    expect($raw)->toContain("route('dashboard.expense.del_expense')");
    expect($raw)->toContain('view_all_expense(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
    expect($raw)->toContain('exportCSVExcel()');
});

it('keeps every DataTables wiring hook in tbl_expense.blade.php intact', function () {
    $raw = expenseTblSource();

    expect($raw)->toContain('id="expense_tbl"');
    expect($raw)->toContain("route('dashboard.expense.ajax_search_expense')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('targets: 1');
    expect($raw)->toContain('api.column(8)');
    expect($raw)->toContain('api.column(9)');
    expect($raw)->toContain('api.column(10)');
    expect($raw)->toContain('rowGroup');
    expect($raw)->toContain('dataSrc: [4]');
    expect($raw)->toContain('Perm::get_function_access(61)');
    expect($raw)->toContain('Perm::get_function_access(62)');
    expect($raw)->toContain('d.expense_type_id =expense_type_id;');
    expect($raw)->toContain('d.det_calculate_month_remain = det_calculate_month_remain;');
    expect($raw)->toContain('const numericColumns = [8, 9, 10, 11, 12];');
});

it('keeps the thead column count in tbl_expense.blade.php at exactly 19 <th> tags', function () {
    $raw = expenseTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    expect(substr_count($m[0], '<th'))->toBe(19);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps expense view.blade.php content in the page-scoped .exp-log wrapper carrying emerald-token CSS', function () {
    $raw = expenseViewSource();

    expect($raw)->toContain('class="exp-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the expenses table for the page-scoped solid-emerald header treatment', function () {
    $raw = expenseTblSource();

    expect($raw)->toContain('sn-thead');
});
