<?php

// Spec 013 bundle B2 (purchase-sync) — PurchaseController::aiExtract() now forwards
// the shared interactive timeout/retries (config('services.gemini.interactive_timeout'
// /'interactive_retries')) into InvoiceExtractionService::extractInvoice() ->
// GeminiClient::extract(), so a slow/overloaded Gemini call fails fast instead of
// freezing a PHP-FPM worker for the full page_timeout(120) x retries(4) budget.
//
// Both tests fake Http — neither ever touches the live Gemini API.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\PurchaseController;
use App\Services\InvoiceExtractionService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

function b2InvoiceTemp(): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'b2inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    return $tmp;
}

afterEach(function () {
    $dir = public_path('uploads/purchase/ai');
    if (is_dir($dir)) {
        foreach (glob($dir.'/*') as $f) {
            @unlink($f);
        }
    }
});

// ---- Unit: InvoiceExtractionService::extractInvoice() honours the interactive budget ----

it('extractInvoice() with an interactive timeout/retries fails fast on a connection error instead of hanging on the full budget', function () {
    config()->set('services.gemini.key', 'test-key');
    // Full background budget would be page_timeout(120) x retries(4) — if the two
    // interactive params were dropped anywhere on the way to GeminiClient::extract(),
    // this call would block far longer than the assertion below allows.
    config()->set('services.gemini.page_timeout', 120);
    config()->set('services.gemini.retries', 4);

    Http::fake(function () {
        throw new ConnectionException('cURL error 28: Connection timed out');
    });

    $tmp = b2InvoiceTemp();
    $start = microtime(true);

    try {
        expect(fn () => app(InvoiceExtractionService::class)->extractInvoice($tmp, null, null, 25, 2))
            ->toThrow(RuntimeException::class, 'Gemini connection failed');
    } finally {
        @unlink($tmp);
    }

    $elapsed = microtime(true) - $start;
    // maxAttempts=2 -> exactly one retry sleep (usleep((2**1)*500_000) = ~1s). Generous
    // ceiling well under the ~120s a dropped/ignored budget would imply.
    expect($elapsed)->toBeLessThan(10.0);
});

// ---- Feature: PurchaseController::aiExtract() route surfaces a clean 422 ----

it('aiExtract() returns a clean 422 JSON error when Gemini is unreachable (ConnectionException), never a hang', function () {
    // admin (emp_job=1) short-circuits Perm::ai_access() without needing a real
    // `permission` DB row — same pattern as tests/Unit/InvoiceBulkPushTest.php.
    Auth::shouldReceive('user')->andReturn((object) ['id' => 1, 'emp_job' => 1, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('check')->andReturn(true);

    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.page_timeout', 120);
    config()->set('services.gemini.retries', 4);
    config()->set('services.gemini.interactive_timeout', 25);
    config()->set('services.gemini.interactive_retries', 2);

    Http::fake(function () {
        throw new ConnectionException('cURL error 56: Recv failure');
    });

    $file = UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf');
    $request = Request::create('/dashboard/purchase/ai-extract', 'POST');
    $request->files->set('invoice', $file);

    $start = microtime(true);
    $response = (new PurchaseController)->aiExtract($request);
    $elapsed = microtime(true) - $start;

    $payload = $response->getData(true);

    expect($response->getStatusCode())->toBe(422);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('تعذّر استخراج');
    expect($elapsed)->toBeLessThan(10.0);
});
