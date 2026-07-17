<?php

// PURE math only for collectionHistory/forecast/anomalies — no DB, no live Gemini
// (except the narrative() Gemini-integration tests below, which use Http::fake and
// need the app container for config()/Http, same pattern as LeaseForecastServiceTest).
uses(Tests\TestCase::class);

use App\Services\FinancialAiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->svc = new FinancialAiService();
    $this->now = Carbon::parse('2026-07-01'); // fixed reference "today" (start of month)
});

// ---- historicalCollectionRate() ----

it('computes the paid/due ratio from history rows', function () {
    $history = [
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 500.0, 'remaining' => 500.0, 'rate' => 50.0],
        ['month' => '2026-06', 'due' => 1000.0, 'paid' => 1000.0, 'remaining' => 0.0, 'rate' => 100.0],
    ];

    $rate = $this->svc->historicalCollectionRate($history);

    expect($rate)->toBe(0.75);
});

it('defaults the collection rate to 1.0 (best-guess full collection) when there is no history', function () {
    expect($this->svc->historicalCollectionRate([]))->toBe(1.0);
});

// ---- buildHistory() ----

it('buckets due vs paid per past calendar month and derives remaining + collection rate percentage', function () {
    $rows = [
        ['y' => 2026, 'm' => 6, 'val' => 1000.0, 'paid' => 800.0],
        ['y' => 2026, 'm' => 6, 'val' => 500.0, 'paid' => 0.0],
        ['y' => 2026, 'm' => 5, 'val' => 1000.0, 'paid' => 1000.0],
    ];

    $out = $this->svc->buildHistory($rows, 2, $this->now);

    expect($out)->toBe([
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 1000.0, 'remaining' => 0.0, 'rate' => 100.0],
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'remaining' => 700.0, 'rate' => 53.3],
    ]);
});

it('returns a zero-filled bucket for a month with no financial rows at all', function () {
    $out = $this->svc->buildHistory([], 1, $this->now);

    expect($out)->toBe([
        ['month' => '2026-06', 'due' => 0.0, 'paid' => 0.0, 'remaining' => 0.0, 'rate' => 0.0],
    ]);
});

it('ignores rows whose y/m falls outside the history window', function () {
    $rows = [
        ['y' => 2026, 'm' => 4, 'val' => 999.0, 'paid' => 999.0], // before window
        ['y' => 2026, 'm' => 7, 'val' => 999.0, 'paid' => 999.0], // current month, outside past-months window
        ['y' => 2026, 'm' => 6, 'val' => 100.0, 'paid' => 100.0],
    ];

    $out = $this->svc->buildHistory($rows, 1, $this->now);

    expect($out)->toBe([
        ['month' => '2026-06', 'due' => 100.0, 'paid' => 100.0, 'remaining' => 0.0, 'rate' => 100.0],
    ]);
});

// ---- averageDue() ----

it('averages due across history rows', function () {
    $history = [
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 1000.0, 'remaining' => 0.0, 'rate' => 100.0],
        ['month' => '2026-06', 'due' => 2000.0, 'paid' => 1000.0, 'remaining' => 1000.0, 'rate' => 50.0],
    ];

    expect($this->svc->averageDue($history))->toBe(1500.0);
});

it('averages due to 0.0 on empty history', function () {
    expect($this->svc->averageDue([]))->toBe(0.0);
});

// ---- buildForecast() ----

it('projects the average due and rate-weighted expected collection across future months', function () {
    $out = $this->svc->buildForecast(1000.0, 0.8, 3, $this->now);

    expect($out)->toBe([
        ['month' => '2026-07', 'due' => 1000.0, 'expected' => 800.0],
        ['month' => '2026-08', 'due' => 1000.0, 'expected' => 800.0],
        ['month' => '2026-09', 'due' => 1000.0, 'expected' => 800.0],
    ]);
});

// ---- detectAnomalies() ----

it('returns no anomalies for an empty history', function () {
    expect($this->svc->detectAnomalies([]))->toBe([]);
});

it('flags a month whose due is far above the running average of prior months', function () {
    $history = [
        ['month' => '2026-04', 'due' => 1000.0, 'paid' => 1000.0, 'remaining' => 0.0, 'rate' => 100.0],
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 1000.0, 'remaining' => 0.0, 'rate' => 100.0],
        ['month' => '2026-06', 'due' => 5000.0, 'paid' => 5000.0, 'remaining' => 0.0, 'rate' => 100.0],
    ];

    $notes = $this->svc->detectAnomalies($history);

    expect($notes)->toHaveCount(1);
    expect($notes[0])->toContain('شذوذ');
    expect($notes[0])->toContain('2026-06');
});

it('flags a month with negative remaining (overpayment)', function () {
    $history = [
        ['month' => '2026-06', 'due' => 1000.0, 'paid' => 1200.0, 'remaining' => -200.0, 'rate' => 120.0],
    ];

    $notes = $this->svc->detectAnomalies($history);

    expect($notes)->toHaveCount(1);
    expect($notes[0])->toContain('شذوذ');
    expect($notes[0])->toContain('2026-06');
});

it('flags a month with zero collection despite a positive due amount', function () {
    $history = [
        ['month' => '2026-06', 'due' => 1000.0, 'paid' => 0.0, 'remaining' => 1000.0, 'rate' => 0.0],
    ];

    $notes = $this->svc->detectAnomalies($history);

    expect($notes)->toHaveCount(1);
    expect($notes[0])->toContain('شذوذ');
    expect($notes[0])->toContain('2026-06');
});

it('flags a sudden drop in collection rate between consecutive months', function () {
    $history = [
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 900.0, 'remaining' => 100.0, 'rate' => 90.0],
        ['month' => '2026-06', 'due' => 1000.0, 'paid' => 100.0, 'remaining' => 900.0, 'rate' => 10.0],
    ];

    $notes = $this->svc->detectAnomalies($history);

    expect($notes)->toHaveCount(1);
    expect($notes[0])->toContain('شذوذ');
    expect($notes[0])->toContain('2026-05');
    expect($notes[0])->toContain('2026-06');
});

it('does not flag a healthy, stable history', function () {
    $history = [
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 950.0, 'remaining' => 50.0, 'rate' => 95.0],
        ['month' => '2026-06', 'due' => 1050.0, 'paid' => 1000.0, 'remaining' => 50.0, 'rate' => 95.2],
    ];

    expect($this->svc->detectAnomalies($history))->toBe([]);
});

// ---- narrativePrompt() (pure prompt shape — narrative content is not asserted here) ----

it('builds a narrative prompt that embeds the history numbers and forbids inventing data', function () {
    $history = [
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'remaining' => 700.0, 'rate' => 53.3],
    ];

    $prompt = $this->svc->narrativePrompt($history);

    expect($prompt)->toContain('1500');
    expect($prompt)->toContain('800');
    expect($prompt)->toContain('53.3');
    expect($prompt)->toContain('JSON');
    expect($prompt)->toContain('لا تخترع');
});

// ---- narrative() Gemini integration + graceful fallback ----

it('phrases an Arabic narrative around the pre-computed collection history via Gemini', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'اتجاه تحصيل مستحقات العمال مستقر خلال الأشهر الماضية.']]],
            ]],
        ], 200),
    ]);

    $result = $this->svc->narrative(null, [
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'remaining' => 700.0, 'rate' => 53.3],
    ]);

    expect($result['source'])->toBe('ai');
    expect($result['narrative'])->toBe('اتجاه تحصيل مستحقات العمال مستقر خلال الأشهر الماضية.');
    expect($result['history'])->toHaveCount(1);
});

it('falls back to the raw numbers (no narrative) when Gemini fails', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);
    config()->set('services.gemini.retries', 1);

    $history = [['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'remaining' => 700.0, 'rate' => 53.3]];
    $result = $this->svc->narrative(null, $history);

    expect($result['source'])->toBe('fallback');
    expect($result['narrative'])->toBeNull();
    expect($result['history'])->toBe($history);
});
