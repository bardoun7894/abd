<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\LeaseExtractionService;
use Illuminate\Support\Facades\Http;

it('builds a Gemini request and parses the structured lease JSON response', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'contract_no' => 'LC-2026-014',
                        'tenant_name' => 'شركة الأفق التجارية',
                        'landlord_name' => 'محمد العتيبي',
                        'start_date' => '2026-01-01',
                        'end_date' => '2026-12-31',
                        'rent_value' => 12000,
                        'num_payments' => 12,
                        'payment_value' => 1000,
                        'payment_frequency' => 'شهري',
                        'confidence' => 0.95,
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'lease').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(LeaseExtractionService::class)->extractLease($tmp);

    expect($r['contract_no'])->toBe('LC-2026-014');
    expect($r['tenant_name'])->toBe('شركة الأفق التجارية');
    expect($r['rent_value'])->toBe(12000.0);
    expect($r['needs_review'])->toBeFalse();

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('throws on a Gemini 429 so the job can back off', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);

    $tmp = tempnam(sys_get_temp_dir(), 'lease').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    expect(fn () => app(LeaseExtractionService::class)->extractLease($tmp))
        ->toThrow(RuntimeException::class);

    @unlink($tmp);
});
