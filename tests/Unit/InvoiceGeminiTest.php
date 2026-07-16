<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\InvoiceExtractionService;
use App\Services\PdfPageSplitter;
use Illuminate\Support\Facades\Http;

it('builds a Gemini request and parses the structured JSON response', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');

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
                        'confidence' => 0.97,
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $r = app(InvoiceExtractionService::class)->extractInvoice($tmp);

    expect($r['supplier_name'])->toBe('شركة نهلة الوادي للتجارة');
    expect($r['supplier_tax_number'])->toBe('300097525940003');
    expect($r['invoice_number'])->toBe('NHD2522236491');
    expect($r['amount_before_vat'])->toBe(100.0);
    expect($r['total_incl_vat'])->toBe(115.0);
    expect($r['needs_review'])->toBeFalse();

    Http::assertSent(fn ($req) => str_contains($req->url(), ':generateContent')
        && data_get($req->data(), 'generationConfig.responseMimeType') === 'application/json');

    @unlink($tmp);
});

it('throws on a Gemini 429 so the job can back off', function () {
    config()->set('services.gemini.key', 'test-key');
    Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);

    $tmp = tempnam(sys_get_temp_dir(), 'inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    expect(fn () => app(InvoiceExtractionService::class)->extractInvoice($tmp))
        ->toThrow(RuntimeException::class);

    @unlink($tmp);
});

it('splits the sample multi-invoice PDF into one PDF per page', function () {
    $src = base_path('storage/sample_invoices.pdf');
    if (! is_file($src)) {
        test()->markTestSkipped('sample PDF not present');
    }

    $dir = sys_get_temp_dir().'/split_'.uniqid();
    $pages = app(PdfPageSplitter::class)->split($src, $dir);

    expect(count($pages))->toBe(3);
    foreach ($pages as $p) {
        expect(is_file($p))->toBeTrue();
        @unlink($p);
    }
    @rmdir($dir);
});
