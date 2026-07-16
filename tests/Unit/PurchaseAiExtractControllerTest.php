<?php

// Boots the Laravel app (Http facade + container) but does NOT use RefreshDatabase —
// the main DB is remote and this test never touches it. Mirrors
// tests/Unit/ShopAiExtractControllerTest.php. The Gemini HTTP call is faked — this
// NEVER hits the live Gemini API. Exercises PurchaseController::aiExtract(), which
// reuses InvoiceExtractionService + InvoicePurchaseMapper (read-only reuse, not
// rebuilt) to prefill the purchase add form.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\PurchaseController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    $dir = public_path('uploads/purchase/ai');
    if (is_dir($dir)) {
        foreach (glob($dir.'/*') as $f) {
            @unlink($f);
        }
    }
});

it('rejects the request when no invoice file is uploaded', function () {
    $controller = new PurchaseController;
    $request = Request::create('/dashboard/purchase/ai-extract', 'POST');

    expect(fn () => $controller->aiExtract($request))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('extracts an invoice and maps it onto the purchase form field ids', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'supplier_name' => 'شركة نهلة الوادي للتجارة',
                        'supplier_tax_number' => '300097525940003',
                        'invoice_number' => 'NHD2522236491',
                        'invoice_date' => '2026-06-15',
                        'amount_before_vat' => 100,
                        'vat_amount' => 15,
                        'total_incl_vat' => 115,
                        'confidence' => 0.92,
                        'image_quality' => 'clear',
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $file = UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf');
    $request = Request::create('/dashboard/purchase/ai-extract', 'POST');
    $request->files->set('invoice', $file);

    $controller = new PurchaseController;
    $response = $controller->aiExtract($request);

    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    // Exact purchase add-form input ids (resources/views/dashboard/purchase/index.blade.php).
    expect($payload['data']['purchase_no'])->toBe('NHD2522236491');
    expect($payload['data']['purchase_dt'])->toBe('2026-06-15');
    expect($payload['data']['purchase_respon'])->toBe('شركة نهلة الوادي للتجارة');
    expect((float) $payload['data']['purchase_price'])->toBe(115.0);
    expect($payload['data']['tax_number'])->toBe('300097525940003');
    expect($payload['data']['note'])->toContain('100'); // amount before VAT preserved in note
    expect($payload['data']['note'])->toContain('15');  // VAT amount preserved in note
    expect($payload['data'])->toHaveKey('invoice_file_url');
    expect($payload['data']['needs_review'])->toBeFalse();
});

it('returns a friendly failure when extraction throws', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake([
        '*' => Http::response('boom', 500),
    ]);
    config()->set('services.gemini.retries', 1);

    $file = UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf');
    $request = Request::create('/dashboard/purchase/ai-extract', 'POST');
    $request->files->set('invoice', $file);

    $controller = new PurchaseController;
    $response = $controller->aiExtract($request);

    $payload = $response->getData(true);

    expect($response->getStatusCode())->toBe(422);
    expect($payload['status'])->toBeFalse();
    expect($payload)->toHaveKey('message_out');
});
