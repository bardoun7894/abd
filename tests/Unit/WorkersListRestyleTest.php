<?php

/**
 * Spec-less UI packet: restyle the WORKERS (employees) LIST page
 * (resources/views/dashboard/workers/view.blade.php + its DataTables
 * server-side partial tbl_workers.blade.php) to match the already-shipped
 * purchases/invoices extraction-log look (solid emerald header, dark
 * high-contrast ink, scoped .wrk-log wrapper CSS reusing --sn-* tokens,
 * subtle animation with prefers-reduced-motion opt-out).
 *
 * Mirrors tests/Unit/PurchaseListRestyleTest.php's approach: source-level
 * assertions (not full HTTP/DB renders) because the page depends on
 * Perm::/auth() gates that would need heavy fixture setup to render
 * end-to-end, and the risk this restyle must guard against is "did the
 * DataTables/JS wiring survive the markup edit" — fully verifiable by
 * asserting the exact hook strings are still present in the raw blade
 * source, plus a structural <th> column-count check so no column was
 * silently added/removed.
 */
uses(Tests\TestCase::class);

function workersViewSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/workers/view.blade.php'));
}

function workersTblSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/workers/tbl_workers.blade.php'));
}

// ---- hook preservation (must stay true before AND after the restyle) --------

it('keeps every filter-form / AJAX-refresh hook in workers view.blade.php intact', function () {
    $raw = workersViewSource();

    expect($raw)->toContain("action=\"{{ route('dashboard.workers.tbl') }}\"");
    expect($raw)->toContain('id="worker_name_v"');
    expect($raw)->toContain('id="ssn_v"');
    expect($raw)->toContain('id="work_place_id_v"');
    expect($raw)->toContain('id="job_id_v"');
    expect($raw)->toContain('id="manager_id_v"');
    expect($raw)->toContain('id="updatedcancal_at_v"');
    expect($raw)->toContain('id="end_dt_v"');
    expect($raw)->toContain('id="end_p_dt_v"');
    expect($raw)->toContain('id="inside_v"');
    expect($raw)->toContain('id="is_imp_v"');
    expect($raw)->toContain('id="nation_v"');
    expect($raw)->toContain('id="order_date"');
    expect($raw)->toContain('id="residence_month"');
    expect($raw)->toContain('id="residence_year"');
    expect($raw)->toContain('id="passport_month"');
    expect($raw)->toContain('id="passport_year"');
    expect($raw)->toContain('id="result_worker_tbl"');
    expect($raw)->toContain("route('dashboard.workers.del_workers')");
    expect($raw)->toContain('view_all_worker(');
    expect($raw)->toContain('id="kt_search"');
    expect($raw)->toContain('id="refresh"');
    expect($raw)->toContain('id="view_prim_const_m"');
    expect($raw)->toContain('id="view_prim_const_sm"');
});

it('keeps every DataTables wiring hook in tbl_workers.blade.php intact', function () {
    $raw = workersTblSource();

    expect($raw)->toContain('id="worker_tbl"');
    expect($raw)->toContain("route('dashboard.workers.ajax_search_workers')");
    expect($raw)->toContain('"serverSide": true');
    expect($raw)->toContain('targets: [1]');
    expect($raw)->toContain('"dataSrc": function(json)');
    expect($raw)->toContain('id=\'not_have_manger\'');
    expect($raw)->toContain('id=\'out_ksa\'');
    expect($raw)->toContain('id=\'in_ksa\'');
    expect($raw)->toContain('id=\'all_imp\'');
    expect($raw)->toContain('id=\'all_imp_not_cancal\'');
    expect($raw)->toContain('id=\'all_cancal\'');
    expect($raw)->toContain('Perm::get_function_access(12)');
    expect($raw)->toContain('Perm::get_function_access(19)');
    expect($raw)->toContain('d.worker_name =worker_name;');
    expect($raw)->toContain('d.residence_month = residence_month;');
    expect($raw)->toContain('d.passport_year = passport_year;');
    expect($raw)->toContain("\$('#worker_tbl tbody').on('click', 'tr'");
});

it('keeps the thead column count in tbl_workers.blade.php at exactly 20 <th> tags', function () {
    $raw = workersTblSource();
    preg_match('/<thead>.*?<\/thead>/s', $raw, $m);

    expect($m)->not->toBeEmpty();
    // Match "<th " / "<th>" only — a plain substr_count('<th') would also
    // match the wrapping "<thead>" tag itself, off-by-one-inflating the count.
    preg_match_all('/<th[ >]/', $m[0], $thMatches);
    expect(count($thMatches[0]))->toBe(20);
});

// ---- new behavior: this is the part that must be RED before the restyle -----

it('wraps workers view.blade.php content in the page-scoped .wrk-log wrapper carrying emerald-token CSS', function () {
    $raw = workersViewSource();

    expect($raw)->toContain('class="wrk-log"');
    expect($raw)->toContain("@section('styles')");
    expect($raw)->toContain('--sn-emerald');
    expect($raw)->toContain('prefers-reduced-motion');
});

it('marks the workers table for the page-scoped solid-emerald header treatment', function () {
    $raw = workersTblSource();

    expect($raw)->toContain('sn-thead');
});
