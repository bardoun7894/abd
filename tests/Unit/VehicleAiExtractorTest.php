<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\VehicleAiExtractor;
use Illuminate\Support\Facades\Http;

it('extracts vehicle plate number, expiry dates, and owner/model from a document via Gemini', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'plate_number' => 'أ ب ج ١٢٣٤',
                        'owner_name' => 'محمد أحمد',
                        'model' => 'تويوتا كامري 2022',
                        'license_expiry' => '٢٠٢٦-٠٣-١٥',
                        'insurance_expiry' => '2026-05-01',
                        'operation_card_expiry' => '2026-07-20',
                        'field_confidence' => [
                            'plate_number' => 0.95,
                            'license_expiry' => 0.9,
                            'insurance_expiry' => 0.88,
                            'operation_card_expiry' => 0.8,
                        ],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'veh').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(VehicleAiExtractor::class)->extract($tmp);

    expect($r['plate_number'])->toBe('أ ب ج 1234');
    expect($r['owner_name'])->toBe('محمد أحمد');
    expect($r['model'])->toBe('تويوتا كامري 2022');
    expect($r['license_expiry'])->toBe('2026-03-15');
    expect($r['insurance_expiry'])->toBe('2026-05-01');
    expect($r['operation_card_expiry'])->toBe('2026-07-20');
    expect($r['field_confidence']['plate_number'])->toBe(0.95);

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('returns null for missing fields instead of guessing', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'plate_number' => null,
                        'owner_name' => null,
                        'model' => null,
                        'license_expiry' => null,
                        'insurance_expiry' => null,
                        'operation_card_expiry' => null,
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'veh').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(VehicleAiExtractor::class)->extract($tmp);

    expect($r['plate_number'])->toBeNull();
    expect($r['license_expiry'])->toBeNull();
    expect($r['insurance_expiry'])->toBeNull();
    expect($r['operation_card_expiry'])->toBeNull();

    @unlink($tmp);
});

it('throws on a Gemini HTTP failure', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);
    config()->set('services.gemini.retries', 1);

    $tmp = tempnam(sys_get_temp_dir(), 'veh').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    expect(fn () => app(VehicleAiExtractor::class)->extract($tmp))
        ->toThrow(RuntimeException::class);

    @unlink($tmp);
});
