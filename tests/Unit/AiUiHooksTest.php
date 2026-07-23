<?php

/**
 * Spec shared AI-page brand UI — apply the extraction-log redesign to the
 * invoice upload, fix-center, and report blades without breaking any JS hooks.
 * Source-level guard that reads the raw blade templates and asserts the
 * ai-page-styles include, ai-page wrapper, and every JS-dependent id/class/
 * data-* attribute are still present verbatim.
 */

uses(Tests\TestCase::class);

function invoicesBladeSource(string $name): string
{
    return file_get_contents(base_path("resources/views/dashboard/invoices/{$name}.blade.php"));
}

// -----------------------------------------------------------------------------
// invoices/upload.blade.php
// -----------------------------------------------------------------------------
it('brands invoices/upload with ai-page styles and wrapper while keeping hooks', function () {
    $src = invoicesBladeSource('upload');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
    expect($src)->toContain("@section('scripts')");

    foreach ([
        'id="drop"',
        'id="pdf"',
        'id="fname"',
        'id="up_form"',
        'id="btn"',
        'id="ov"',
        'id="err"',
        'name="pdf"',
        'accept="application/pdf"',
        "route('dashboard.invoices.store')",
        "route('dashboard.invoices.index')",
        '@include(\'dashboard.partials.ai_subscription_banner\')',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});

// -----------------------------------------------------------------------------
// invoices/needs_fix.blade.php
// -----------------------------------------------------------------------------
it('brands invoices/needs_fix with ai-page styles and wrapper while keeping hooks', function () {
    $src = invoicesBladeSource('needs_fix');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
    expect($src)->toContain("@section('scripts')");

    foreach ([
        '@include(\'dashboard.invoices._edit_modal\')',
        'id="pushFixedBtn"',
        'id="fixTable"',
        'id="fixShopId"',
        'id="fixManagerId"',
        'id="pushFixedModal"',
        'id="pushFixedResult"',
        'id="pushFixedSubmitBtn"',
        'js-edit-inv',
        'data-inv="{{ $inv->id }}"',
        'data-id="{{ $inv->id }}"',
        'data-supplier_name="{{ $inv->supplier_name }}"',
        'data-supplier_tax_number="{{ $inv->supplier_tax_number }}"',
        'data-invoice_number="{{ $inv->invoice_number }}"',
        'data-invoice_date="{{ $date }}"',
        'data-amount_before_vat="{{ $inv->amount_before_vat }}"',
        'data-vat_amount="{{ $inv->vat_amount }}"',
        'data-total_incl_vat="{{ $inv->total_incl_vat }}"',
        "route('dashboard.invoices.bulk-push')",
        "route('dashboard.invoices.needs-fix')",
        "route('dashboard.invoices.index')",
        "route('dashboard.invoices.show', \$inv->batch_id)",
        '$affectedBatchIds',
        '@forelse',
        '@empty',
        '@endforelse',
        'sn-thead',
        'sn-row',
        'sn-num',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});

// -----------------------------------------------------------------------------
// invoices/report.blade.php
// -----------------------------------------------------------------------------
it('brands invoices/report with ai-page styles and wrapper while keeping hooks', function () {
    $src = invoicesBladeSource('report');

    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
    expect($src)->toContain("@section('scripts')");

    foreach ([
        'id="kt_chartjs_suppliers"',
        'id="kt_chartjs_status"',
        "\$stats['today']",
        "\$stats['thisMonth']",
        "\$stats['totalPurchases']",
        "\$stats['totalVat']",
        "\$stats['duplicates']",
        "\$stats['rejected']",
        "\$stats['needsReview']",
        "\$stats['successRate']",
        "\$stats['avgProcessingMs']",
        "\$stats['topItems']",
        "\$stats['topSuppliers']",
        '@forelse',
        '@empty',
        '@endforelse',
        'sn-stat',
        'sn-stat-ico',
        'sn-chart-ico',
        'sn-thead',
        'sn-num',
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});
