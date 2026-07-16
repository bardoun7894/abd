<?php

// PURE logic only — NEVER hits the live (or faked) Gemini API. Only exercises the
// aggregate math, the metric whitelist gate, and the prompt-shape builders.
uses(Tests\TestCase::class);

use App\Services\ReportsNlService;

it('builds rounded metrics with a derived net_total', function () {
    $svc = app(ReportsNlService::class);

    $metrics = $svc->buildMetrics(10000.456, 4000.123, 2000.001);

    expect($metrics)->toBe([
        'income_total' => 10000.46,
        'expense_total' => 4000.12,
        'purchase_total' => 2000.0,
        'net_total' => 4000.33,
    ]);
});

it('filters out any metric key that is not on the fixed whitelist', function () {
    $svc = app(ReportsNlService::class);

    $safe = $svc->filterAllowed([
        'income_total' => 100.0,
        'expense_total' => 40.0,
        'purchase_total' => 20.0,
        'net_total' => 40.0,
        'raw_sql' => 'DROP TABLE workers;',
        'salary_of_worker_5' => 9999,
    ]);

    expect($safe)->toBe([
        'income_total' => 100.0,
        'expense_total' => 40.0,
        'purchase_total' => 20.0,
        'net_total' => 40.0,
    ]);
    expect($safe)->not->toHaveKey('raw_sql');
    expect($safe)->not->toHaveKey('salary_of_worker_5');
});

it('keeps ALLOWED_METRICS in sync with buildMetrics output keys', function () {
    $svc = app(ReportsNlService::class);

    $metrics = $svc->buildMetrics(1.0, 1.0, 1.0);

    expect(array_keys($metrics))->toEqual(array_keys(ReportsNlService::ALLOWED_METRICS));
});

it('builds a narrate prompt that embeds the whitelisted JSON numbers and forbids inventing data', function () {
    $svc = app(ReportsNlService::class);

    $prompt = $svc->narratePrompt(['income_total' => 100.0, 'expense_total' => 40.0]);

    expect($prompt)->toContain('100');
    expect($prompt)->toContain('40');
    expect($prompt)->toContain('JSON');
    expect($prompt)->toContain('لا تخترع');
});

it('builds an ask prompt that includes the question and explicitly forbids SQL generation', function () {
    $svc = app(ReportsNlService::class);

    $prompt = $svc->askPrompt('كم بلغ إجمالي المصروفات؟', ['expense_total' => 40.0]);

    expect($prompt)->toContain('كم بلغ إجمالي المصروفات؟');
    expect($prompt)->toContain('40');
    expect($prompt)->toContain('SQL');
    expect($prompt)->toContain('لا تكتب أي استعلام SQL');
});

it('drops disallowed metrics before they ever reach the ask prompt', function () {
    $svc = app(ReportsNlService::class);

    $prompt = $svc->askPrompt('ما هو راتب العامل رقم 5؟', [
        'expense_total' => 40.0,
        'salary_of_worker_5' => 99999,
    ]);

    expect($prompt)->toContain('40');
    expect($prompt)->not->toContain('99999');
});
