<?php

// Boot the Laravel app (Http facade + container) but DO NOT use RefreshDatabase —
// the main DB is remote/local Docker MySQL and this test doesn't need it.
uses(Tests\TestCase::class);

use App\Http\Controllers\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

afterEach(function () {
    $dir = public_path('uploads/vehicles/ai');
    if (File::isDirectory($dir)) {
        File::deleteDirectory($dir);
    }
    $privateDir = storage_path('app/private/uploads/vehicles/ai');
    if (File::isDirectory($privateDir)) {
        File::deleteDirectory($privateDir);
    }
});

function fakeVehicleDocument(): UploadedFile
{
    $tmp = tempnam(sys_get_temp_dir(), 'vehdoc').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake vehicle document');

    return new UploadedFile($tmp, 'document.pdf', 'application/pdf', null, true);
}

it('aiExtract() returns plate number and expiry dates for the vehicle form to prefill', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'plate_number' => '1234 أ ب ج',
                        'owner_name' => 'خالد سالم',
                        'model' => 'هوندا أكورد 2021',
                        'license_expiry' => '2026-04-10',
                        'insurance_expiry' => '2026-06-01',
                        'operation_card_expiry' => '2026-08-15',
                        'field_confidence' => ['plate_number' => 0.9],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $request = Request::create('/vehicles/ai-extract', 'POST');
    $request->files->set('document', fakeVehicleDocument());

    $controller = new VehicleController();
    $response = $controller->aiExtract($request);
    $payload = json_decode($response->getContent(), true);

    expect($payload['status'])->toBeTrue();
    expect($payload['data']['plate_number'])->toBe('1234 أ ب ج');
    expect($payload['data']['owner_name'])->toBe('خالد سالم');
    expect($payload['data']['model'])->toBe('هوندا أكورد 2021');
    expect($payload['data']['license_expiry'])->toBe('2026-04-10');
    expect($payload['data']['insurance_expiry'])->toBe('2026-06-01');
    expect($payload['data']['operation_card_expiry'])->toBe('2026-08-15');
    $storedFilename = basename($payload['data']['document_url']);
    $expectedUrl = route('dashboard.documents.serve', ['module' => 'vehicles', 'filename' => $storedFilename]);
    expect($payload['data']['document_url'])->toBe($expectedUrl);

    $privatePath = storage_path('app/private/uploads/vehicles/ai/'.$storedFilename);
    expect(is_file($privatePath))->toBeTrue();
});

it('aiExtract() returns a 422 with an Arabic error message when Gemini extraction fails', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.retries', 1);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    $request = Request::create('/vehicles/ai-extract', 'POST');
    $request->files->set('document', fakeVehicleDocument());

    $controller = new VehicleController();
    $response = $controller->aiExtract($request);
    $payload = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('تعذّر استخراج البيانات');
});

it('aiExtract() rejects a request without a document file', function () {
    $request = Request::create('/vehicles/ai-extract', 'POST');
    $controller = new VehicleController();

    expect(fn () => $controller->aiExtract($request))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});
