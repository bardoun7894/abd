<?php

// PURE logic only — NEVER hits the live (or faked) Gemini API. Only exercises the
// delta math and the cache-key / prompt-shape builders.
uses(Tests\TestCase::class);

use App\Services\HomeInsightService;
use Carbon\Carbon;

it('computes a positive delta percentage when current exceeds previous', function () {
    $svc = app(HomeInsightService::class);

    expect($svc->deltaPct(150.0, 100.0))->toBe(50.0);
});

it('computes a negative delta percentage when current is below previous', function () {
    $svc = app(HomeInsightService::class);

    expect($svc->deltaPct(50.0, 100.0))->toBe(-50.0);
});

it('treats previous=0 current=0 as a 0% delta (no growth, not a divide-by-zero)', function () {
    $svc = app(HomeInsightService::class);

    expect($svc->deltaPct(0.0, 0.0))->toBe(0.0);
});

it('treats previous=0 current>0 as a 100% delta (cannot divide by zero)', function () {
    $svc = app(HomeInsightService::class);

    expect($svc->deltaPct(500.0, 0.0))->toBe(100.0);
});

it('builds the current/previous/delta_pct shape for each of the three metrics', function () {
    $svc = app(HomeInsightService::class);

    $deltas = $svc->computeDeltas(
        ['income' => 1000.0, 'expense' => 400.0, 'purchase' => 200.0],
        ['income' => 800.0, 'expense' => 500.0, 'purchase' => 200.0],
    );

    expect($deltas)->toBe([
        'income' => ['current' => 1000.0, 'previous' => 800.0, 'delta_pct' => 25.0],
        'expense' => ['current' => 400.0, 'previous' => 500.0, 'delta_pct' => -20.0],
        'purchase' => ['current' => 200.0, 'previous' => 200.0, 'delta_pct' => 0.0],
    ]);
});

it('defaults missing metric keys to zero instead of throwing', function () {
    $svc = app(HomeInsightService::class);

    $deltas = $svc->computeDeltas(['income' => 100.0], []);

    expect($deltas['income'])->toBe(['current' => 100.0, 'previous' => 0.0, 'delta_pct' => 100.0]);
    expect($deltas['expense'])->toBe(['current' => 0.0, 'previous' => 0.0, 'delta_pct' => 0.0]);
    expect($deltas['purchase'])->toBe(['current' => 0.0, 'previous' => 0.0, 'delta_pct' => 0.0]);
});

it('builds a year-month cache key for the given date so Gemini is not re-called every load', function () {
    $svc = app(HomeInsightService::class);

    $key = $svc->cacheKey(Carbon::create(2026, 3, 5));

    expect($key)->toBe('home_ai_insight_2026_03');
});

it('builds a different cache key for a different month', function () {
    $svc = app(HomeInsightService::class);

    expect($svc->cacheKey(Carbon::create(2026, 1, 1)))->not->toBe($svc->cacheKey(Carbon::create(2026, 2, 1)));
});

it('builds an insight prompt that embeds the deltas JSON and forbids inventing numbers', function () {
    $svc = app(HomeInsightService::class);

    $deltas = $svc->computeDeltas(['income' => 100.0], ['income' => 50.0]);
    $prompt = $svc->buildPrompt($deltas);

    expect($prompt)->toContain('100');
    expect($prompt)->toContain('50');
    expect($prompt)->toContain('JSON');
    expect($prompt)->toContain('لا تخترع');
});
