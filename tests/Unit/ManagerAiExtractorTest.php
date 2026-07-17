<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — mirrors tests/Unit/WorkerAiExtractorTest.php.
uses(Tests\TestCase::class);

use App\Services\ManagerAiExtractor;
use Illuminate\Support\Facades\Http;

it('extracts and normalizes manager name/mobile fields from a Gemini response', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'manager_name' => 'محمد أحمد',
                        'manager_mobile' => '٠٥٥١٢٣٤٥٦٧', // Arabic-Indic digits
                        'field_confidence' => [
                            'manager_name' => 0.95,
                            'manager_mobile' => 0.6,
                        ],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'mai').'.jpg';
    file_put_contents($tmp, 'fake-image-bytes');

    $r = app(ManagerAiExtractor::class)->extract($tmp);

    expect($r['manager_name'])->toBe('محمد أحمد');
    expect($r['manager_mobile'])->toBe('0551234567'); // Arabic digits converted to Latin
    expect($r['field_confidence']['manager_name'])->toBe(0.95);
    expect($r['field_confidence']['manager_mobile'])->toBe(0.6);

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('does not invent a manager name and clamps confidence to 0..1', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'manager_name' => null, // unclear scan — must not be guessed
                        'manager_mobile' => null,
                        'field_confidence' => ['manager_name' => 1.5, 'manager_mobile' => -0.4],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'mai').'.jpg';
    file_put_contents($tmp, 'fake-image-bytes');

    $r = app(ManagerAiExtractor::class)->extract($tmp);

    expect($r['manager_name'])->toBeNull();
    expect($r['manager_mobile'])->toBeNull();
    expect($r['field_confidence']['manager_name'])->toBe(1.0);
    expect($r['field_confidence']['manager_mobile'])->toBe(0.0);

    @unlink($tmp);
});

it('trims blank strings to null for manager_name and manager_mobile', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'manager_name' => '   ',
                        'manager_mobile' => '',
                        'field_confidence' => [],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'mai').'.jpg';
    file_put_contents($tmp, 'fake-image-bytes');

    $r = app(ManagerAiExtractor::class)->extract($tmp);

    expect($r['manager_name'])->toBeNull();
    expect($r['manager_mobile'])->toBeNull();

    @unlink($tmp);
});
