<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
// We call the controller method directly (no route is registered yet — see
// Spec 004 B2 handoff note), mirroring how ExpenseController::aiExtract's
// logic is unit-tested via the shared GeminiClient/extractor pattern.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ShopController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    $dir = public_path('uploads/shop/ai');
    if (is_dir($dir)) {
        foreach (glob($dir.'/*') as $f) {
            @unlink($f);
        }
    }
});

it('rejects the request when no document file is uploaded', function () {
    $controller = new ShopController;
    $request = Request::create('/dashboard/shop/ai-extract', 'POST');

    expect(fn () => $controller->aiExtract($request))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('extracts shop document fields for the AI-prefill widget', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'document_type' => 'municipal_license',
                        'document_number' => 'MUN-4521',
                        'issue_date' => '2024-05-01',
                        'expiry_date' => '2025-05-01',
                        'owner_name' => 'محل الأمانة',
                        'rent_amount' => null,
                        'field_confidence' => ['document_number' => 0.9, 'expiry_date' => 0.93],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $file = UploadedFile::fake()->create('license.pdf', 50, 'application/pdf');
    $request = Request::create('/dashboard/shop/ai-extract', 'POST');
    $request->files->set('document', $file);

    $controller = new ShopController;
    $response = $controller->aiExtract($request);

    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    expect($payload['data']['document_type'])->toBe('municipal_license');
    expect($payload['data']['document_number'])->toBe('MUN-4521');
    expect($payload['data']['expiry_date'])->toBe('2025-05-01');
    expect($payload['data']['issue_date'])->toBe('2024-05-01');
    expect($payload['data']['owner_name'])->toBe('محل الأمانة');
    expect($payload['data'])->toHaveKey('document_url');
    expect($payload['data'])->toHaveKey('confidence');
});
