<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\ViolationAiExtractor;
use Illuminate\Support\Facades\Http;

it('classifies a violation note and matches the side to the real taxonomy row', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'side_hint' => 'الأمانة',
                        'severity_hint' => 'متوسطة',
                        'suggested_action' => 'سداد قيمة المخالفة خلال أسبوع وتصويب المخالفة.',
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $sides = [
        (object) ['violation_side_id' => 1, 'violation_side_name' => 'البلدية'],
        (object) ['violation_side_id' => 2, 'violation_side_name' => 'الأمانة'],
        (object) ['violation_side_id' => 3, 'violation_side_name' => 'الدفاع المدني'],
    ];

    $r = app(ViolationAiExtractor::class)->classify('تم رصد مخالفة نظافة من قبل مفتشي الأمانة', $sides);

    expect($r['side'])->toBe('الأمانة');
    expect($r['side_id'])->toBe(2);
    expect($r['severity'])->toBe('متوسطة');
    expect($r['suggested_action'])->toBe('سداد قيمة المخالفة خلال أسبوع وتصويب المخالفة.');

    Http::assertSent(function ($req) {
        return str_contains($req->url(), ':generateContent')
            && data_get($req->data(), 'generationConfig.responseMimeType') === 'text/plain'
            && str_contains(data_get($req->data(), 'contents.0.parts.0.text'), 'تم رصد مخالفة نظافة');
    });
});

it('classifies with no real side taxonomy and falls back to the raw hint / default severities', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'side_hint' => 'جهة غير معروفة تماما',
                        'severity_hint' => 'جسيمة',
                        'suggested_action' => null,
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $r = app(ViolationAiExtractor::class)->classify('وصف مخالفة عام', []);

    expect($r['side_id'])->toBeNull();
    expect($r['side'])->toBe('جهة غير معروفة تماما');
    expect($r['severity'])->toBe('جسيمة');
    expect($r['suggested_action'])->toBeNull();
});

it('strips a markdown code fence around the classify JSON response', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => "```json\n".json_encode([
                        'side_hint' => null,
                        'severity_hint' => 'بسيطة',
                        'suggested_action' => 'لا يوجد',
                    ])."\n```",
                ]]],
            ]],
        ], 200),
    ]);

    $r = app(ViolationAiExtractor::class)->classify('مخالفة بسيطة', []);

    expect($r['severity'])->toBe('بسيطة');
    expect($r['suggested_action'])->toBe('لا يوجد');
});

it('drafts a formal Arabic violation-notice letter from a text-only Gemini call (no file)', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => "السادة محل الأمل التجاري المحترمين،\nبالإشارة إلى المخالفة المرصودة بتاريخ 2026-01-10 بخصوص عدم الالتزام باشتراطات النظافة، نأمل تصويب المخالفة خلال 7 أيام من تاريخه.\nوتفضلوا بقبول فائق الاحترام.",
                ]]],
            ]],
        ], 200),
    ]);

    $r = app(ViolationAiExtractor::class)->draftNotice([
        'name' => 'محل الأمل التجاري',
        'violation_type' => 'الأمانة',
        'date' => '2026-01-10',
        'note' => 'عدم الالتزام باشتراطات النظافة',
    ]);

    expect($r['draft'])->toContain('محل الأمل التجاري');
    expect($r['draft'])->toContain('تفضلوا بقبول فائق الاحترام');

    Http::assertSent(function ($req) {
        // Text-only call: no inline_data part (no file).
        $parts = data_get($req->data(), 'contents.0.parts', []);
        $hasInlineData = collect($parts)->contains(fn ($p) => isset($p['inline_data']));

        return str_contains($req->url(), ':generateContent')
            && ! $hasInlineData
            && str_contains(data_get($req->data(), 'contents.0.parts.0.text'), 'محل الأمل التجاري')
            && str_contains(data_get($req->data(), 'contents.0.parts.0.text'), 'عدم الالتزام باشتراطات النظافة');
    });
});

it('classifies with a caller-supplied model instead of the default', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.default_model', 'gemini-flash-lite-latest');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'side_hint' => 'البلدية',
                        'severity_hint' => 'بسيطة',
                        'suggested_action' => 'تصحيح المخالفة.',
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    app(ViolationAiExtractor::class)->classify('مخالفة بسيطة', [], [], 'gemini-3-flash-preview');

    Http::assertSent(function ($req) {
        return str_contains($req->url(), 'models/gemini-3-flash-preview:generateContent');
    });
});

it('throws on a Gemini HTTP failure during classify', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.retries', 1);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    expect(fn () => app(ViolationAiExtractor::class)->classify('نص تجريبي', []))
        ->toThrow(RuntimeException::class);
});

it('throws on a Gemini HTTP failure during draftNotice', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.retries', 1);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    expect(fn () => app(ViolationAiExtractor::class)->draftNotice(['note' => 'نص']))
        ->toThrow(RuntimeException::class);
});

it('matches a free-text side hint to the closest taxonomy row by name similarity (pure)', function () {
    $rows = [
        (object) ['id' => 1, 'name' => 'البلدية'],
        (object) ['id' => 2, 'name' => 'الأمانة'],
        (object) ['id' => 3, 'name' => 'الدفاع المدني'],
    ];

    [$id, $name] = app(ViolationAiExtractor::class)->suggestFromList('الأمانة تقريبا', $rows, 'id', 'name');

    expect($id)->toBe(2);
    expect($name)->toBe('الأمانة');
});

it('does not suggest a side for an empty hint (pure)', function () {
    $rows = [(object) ['id' => 1, 'name' => 'البلدية']];

    [$id, $name] = app(ViolationAiExtractor::class)->suggestFromList('', $rows, 'id', 'name');

    expect($id)->toBeNull();
    expect($name)->toBeNull();
});

it('matches a severity hint against a real supplied taxonomy (pure)', function () {
    $severities = ['خفيفة', 'متوسطة الخطورة', 'جسيمة جدا'];

    $result = app(ViolationAiExtractor::class)->matchSeverity('متوسطة الخطوره', $severities);

    expect($result)->toBe('متوسطة الخطورة');
});

it('falls back to the default severity labels when no taxonomy is supplied (pure)', function () {
    $result = app(ViolationAiExtractor::class)->matchSeverity('جسيمة', []);

    expect($result)->toBe('جسيمة');
});

it('returns the raw hint when nothing matches confidently (pure)', function () {
    $result = app(ViolationAiExtractor::class)->matchSeverity('حالة غامضة تماما لا تشبه شيئا', []);

    expect($result)->toBe('حالة غامضة تماما لا تشبه شيئا');
});

it('returns null severity for an empty hint (pure)', function () {
    $result = app(ViolationAiExtractor::class)->matchSeverity('', []);

    expect($result)->toBeNull();
});
