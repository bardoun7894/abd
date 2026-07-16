<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\ShopAiExtractor;
use Illuminate\Support\Facades\Http;

function fakeGeminiJson(array $payload): void
{
    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode($payload),
                ]]],
            ]],
        ], 200),
    ]);
}

function tmpDoc(string $ext = 'pdf'): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'shopdoc').'.'.$ext;
    file_put_contents($tmp, '%PDF-1.4 fake');

    return $tmp;
}

it('extracts a commercial registration document', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

    fakeGeminiJson([
        'document_type' => 'commercial_registration',
        'document_number' => '1010123456',
        'issue_date' => '2020-01-15',
        'expiry_date' => '2027-01-15',
        'owner_name' => 'مؤسسة الأفق التجارية',
        'rent_amount' => null,
        'field_confidence' => [
            'document_number' => 0.98,
            'issue_date' => 0.9,
            'expiry_date' => 0.95,
            'owner_name' => 0.8,
        ],
    ]);

    $tmp = tmpDoc();
    $r = app(ShopAiExtractor::class)->extract($tmp);

    expect($r['document_type'])->toBe('commercial_registration');
    expect($r['document_number'])->toBe('1010123456');
    expect($r['issue_date'])->toBe('2020-01-15');
    expect($r['expiry_date'])->toBe('2027-01-15');
    expect($r['owner_name'])->toBe('مؤسسة الأفق التجارية');
    expect($r['rent_amount'])->toBeNull();
    expect($r['field_confidence']['document_number'])->toBe(0.98);

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('extracts a municipal license document', function () {
    config()->set('services.gemini.key', 'test-key');

    fakeGeminiJson([
        'document_type' => 'municipal_license',
        'document_number' => 'MUN-9988',
        'issue_date' => '2024-03-01',
        'expiry_date' => '2025-03-01',
        'owner_name' => 'محل النور',
        'rent_amount' => null,
        'field_confidence' => ['document_number' => 0.9, 'expiry_date' => 0.92],
    ]);

    $tmp = tmpDoc('jpg');
    $r = app(ShopAiExtractor::class)->extract($tmp);

    expect($r['document_type'])->toBe('municipal_license');
    expect($r['document_number'])->toBe('MUN-9988');
    expect($r['expiry_date'])->toBe('2025-03-01');

    @unlink($tmp);
});

it('extracts a lease document with a rent amount', function () {
    config()->set('services.gemini.key', 'test-key');

    fakeGeminiJson([
        'document_type' => 'lease',
        'document_number' => 'RENT-77',
        'issue_date' => '2026-01-01',
        'expiry_date' => '2027-01-01',
        'owner_name' => 'عبدالله المالك',
        'rent_amount' => 24000,
        'field_confidence' => ['document_number' => 0.85, 'expiry_date' => 0.88],
    ]);

    $tmp = tmpDoc();
    $r = app(ShopAiExtractor::class)->extract($tmp);

    expect($r['document_type'])->toBe('lease');
    expect($r['rent_amount'])->toBe(24000.0);
    expect($r['owner_name'])->toBe('عبدالله المالك');

    @unlink($tmp);
});

it('normalizes arabic digits in numbers and dates and nulls out missing fields', function () {
    config()->set('services.gemini.key', 'test-key');

    fakeGeminiJson([
        'document_type' => 'commercial_registration',
        'document_number' => '١٠١٠١٢٣٤٥٦',
        'issue_date' => null,
        'expiry_date' => '٢٠٢٦-٠٧-٣٠',
        'owner_name' => '',
        'rent_amount' => '٢٤٠٠٠',
        'field_confidence' => null,
    ]);

    $tmp = tmpDoc();
    $r = app(ShopAiExtractor::class)->extract($tmp);

    expect($r['document_number'])->toBe('1010123456');
    expect($r['issue_date'])->toBeNull();
    expect($r['expiry_date'])->toBe('2026-07-30');
    expect($r['owner_name'])->toBeNull();
    expect($r['rent_amount'])->toBe(24000.0);
    expect($r['field_confidence'])->toBe([]);

    @unlink($tmp);
});

it('throws on a Gemini 429 so the caller can surface an error', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);

    $tmp = tmpDoc();

    expect(fn () => app(ShopAiExtractor::class)->extract($tmp))
        ->toThrow(RuntimeException::class);

    @unlink($tmp);
});
