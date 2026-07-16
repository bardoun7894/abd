<?php

// PURE math only for projectRevenue/collectionHistory — no DB, no live Gemini.
// trendNarrative() Gemini call is covered separately with Http::fake (needs the app
// container for config()/Http), so this file boots the app but never touches the DB.
uses(Tests\TestCase::class);

use App\Services\LeaseForecastService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->svc = new LeaseForecastService();
    $this->now = Carbon::parse('2026-07-01'); // fixed reference "today" (start of month)
});

// ---- historicalCollectionRate() ----

it('computes the paid/due ratio from historical rows', function () {
    $rows = [
        ['due_date' => '2026-06-05', 'amount' => 1000.0, 'status' => 'paid', 'paid_amount' => 1000.0],
        ['due_date' => '2026-06-10', 'amount' => 1000.0, 'status' => 'pending', 'paid_amount' => null],
    ];

    $rate = $this->svc->historicalCollectionRate($rows, $this->now);

    expect($rate)->toBe(0.5);
});

it('falls back to paid_amount = amount when paid_amount is null on a paid row', function () {
    $rows = [
        ['due_date' => '2026-06-05', 'amount' => 500.0, 'status' => 'paid', 'paid_amount' => null],
    ];

    $rate = $this->svc->historicalCollectionRate($rows, $this->now);

    expect($rate)->toBe(1.0);
});

it('defaults the collection rate to 1.0 (best-guess full collection) when there is no history', function () {
    $rate = $this->svc->historicalCollectionRate([], $this->now);

    expect($rate)->toBe(1.0);
});

// ---- buildProjection() (the pure core of projectRevenue) ----

it('buckets scheduled amounts into calendar months and weights projected by the collection rate', function () {
    $rows = [
        ['due_date' => '2026-07-15', 'amount' => 1000.0],
        ['due_date' => '2026-07-20', 'amount' => 500.0],
        ['due_date' => '2026-08-01', 'amount' => 2000.0],
    ];

    $out = $this->svc->buildProjection($rows, 0.8, 3, $this->now);

    expect($out)->toHaveCount(3);
    expect($out[0])->toBe(['month' => '2026-07', 'scheduled' => 1500.0, 'projected' => 1200.0]);
    expect($out[1])->toBe(['month' => '2026-08', 'scheduled' => 2000.0, 'projected' => 1600.0]);
    expect($out[2])->toBe(['month' => '2026-09', 'scheduled' => 0.0, 'projected' => 0.0]);
});

it('ignores rows whose due_date falls outside the projected window', function () {
    $rows = [
        ['due_date' => '2026-06-30', 'amount' => 999.0], // before window
        ['due_date' => '2027-01-01', 'amount' => 999.0], // after window
        ['due_date' => '2026-07-10', 'amount' => 100.0],
    ];

    $out = $this->svc->buildProjection($rows, 1.0, 1, $this->now);

    expect($out)->toBe([
        ['month' => '2026-07', 'scheduled' => 100.0, 'projected' => 100.0],
    ]);
});

// ---- buildHistory() (the pure core of collectionHistory) ----

it('buckets due vs paid per past calendar month and derives a collection rate percentage', function () {
    $rows = [
        ['due_date' => '2026-06-05', 'amount' => 1000.0, 'status' => 'paid', 'paid_amount' => 800.0],
        ['due_date' => '2026-06-20', 'amount' => 500.0, 'status' => 'pending', 'paid_amount' => null],
        ['due_date' => '2026-05-15', 'amount' => 1000.0, 'status' => 'paid', 'paid_amount' => 1000.0],
    ];

    $out = $this->svc->buildHistory($rows, 2, $this->now);

    expect($out)->toBe([
        ['month' => '2026-05', 'due' => 1000.0, 'paid' => 1000.0, 'rate' => 100.0],
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'rate' => 53.3],
    ]);
});

it('returns a zero-filled bucket for a month with no payments at all', function () {
    $out = $this->svc->buildHistory([], 1, $this->now);

    expect($out)->toBe([
        ['month' => '2026-06', 'due' => 0.0, 'paid' => 0.0, 'rate' => 0.0],
    ]);
});

// ---- trendPrompt() (pure prompt shape — narrative content is not asserted here) ----

it('builds a trend prompt that embeds the history numbers and forbids inventing data', function () {
    $history = [
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'rate' => 53.3],
    ];

    $prompt = $this->svc->trendPrompt($history);

    expect($prompt)->toContain('1500');
    expect($prompt)->toContain('800');
    expect($prompt)->toContain('53.3');
    expect($prompt)->toContain('JSON');
    expect($prompt)->toContain('لا تخترع');
});

// ---- trendNarrative() Gemini integration + graceful fallback ----

it('phrases an Arabic narrative around the pre-computed collection history via Gemini', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'اتجاه التحصيل مستقر خلال الأشهر الماضية.']]],
            ]],
        ], 200),
    ]);

    $result = $this->svc->trendNarrative(null, [
        ['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'rate' => 53.3],
    ]);

    expect($result['source'])->toBe('ai');
    expect($result['narrative'])->toBe('اتجاه التحصيل مستقر خلال الأشهر الماضية.');
    expect($result['history'])->toHaveCount(1);
});

it('falls back to the raw numbers (no narrative) when Gemini fails', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);
    config()->set('services.gemini.retries', 1);

    $history = [['month' => '2026-06', 'due' => 1500.0, 'paid' => 800.0, 'rate' => 53.3]];
    $result = $this->svc->trendNarrative(null, $history);

    expect($result['source'])->toBe('fallback');
    expect($result['narrative'])->toBeNull();
    expect($result['history'])->toBe($history);
});
