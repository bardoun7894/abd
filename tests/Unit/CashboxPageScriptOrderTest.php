<?php

uses(Tests\TestCase::class);

/**
 * Regression guard for a bug that made الصندوق look empty in production for its
 * whole life: dashboard/cashbox/index.blade.php loaded the DataTables bundle and
 * ran `$(function(){...})` from inside @section('content').
 *
 * layouts/app.blade.php yields 'content' BEFORE it loads scripts.bundle.js (which
 * carries jQuery) and yields 'scripts' after it. So the page threw
 * "ReferenceError: jQuery is not defined", DataTables never initialised, and the
 * table stayed empty even though ajax_search returned every row correctly — the
 * backend was fine the entire time, which is exactly why it went unnoticed.
 *
 * Source-level, matching the house convention for JS-heavy blades
 * (InvoiceTransferUiTest): the page is behind a Perm gate and renders its rows
 * client-side, so "are the scripts on the right side of jQuery" is the only
 * meaningful assertion.
 */
function cashboxIndexSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/cashbox/index.blade.php'));
}

it('loads jQuery-dependent scripts in @section(\'scripts\'), never in content', function () {
    $src = cashboxIndexSource();

    $contentAt = strpos($src, "@section('content')");
    $scriptsAt = strpos($src, "@section('scripts')");

    expect($contentAt)->not->toBeFalse();
    expect($scriptsAt)->not->toBeFalse();
    expect($scriptsAt)->toBeGreaterThan($contentAt);

    // Nothing that needs jQuery may appear before the scripts section.
    $beforeScripts = substr($src, 0, $scriptsAt);
    expect($beforeScripts)->not->toContain('datatables.bundle.js');
    expect($beforeScripts)->not->toContain('$(function');
    expect($beforeScripts)->not->toContain('.DataTable(');
});

it('keeps the void modal (which binds jQuery handlers) after jQuery too', function () {
    $src = cashboxIndexSource();

    $scriptsAt = strpos($src, "@section('scripts')");
    $modalAt = strpos($src, "@include('dashboard.cashbox.void_modal')");

    expect($modalAt)->not->toBeFalse();
    expect($modalAt)->toBeGreaterThan($scriptsAt);

    // And it really does need jQuery at load time — if that ever stops being
    // true this test's premise is gone and it should be revisited, not deleted.
    $modal = file_get_contents(base_path('resources/views/dashboard/cashbox/void_modal.blade.php'));
    expect($modal)->toContain('$(function');
});

it('still keeps the DataTables stylesheet in the styles section', function () {
    // The CSS has no jQuery dependency and belongs in <head>, so it must NOT
    // have been swept into the scripts move.
    expect(cashboxIndexSource())->toContain("@section('styles')");
    expect(cashboxIndexSource())->toContain('datatables.bundle.css');
});
