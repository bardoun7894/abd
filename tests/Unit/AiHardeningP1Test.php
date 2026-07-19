<?php

// Tests for P1 AI hardening: structured logging, per-page deadline/timeout guards,
// and pdftoppm shell timeout wrapping.
uses(Tests\TestCase::class);

use App\Jobs\ProcessInvoiceBatch;
use App\Jobs\ProcessLeaseBatch;
use App\Models\InvoiceBatch;
use App\Models\LeaseBatch;
use App\Services\GeminiClient;
use App\Services\InvoiceExtractionService;
use App\Services\InvoicePipeline;
use App\Services\LeaseExtractionService;
use App\Services\LeasePipeline;
use App\Services\PdfPageRasterizer;
use App\Services\PdfPageSplitter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

// ----------------------------------------------------------------------
// GeminiClient logging
// ----------------------------------------------------------------------
it('logs warning on 429/503 retries and info with tokens on success', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.retries', 4);

    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => ['code' => 503]], 503)
            ->push(['error' => ['code' => 429]], 429)
            ->push([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode(['result' => 'ok']),
                    ]]],
                ]],
                'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 20],
            ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'gem').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    Log::shouldReceive('warning')->twice();
    Log::shouldReceive('info')->once();
    Log::shouldReceive('error')->never();

    $client = new GeminiClient();
    $client->extract('prompt', $tmp, ['type' => 'OBJECT', 'properties' => []], 'gemini-flash-lite-latest');

    @unlink($tmp);
});

it('logs error when GeminiClient exhausts retries', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.retries', 2);

    Http::fake(['*' => Http::response(['error' => ['code' => 503]], 503)]);

    $tmp = tempnam(sys_get_temp_dir(), 'gem').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    Log::shouldReceive('warning')->once();
    Log::shouldReceive('error')->once();

    $client = new GeminiClient();
    try {
        $client->extract('prompt', $tmp, ['type' => 'OBJECT', 'properties' => []]);
    } catch (RuntimeException $e) {
        // expected
    }

    @unlink($tmp);
});

// ----------------------------------------------------------------------
// Extraction-service logging
// ----------------------------------------------------------------------
it('logs warning on 429/503 retries in InvoiceExtractionService', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.retries', 3);

    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => ['code' => 429]], 429)
            ->push([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'supplier_name' => 'Test',
                            'supplier_tax_number' => '300097525940003',
                            'invoice_number' => 'INV-1',
                            'invoice_date' => '2026-01-01',
                            'amount_before_vat' => 100,
                            'vat_amount' => 15,
                            'total_incl_vat' => 115,
                            'image_quality' => 'clear',
                        ]),
                    ]]],
                ]],
                'usageMetadata' => ['promptTokenCount' => 50, 'candidatesTokenCount' => 10],
            ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    Log::shouldReceive('warning')->once();
    Log::shouldReceive('info')->once();
    Log::shouldReceive('error')->never();

    app(InvoiceExtractionService::class)->extractInvoice($tmp);

    @unlink($tmp);
});

it('logs error when InvoiceExtractionService exhausts retries', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.retries', 1);

    Http::fake(['*' => Http::response(['error' => ['code' => 503]], 503)]);

    $tmp = tempnam(sys_get_temp_dir(), 'inv').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    Log::shouldReceive('warning')->never();
    Log::shouldReceive('error')->once();

    try {
        app(InvoiceExtractionService::class)->extractInvoice($tmp);
    } catch (RuntimeException $e) {
        // expected
    }

    @unlink($tmp);
});

it('logs warning on 429/503 retries in LeaseExtractionService', function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.retries', 3);

    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => ['code' => 503]], 503)
            ->push([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'contract_no' => 'LC-1',
                            'tenant_name' => 'Tenant',
                            'landlord_name' => 'Landlord',
                            'start_date' => '2026-01-01',
                            'end_date' => '2026-12-31',
                            'rent_value' => 1000,
                        ]),
                    ]]],
                ]],
                'usageMetadata' => ['promptTokenCount' => 40, 'candidatesTokenCount' => 8],
            ], 200),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'lease').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    Log::shouldReceive('warning')->once();
    Log::shouldReceive('info')->once();
    Log::shouldReceive('error')->never();

    app(LeaseExtractionService::class)->extractLease($tmp);

    @unlink($tmp);
});

// ----------------------------------------------------------------------
// Pipeline deadline/timeout guard
// ----------------------------------------------------------------------
it('marks remaining invoice pages failed when deadline is exceeded', function () {
    $batch = InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'deadline-test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/deadline-test.pdf',
        'status' => 'pending',
        'total_pages' => 0,
        'processed_pages' => 0,
        'grand_total' => 0,
    ]);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->once()->andReturn([
        public_path('uploads/invoices/pages/batch_'.$batch->id.'/page-1.png'),
        public_path('uploads/invoices/pages/batch_'.$batch->id.'/page-2.png'),
    ]);

    $service = Mockery::mock(InvoiceExtractionService::class);
    $service->shouldReceive('extractInvoice')->never();
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new InvoicePipeline(new PdfPageSplitter(), $service, $rasterizer);

    // Deadline in the past: no time for any AI call.
    $pipeline->run($batch, __FILE__, null, null, 'split', microtime(true) - 1);

    $batch->refresh();
    // Every page failed (deadline before any AI call) — batch must NOT claim 'done'.
    expect($batch->status)->toBe('failed');
    expect($batch->invoices()->count())->toBe(2);
    expect($batch->invoices()->where('status', 'failed')->count())->toBe(2);
});

it('processes invoice pages normally when deadline is far in the future', function () {
    $batch = InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'deadline-ok.pdf',
        'pdf_path' => 'uploads/invoices/pdf/deadline-ok.pdf',
        'status' => 'pending',
        'total_pages' => 0,
        'processed_pages' => 0,
        'grand_total' => 0,
    ]);

    $pagePath = public_path('uploads/invoices/pages/batch_'.$batch->id.'/page-1.png');
    @mkdir(dirname($pagePath), 0775, true);
    file_put_contents($pagePath, 'png');

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->once()->andReturn([$pagePath]);

    $service = Mockery::mock(InvoiceExtractionService::class);
    $service->shouldReceive('extractInvoice')->once()->andReturn([
        'supplier_name' => 'OK',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'OK-1',
        'invoice_date' => '2026-01-01',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'confidence' => 0.9,
        'image_quality' => 'clear',
        'needs_review' => false,
        'validation_notes' => [],
        '_in' => 10,
        '_out' => 5,
    ]);
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new InvoicePipeline(new PdfPageSplitter(), $service, $rasterizer);

    $pipeline->run($batch, __FILE__, null, null, 'split', microtime(true) + 3600);

    $batch->refresh();
    expect($batch->status)->toBe('done');
    expect($batch->invoices()->where('status', 'done')->count())->toBe(1);
});

it('marks remaining lease pages failed when deadline is exceeded', function () {
    $batch = LeaseBatch::create([
        'user_id' => 1,
        'original_filename' => 'deadline-lease.pdf',
        'pdf_path' => 'uploads/leases/pdf/deadline-lease.pdf',
        'status' => 'pending',
        'total_pages' => 0,
        'processed_pages' => 0,
    ]);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->once()->andReturn([
        public_path('uploads/leases/pages/batch_'.$batch->id.'/page-1.png'),
        public_path('uploads/leases/pages/batch_'.$batch->id.'/page-2.png'),
    ]);

    $splitter = Mockery::mock(PdfPageSplitter::class);
    $service = Mockery::mock(LeaseExtractionService::class);
    $service->shouldReceive('extractLease')->never();
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new LeasePipeline($rasterizer, $splitter, $service);

    $pipeline->run($batch, __FILE__, null, null, microtime(true) - 1);

    $batch->refresh();
    // Every page failed (deadline before any AI call) — batch must NOT claim 'done'.
    expect($batch->status)->toBe('failed');
    expect($batch->extractions()->count())->toBe(2);
    expect($batch->extractions()->where('status', 'failed')->count())->toBe(2);
});

// ----------------------------------------------------------------------
// PdfPageRasterizer shell timeout
// ----------------------------------------------------------------------
it('wraps pdftoppm with shell timeout and reports timeout failure', function () {
    config()->set('services.gemini.page_timeout', 5);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class)->makePartial();
    $rasterizer->shouldAllowMockingProtectedMethods();
    $rasterizer->shouldReceive('available')->andReturn(true);

    $pdf = tempnam(sys_get_temp_dir(), 'pdf').'.pdf';
    file_put_contents($pdf, '%PDF-1.4 fake');
    $outDir = sys_get_temp_dir().'/rast_'.uniqid();
    @mkdir($outDir, 0775, true);

    // Capture the command and force a timeout-like exit.
    $captured = null;
    $rasterizer->shouldReceive('exec')->andReturnUsing(function ($cmd, &$output, &$code) use (&$captured) {
        $captured = $cmd;
        $output = [];
        $code = 124;
    });

    try {
        $rasterizer->rasterize($pdf, $outDir);
    } catch (\App\Services\PdfSplitException $e) {
        expect($e->getMessage())->toContain('timed out');
    }

    expect($captured)->toContain('timeout 5');
    expect($captured)->toContain('pdftoppm');

    @unlink($pdf);
    @rmdir($outDir);
});

// ----------------------------------------------------------------------
// Job logging
// ----------------------------------------------------------------------
it('logs invoice batch job start and completion', function () {
    $batch = InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'job-log.pdf',
        'pdf_path' => 'uploads/invoices/pdf/job-log.pdf',
        'status' => 'pending',
        'total_pages' => 0,
        'processed_pages' => 0,
        'grand_total' => 0,
    ]);

    $abs = public_path($batch->pdf_path);
    @mkdir(dirname($abs), 0775, true);
    file_put_contents($abs, '%PDF-1.4 fake');

    $pipeline = Mockery::mock(InvoicePipeline::class);
    $pipeline->shouldReceive('run')->once();

    Log::shouldReceive('info')->twice();
    Log::shouldReceive('error')->never();

    $job = new ProcessInvoiceBatch($batch->id);
    $job->handle($pipeline);

    @unlink($abs);
});

it('logs lease batch job start and failure', function () {
    $batch = LeaseBatch::create([
        'user_id' => 1,
        'original_filename' => 'job-log-lease.pdf',
        'pdf_path' => 'uploads/leases/pdf/job-log-lease.pdf',
        'status' => 'pending',
        'total_pages' => 0,
        'processed_pages' => 0,
    ]);

    $abs = public_path($batch->pdf_path);
    @mkdir(dirname($abs), 0775, true);
    file_put_contents($abs, '%PDF-1.4 fake');

    $pipeline = Mockery::mock(LeasePipeline::class);
    $pipeline->shouldReceive('run')->once()->andThrow(new RuntimeException('boom'));

    Log::shouldReceive('info')->once();
    Log::shouldReceive('error')->once();

    $job = new ProcessLeaseBatch($batch->id);
    try {
        $job->handle($pipeline);
    } catch (RuntimeException $e) {
        // expected to bubble after logging
    }

    @unlink($abs);
});
