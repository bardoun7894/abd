<?php

/**
 * Spec 024 F1 — per-invoice branch transfer UI on the batch-detail grid
 * (show.blade.php, JS-rendered from status() JSON) and the cross-batch fix
 * center (needs_fix.blade.php, server-rendered Blade @foreach).
 *
 * Source-level (not full HTTP/DB renders), mirroring the house convention in
 * InvoicesIndexBladeHooksTest.php / ShopListRestyleTest.php: these pages sit
 * behind Perm::/auth() gates and (for show.blade.php) an entirely
 * client-rendered invoice grid, so the only meaningfully-testable surface is
 * "are the required hooks/strings still present in the raw blade source".
 * BladeCompileSmokeTest already proves every touched view still compiles.
 */
uses(Tests\TestCase::class);

function invoicesShowBladeSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/invoices/show.blade.php'));
}

function invoicesNeedsFixBladeSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/invoices/needs_fix.blade.php'));
}

it('adds a per-invoice checkbox column + master checkbox + transfer bar/modal to show.blade.php', function () {
    $src = invoicesShowBladeSource();

    // Master checkbox + per-row checkbox hook (rows are built in JS render()).
    expect($src)->toContain('id="invSelAll"');
    expect($src)->toContain('js-inv-chk');

    // Action bar, mirroring #bulkBar from invoices/index.blade.php.
    expect($src)->toContain('id="invBulkBar"');
    expect($src)->toContain('id="invPushOpenBtn"');

    // Shop/manager picker modal (copied pattern from index.blade.php).
    expect($src)->toContain('id="invPushModal"');
    expect($src)->toContain('id="invShopId"');
    expect($src)->toContain('id="invManagerId"');
    expect($src)->toContain('id="invPushSubmitBtn"');
    expect($src)->toContain('id="invPushResult"');

    // Wired to the new per-invoice push endpoint.
    expect($src)->toContain("route('dashboard.invoices.push-invoices')");

    // "الفرع المُرحّل إليه" column, forward-compatible with the (not-yet-exposed)
    // transferred_branch_label/transferred_at fields on the status() JSON payload.
    expect($src)->toContain('الفرع المُرحّل إليه');
    expect($src)->toContain('transferred_branch_label');
});

it('gates the re-route control behind @if($canReroute) and wires it to the reroute route', function () {
    $src = invoicesShowBladeSource();

    expect($src)->toContain('@if($canReroute)');
    expect($src)->toContain('js-inv-reroute');
    expect($src)->toContain("'/reroute'");
});

it('adds a per-invoice checkbox column + master checkbox + transfer bar/modal to needs_fix.blade.php', function () {
    $src = invoicesNeedsFixBladeSource();

    expect($src)->toContain('id="fixInvSelAll"');
    expect($src)->toContain('js-inv-chk');
    expect($src)->toContain('value="{{ $inv->id }}"');

    expect($src)->toContain('id="fixInvBar"');
    expect($src)->toContain('id="fixInvPushOpenBtn"');

    expect($src)->toContain('id="fixInvPushModal"');
    expect($src)->toContain('id="fixInvShopId"');
    expect($src)->toContain('id="fixInvManagerId"');
    expect($src)->toContain('id="fixInvPushSubmitBtn"');
    expect($src)->toContain('id="fixInvPushResult"');

    expect($src)->toContain("route('dashboard.invoices.push-invoices')");
});
