<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\MoraslatAiExtractor;
use Illuminate\Support\Facades\Http;

it('analyzes a scanned letter and matches suggestions to the real taxonomy rows', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'summary' => 'خطاب من وزارة العمل بخصوص تجديد رخصة عمل. يطلب استكمال المستندات خلال أسبوع.',
                        'subject' => 'تجديد رخصة عمل',
                        'sender' => 'وزارة العمل',
                        'date' => '2026-01-10',
                        'key_points' => ['تجديد الرخصة', 'استكمال المستندات خلال أسبوع'],
                        'type_hint' => 'مراسلة عامة',
                        'category_hint' => 'عاجل',
                        'status_hint' => 'قيد المراجعة',
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $types = [
        (object) ['moraslat_type_id' => 1, 'moraslat_type_name' => 'مراسلة محل'],
        (object) ['moraslat_type_id' => 2, 'moraslat_type_name' => 'مراسلة عامة'],
        (object) ['moraslat_type_id' => 3, 'moraslat_type_name' => 'مراسلة عامل'],
    ];
    $categories = [
        (object) ['moraslat_categoty_id' => 1, 'moraslat_categoty_name' => 'عادي'],
        (object) ['moraslat_categoty_id' => 2, 'moraslat_categoty_name' => 'عاجل'],
    ];
    $statuses = [
        (object) ['moraslat_status_id' => 1, 'moraslat_status_name' => 'مقبول'],
        (object) ['moraslat_status_id' => 2, 'moraslat_status_name' => 'قيد المراجعة'],
        (object) ['moraslat_status_id' => 3, 'moraslat_status_name' => 'مرفوض'],
    ];

    $tmp = tempnam(sys_get_temp_dir(), 'mrs').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(MoraslatAiExtractor::class)->analyze($tmp, $types, $categories, $statuses);

    expect($r['summary'])->toContain('تجديد رخصة عمل');
    expect($r['extracted_subject'])->toBe('تجديد رخصة عمل');
    expect($r['sender'])->toBe('وزارة العمل');
    expect($r['date'])->toBe('2026-01-10');
    expect($r['key_points'])->toBe(['تجديد الرخصة', 'استكمال المستندات خلال أسبوع']);
    expect($r['suggested_type_id'])->toBe(2);
    expect($r['suggested_type_name'])->toBe('مراسلة عامة');
    expect($r['suggested_category_id'])->toBe(2);
    expect($r['suggested_category_name'])->toBe('عاجل');
    expect($r['suggested_status_id'])->toBe(2);
    expect($r['suggested_status_name'])->toBe('قيد المراجعة');

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('returns null suggestions when no hint is provided by the model', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'summary' => 'خطاب غير واضح المحتوى.',
                        'subject' => null,
                        'sender' => null,
                        'date' => null,
                        'key_points' => [],
                        'type_hint' => null,
                        'category_hint' => null,
                        'status_hint' => null,
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'mrs').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(MoraslatAiExtractor::class)->analyze($tmp, [], [], []);

    expect($r['suggested_type_id'])->toBeNull();
    expect($r['suggested_category_id'])->toBeNull();
    expect($r['suggested_status_id'])->toBeNull();
    expect($r['extracted_subject'])->toBeNull();

    @unlink($tmp);
});

it('drafts a formal Arabic reply from a text-only Gemini call (no file)', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => "السادة وزارة العمل المحترمين،\nبالإشارة إلى خطابكم بخصوص تجديد رخصة العمل، نفيدكم بأنه سيتم استكمال المستندات المطلوبة خلال المدة المحددة.\nوتفضلوا بقبول فائق الاحترام.",
                ]]],
            ]],
        ], 200),
    ]);

    $r = app(MoraslatAiExtractor::class)->draftReply('خطاب من وزارة العمل بخصوص تجديد رخصة عمل.');

    expect($r['draft'])->toContain('وزارة العمل');
    expect($r['draft'])->toContain('تفضلوا بقبول فائق الاحترام');

    Http::assertSent(function ($req) {
        // Text-only call: no inline_data part (no file), unlike the OCR analyze() call.
        $parts = data_get($req->data(), 'contents.0.parts', []);
        $hasInlineData = collect($parts)->contains(fn ($p) => isset($p['inline_data']));

        return str_contains($req->url(), ':generateContent') && ! $hasInlineData;
    });
});

it('throws on a Gemini HTTP failure during draftReply', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.retries', 1);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    expect(fn () => app(MoraslatAiExtractor::class)->draftReply('نص تجريبي'))
        ->toThrow(RuntimeException::class);
});

it('matches a free-text hint to the closest taxonomy row by name similarity (pure)', function () {
    $rows = [
        (object) ['id' => 1, 'name' => 'عادي'],
        (object) ['id' => 2, 'name' => 'عاجل'],
        (object) ['id' => 3, 'name' => 'سري'],
    ];

    [$id, $name] = app(MoraslatAiExtractor::class)->suggestFromList('عاجل جدا', $rows, 'id', 'name');

    expect($id)->toBe(2);
    expect($name)->toBe('عاجل');
});

it('does not suggest anything for an empty hint', function () {
    $rows = [(object) ['id' => 1, 'name' => 'عادي']];

    [$id, $name] = app(MoraslatAiExtractor::class)->suggestFromList('', $rows, 'id', 'name');

    expect($id)->toBeNull();
    expect($name)->toBeNull();
});
