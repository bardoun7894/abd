<?php

/**
 * Spec 012 — apply the shared AI-page brand UI to the API-keys settings page
 * (resources/views/dashboard/settings/index.blade.php). This test guards every
 * JS hook, form field name/id and route helper so the redesign stays purely
 * additive and cannot break the POST handlers in SettingsController.
 */

uses(Tests\TestCase::class);

function settingsIndexBladeSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/settings/index.blade.php'));
}

it('includes the shared AI-page styles and wrapper', function () {
    $src = settingsIndexBladeSource();

    expect($src)->toContain("@include('dashboard.partials.ai-page-styles')");
    expect($src)->toContain('ai-page');
});

it('keeps every JS-dependent hook required by the settings scripts', function () {
    $src = settingsIndexBladeSource();

    // Layout / directives
    expect($src)->toContain("@extends('layouts.app')");
    expect($src)->toContain("@section('styles')");
    expect($src)->toContain("@section('scripts')");

    // Session success alert
    expect($src)->toContain("session('success')");

    // AI subscription card routes and form fields
    expect($src)->toContain("route('dashboard.settings.ai_usage'");
    expect($src)->toContain("route('dashboard.settings.subscription.update'");
    expect($src)->toContain('name="sub_active"');
    expect($src)->toContain('name="sub_expires_at"');
    expect($src)->toContain('name="sub_quota_pages"');

    // Subscription renewal form
    expect($src)->toContain("route('dashboard.settings.subscription.renew'");
    expect($src)->toContain('name="renew_expires_at"');

    // API keys / integration settings form
    expect($src)->toContain("route('dashboard.settings.update'");
    expect($src)->toContain('name="setting_{{ $it[\'key\'] }}"');

    // Custom key/value rows
    expect($src)->toContain('id="custom_rows"');
    expect($src)->toContain('name="custom_key[]"');
    expect($src)->toContain('name="custom_value[]"');
    expect($src)->toContain('id="add_custom_row"');
    expect($src)->toContain('class="row g-3 mb-3 align-items-end custom-row"');
});

it('does not remove any wired JS snippet from the scripts block', function () {
    $src = settingsIndexBladeSource();

    foreach ([
        "document.getElementById('add_custom_row')",
        "document.getElementById('custom_rows')",
        "rows.querySelector('.custom-row')",
    ] as $needle) {
        expect($src)->toContain($needle);
    }
});
