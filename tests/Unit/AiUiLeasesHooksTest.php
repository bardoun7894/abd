<?php

/**
 * Spec shared AI-page brand UI — apply the extraction-log redesign to the
 * three lease blades without breaking any JS hooks. Source-level guard that
 * reads the raw blade templates and asserts the ai-page-styles include,
 * ai-page wrapper, and every JS-dependent id/class/data-* are still present.
 */

uses(Tests\TestCase::class);

function leasesBladeSource(string $name): string
{
    return file_get_contents(base_path("resources/views/dashboard/leases/{$name}.blade.php"));
}

// -----------------------------------------------------------------------------
// leases/index.blade.php
// -----------------------------------------------------------------------------
it('brands leases/index with ai-page styles and wrapper while keeping hooks', function () {
    $src = leasesBladeSource('index');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');

    foreach ([
        "route('dashboard.leases.create')",
        "route('dashboard.leases.show', \$b->id)",
        '$b->id', '$b->original_filename', '$b->processed_pages', '$b->total_pages',
        '$b->status', '$b->created_at', '@forelse', '@empty', '@endforelse',
        'sn-thead', 'sn-row-hover', 'sn-num',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});

// -----------------------------------------------------------------------------
// leases/unprocessed.blade.php
// -----------------------------------------------------------------------------
it('brands leases/unprocessed with ai-page styles and wrapper while keeping hooks', function () {
    $src = leasesBladeSource('unprocessed');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
    expect($src)->toContain("@section('scripts')");

    foreach ([
        'reprocessBtn', 'rejectBtn', 'delBtn',
        'data-id="{{ $e->id }}"',
        "route('dashboard.leases.show', \$e->batch_id)",
        '$e->id', '$e->batch_id', '$e->contract_no', '$e->tenant_name',
        '$e->status', '$e->validation_notes', '$e->error_message',
        'sn-thead', 'sn-lease-review', 'sn-row-hover',
        'sn-col-id', 'sn-col-batch', 'sn-col-contract', 'sn-col-tenant',
        'sn-col-status', 'sn-col-notes', 'sn-col-actions',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});

// -----------------------------------------------------------------------------
// leases/analytics.blade.php
// -----------------------------------------------------------------------------
it('brands leases/analytics with ai-page styles and wrapper while keeping hooks', function () {
    $src = leasesBladeSource('analytics');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
    expect($src)->toContain("@section('scripts')");

    foreach ([
        'id="statusChart"', 'id="collectionChart"', 'id="forecastChart"',
        "\$stats['active']", "\$stats['ended']", "\$stats['renewable']", "\$stats['troubled']",
        "\$stats['collection_rate']", "\$stats['overdue']", "\$stats['upcoming']", "\$stats['monthly_revenue']",
        "\$stats['due_total']", "\$stats['paid_total']", "\$stats['annual_revenue']",
        '$collection_history', '$top_tenants', '$late_tenants', '$forecast',
        "\$trend['source']", "\$trend['narrative']",
        '@forelse', '@empty', '@endforelse',
        'sn-stat', 'sn-stat-ico', 'sn-chart-ico', 'sn-thead', 'sn-row-hover', 'sn-num',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});
