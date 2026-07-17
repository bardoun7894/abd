<?php

// Tests the hardened JSON decoder in InvoiceExtractionService: fenced JSON,
// thinking-model thought parts, and truncated JSON salvage.
uses(Tests\TestCase::class);

use App\Services\InvoiceExtractionService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
});

function invoicePdfTemp(): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    return $tmp;
}

function geminiInvoiceResponse(array $parts, int $status = 200): array
{
    return [
        'candidates' => [[
            'content' => ['parts' => $parts],
        ]],
    ];
}

function validInvoicePayload(): array
{
    return [
        'supplier_name' => 'شركة نهلة الوادي للتجارة',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'NHD2522236491',
        'invoice_date' => '2026-06-15',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'confidence' => 0.97,
    ];
}

it('decodes JSON wrapped in markdown fences', function () {
    $tmp = invoicePdfTemp();
    Http::fake([
        '*' => Http::response(geminiInvoiceResponse([[
            'text' => "```json\n".json_encode(validInvoicePayload())."\n```",
        ]]), 200),
    ]);

    $r = app(InvoiceExtractionService::class)->extractInvoice($tmp);

    expect($r['supplier_name'])->toBe('شركة نهلة الوادي للتجارة');
    expect($r['invoice_number'])->toBe('NHD2522236491');
    expect($r['amount_before_vat'])->toBe(100.0);

    @unlink($tmp);
});

it('decodes JSON from a thinking-model response with thought parts', function () {
    $tmp = invoicePdfTemp();
    Http::fake([
        '*' => Http::response(geminiInvoiceResponse([
            ['thought' => 'I should look for the supplier tax number at the top.'],
            ['text' => json_encode(validInvoicePayload())],
        ]), 200),
    ]);

    $r = app(InvoiceExtractionService::class)->extractInvoice($tmp);

    expect($r['supplier_tax_number'])->toBe('300097525940003');
    expect($r['total_incl_vat'])->toBe(115.0);

    @unlink($tmp);
});

it('salvages truncated JSON by keeping every complete top-level pair', function () {
    $tmp = invoicePdfTemp();
    // Missing closing brace and any trailing pairs, but all required fields are complete.
    $truncated = '{"supplier_name":"شركة نهلة الوادي للتجارة","supplier_tax_number":"300097525940003","invoice_number":"NHD2522236491","invoice_date":"2026-06-15","amount_before_vat":100,"vat_amount":15,"total_incl_vat":115';
    Http::fake([
        '*' => Http::response(geminiInvoiceResponse([['text' => $truncated]]), 200),
    ]);

    $r = app(InvoiceExtractionService::class)->extractInvoice($tmp);

    expect($r['invoice_number'])->toBe('NHD2522236491');
    expect($r['amount_before_vat'])->toBe(100.0);
    expect($r['vat_amount'])->toBe(15.0);
    // total_incl_vat pair is the unfinished tail with no trailing comma, so it is dropped.
    expect($r['total_incl_vat'])->toBeNull();

    @unlink($tmp);
});
