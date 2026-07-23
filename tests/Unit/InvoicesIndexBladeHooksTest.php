<?php

/**
 * Spec 012 — visual redesign of the extraction-log page (سجل عمليات الاستخراج)
 * must not break any of the JS hooks the page's @section('scripts') block
 * depends on. This is a source-level guard (not a full ->render(), which would
 * pull in layouts.app -> the whole menu tree — see ActivityLogTest.php for why
 * that pattern is avoided in this repo) that reads the raw blade template and
 * asserts every required id/class/attribute from the redesign brief's HARD
 * CONSTRAINTS list is still present, verbatim, after any restyling.
 */

uses(Tests\TestCase::class);

function invoicesIndexBladeSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/invoices/index.blade.php'));
}

it('keeps every JS-dependent hook required by the extraction-log scripts', function () {
    $src = invoicesIndexBladeSource();

    // Layout / directives
    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('scripts')");

    // Filter form
    expect($src)->toContain('<form method="GET"');
    expect($src)->toContain('name="q"');
    expect($src)->toContain('name="status"');
    expect($src)->toContain('name="date_from"');
    expect($src)->toContain('name="date_to"');
    expect($src)->toContain('name="min_count"');

    // Bulk selection
    expect($src)->toContain('id="selAll"');
    expect($src)->toContain('js-batch-chk');
    expect($src)->toContain('value="{{ $b->id }}"');

    // Bulk-action bar
    expect($src)->toContain('id="bulkBar"');
    expect($src)->toContain('id="bulkCount"');
    expect($src)->toContain('id="bulkPushOpenBtn"');
    expect($src)->toContain('id="bulkExportBtn"');

    // Row delete
    expect($src)->toContain('js-del-batch');
    expect($src)->toContain('data-id="{{ $b->id }}"');
    expect($src)->toContain('data-name="{{ $b->original_filename }}"');

    // Export route
    expect($src)->toContain("route('dashboard.invoices.export'");

    // Bulk push modal
    expect($src)->toContain('id="bulkPushModal"');
    expect($src)->toContain('id="bulkPushCount"');
    expect($src)->toContain('id="bulkShopId"');
    expect($src)->toContain('id="bulkManagerId"');
    expect($src)->toContain('id="bulkPushSubmitBtn"');
    expect($src)->toContain('id="bulkPushResult"');
    expect($src)->toContain('id="bulkPushModalLabel"');

    // الترحيل badge cell logic + status cell + forelse/empty structure
    expect($src)->toContain('$b->posted_count');
    expect($src)->toContain('$b->invoices_count');
    expect($src)->toContain('AuditLabels::statusColor($b->status)');
    expect($src)->toContain('AuditLabels::statusLabel($b->status)');
    expect($src)->toContain('@forelse ($batches as $b)');
    expect($src)->toContain('@empty');
    expect($src)->toContain('colspan="9"');
});

it('does not remove any wired JS function from the scripts block', function () {
    $src = invoicesIndexBladeSource();

    foreach ([
        'selectedBatchIds',
        'syncBulkBar',
        "\$(document).on('click', '.js-del-batch'",
        "\$(document).on('change', '#selAll'",
        "\$(document).on('change', '.js-batch-chk'",
        "\$('#bulkExportBtn').on('click'",
        "\$('#bulkPushOpenBtn').on('click'",
        "\$('#bulkPushSubmitBtn').on('click'",
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});
