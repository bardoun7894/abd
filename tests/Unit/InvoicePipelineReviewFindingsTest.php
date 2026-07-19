<?php

// Regression tests for the invoice/lease pipeline code-review findings:
//   - DuplicateDetector normalizes invoice numbers before matching candidates.
//   - InvoicePipeline::flagDuplicates compares normalized numbers in memory (no N+1).
//   - InvoicePipeline::run() marks batch 'failed' when every invoice failed.
//   - LeasePipeline::run() counts only successful rows in processed_pages and marks
//     the batch 'failed' when all pages failed.
//
// Uses the isolated `invoices` sqlite file (same as the other unit tests) and
// switches the default connection to an in-memory sqlite database for the few
// operations that touch the main schema.
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Models\LeaseBatch;
use App\Models\LeaseExtraction;
use App\Services\DuplicateDetector;
use App\Services\InvoiceExtractionService;
use App\Services\InvoicePipeline;
use App\Services\LeaseExtractionService;
use App\Services\LeasePipeline;
use App\Services\PdfPageRasterizer;
use App\Services\PdfPageSplitter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Switch the default connection to a dedicated sqlite test file so schema
    // created in one test persists for the next, while transactions roll back data.
    $path = database_path('testing.sqlite');
    if (! is_file($path)) {
        touch($path);
    }
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => $path]);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    DB::connection('invoices')->beginTransaction();
    DB::connection('sqlite')->beginTransaction();
});

afterEach(function () {
    DB::connection('sqlite')->rollBack();
    DB::connection('invoices')->rollBack();
    Mockery::close();
});

function makeInvoiceBatch(array $overrides = []): InvoiceBatch
{
    return InvoiceBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/test.pdf',
        'status' => 'processing',
        'total_pages' => 1,
        'processed_pages' => 0,
        'grand_total' => 0,
    ], $overrides));
}

function makeLeaseBatch(array $overrides = []): LeaseBatch
{
    return LeaseBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'lease-test.pdf',
        'pdf_path' => 'uploads/leases/pdf/lease-test.pdf',
        'status' => 'processing',
        'total_pages' => 1,
        'processed_pages' => 0,
    ], $overrides));
}

function makeInvoice(int $batchId, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        'supplier_name' => 'شركة الاختبار',
        'supplier_tax_number' => '300000000000003',
        'invoice_number' => 'INV-1',
        'invoice_date' => '2026-07-01',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'status' => 'done',
        'needs_review' => false,
    ], $overrides));
}

// ---- DuplicateDetector normalization -----------------------------------------

it('finds a duplicate invoice when numbers differ only by spacing', function () {
    $batch1 = makeInvoiceBatch();
    $prior = makeInvoice($batch1->id, [
        'invoice_number' => 'ABC 123',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
        'invoice_date' => '2026-07-01',
    ]);

    $detector = new DuplicateDetector();
    $dup = $detector->findDuplicate([
        'invoice_number' => 'abc123',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
        'invoice_date' => '2026-07-01',
    ], $batch1->id);

    expect($dup)->not->toBeNull();
    expect($dup['invoice']->id)->toBe($prior->id);
});

it('does not flag unrelated invoices as duplicates after normalization', function () {
    $batch1 = makeInvoiceBatch();
    makeInvoice($batch1->id, [
        'invoice_number' => 'ABC 123',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
    ]);

    $detector = new DuplicateDetector();
    $dup = $detector->findDuplicate([
        'invoice_number' => 'XYZ 999',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
    ], $batch1->id);

    expect($dup)->toBeNull();
});

// ---- InvoicePipeline flagDuplicates ------------------------------------------

it('flags cross-batch duplicates using normalized invoice numbers', function () {
    $batch1 = makeInvoiceBatch();
    makeInvoice($batch1->id, ['invoice_number' => 'ABC 123']);

    $batch2 = makeInvoiceBatch();
    $later = makeInvoice($batch2->id, [
        'invoice_number' => 'abc123',
        'validation_notes' => '',
    ]);

    $pipeline = new InvoicePipeline(
        new PdfPageSplitter(),
        new InvoiceExtractionService(),
        new PdfPageRasterizer(),
    );

    // flagDuplicates is private; use reflection.
    $method = new ReflectionMethod($pipeline, 'flagDuplicates');
    $method->setAccessible(true);
    $method->invoke($pipeline, $batch2);

    $fresh = $later->fresh();
    expect($fresh->needs_review)->toBeTrue();
    expect($fresh->validation_notes)->toContain('دفعة أخرى');
});

it('does not query the database per invoice when flagging cross-batch duplicates', function () {
    $batch1 = makeInvoiceBatch();
    for ($i = 1; $i <= 5; $i++) {
        makeInvoice($batch1->id, ['invoice_number' => 'DUP-'.$i, 'page_number' => $i]);
    }

    $batch2 = makeInvoiceBatch();
    for ($i = 1; $i <= 5; $i++) {
        makeInvoice($batch2->id, ['invoice_number' => 'DUP-'.$i, 'page_number' => $i]);
    }

    $pipeline = new InvoicePipeline(
        new PdfPageSplitter(),
        new InvoiceExtractionService(),
        new PdfPageRasterizer(),
    );

    // Enable query logging on the invoices connection to count cross-batch lookups.
    DB::connection('invoices')->enableQueryLog();

    $method = new ReflectionMethod($pipeline, 'flagDuplicates');
    $method->setAccessible(true);
    $method->invoke($pipeline, $batch2);

    $queries = DB::connection('invoices')->getQueryLog();
    // One query for the batch invoices + one query for cross-batch candidates.
    // Previously this issued one exists() query per invoice.
    $selects = collect($queries)->filter(fn ($q) => str_contains($q['query'] ?? '', 'select'))->count();
    expect($selects)->toBeLessThanOrEqual(2);
});

// ---- InvoicePipeline::run() status -------------------------------------------

it('marks an invoice batch failed when every page extraction fails', function () {
    $batch = makeInvoiceBatch(['status' => 'pending']);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->andReturn([public_path('uploads/invoices/pdf/test.pdf')]);

    $service = Mockery::mock(InvoiceExtractionService::class);
    $service->shouldReceive('extractInvoice')->andThrow(new RuntimeException('AI call failed'));
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new InvoicePipeline(
        new PdfPageSplitter(),
        $service,
        $rasterizer,
    );

    $pipeline->run($batch, public_path('uploads/invoices/pdf/test.pdf'), null, null, 'split');

    expect($batch->fresh()->status)->toBe('failed');
    expect($batch->fresh()->processed_pages)->toBe(1); // total persisted rows
});

it('keeps an invoice batch done when some pages succeed', function () {
    $batch = makeInvoiceBatch(['status' => 'pending']);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->andReturn([
        public_path('uploads/invoices/pdf/test.pdf'),
        public_path('uploads/invoices/pdf/test.pdf'),
    ]);

    $service = Mockery::mock(InvoiceExtractionService::class);
    $service->shouldReceive('extractInvoice')
        ->once()->andReturn([
            'supplier_name' => 'مورد 1',
            'invoice_number' => 'OK-1',
            'invoice_date' => '2026-07-01',
            'total_incl_vat' => 100,
            'needs_review' => false,
            '_in' => 1,
            '_out' => 1,
        ]);
    $service->shouldReceive('extractInvoice')
        ->once()->andThrow(new RuntimeException('AI call failed'));
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new InvoicePipeline(
        new PdfPageSplitter(),
        $service,
        $rasterizer,
    );

    $pipeline->run($batch, public_path('uploads/invoices/pdf/test.pdf'), null, null, 'split');

    expect($batch->fresh()->status)->toBe('done');
});

// ---- LeasePipeline::run() status ---------------------------------------------

it('counts only successful lease extractions in processed_pages and marks all-failed as failed', function () {
    $batch = makeLeaseBatch(['status' => 'pending']);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->andReturn([public_path('uploads/invoices/pdf/test.pdf')]);

    $service = Mockery::mock(LeaseExtractionService::class);
    $service->shouldReceive('extractLease')->andThrow(new RuntimeException('AI call failed'));
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new LeasePipeline(
        $rasterizer,
        new PdfPageSplitter(),
        $service,
    );

    $pipeline->run($batch, public_path('uploads/invoices/pdf/test.pdf'));

    $fresh = $batch->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->processed_pages)->toBe(0);
    expect($batch->extractions()->where('status', 'failed')->count())->toBe(1);
});

it('keeps a lease batch done when at least one page succeeds', function () {
    $batch = makeLeaseBatch(['status' => 'pending', 'total_pages' => 2]);

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('rasterize')->andReturn([
        public_path('uploads/invoices/pdf/test.pdf'),
        public_path('uploads/invoices/pdf/test.pdf'),
    ]);

    $service = Mockery::mock(LeaseExtractionService::class);
    $service->shouldReceive('extractLease')
        ->once()->andReturn(['contract_no' => 'L-1', '_in' => 1, '_out' => 1]);
    $service->shouldReceive('extractLease')
        ->once()->andThrow(new RuntimeException('AI call failed'));
    $service->shouldReceive('costUsd')->andReturn(0.0);

    $pipeline = new LeasePipeline(
        $rasterizer,
        new PdfPageSplitter(),
        $service,
    );

    $pipeline->run($batch, public_path('uploads/invoices/pdf/test.pdf'));

    $fresh = $batch->fresh();
    expect($fresh->status)->toBe('done');
    expect($fresh->processed_pages)->toBe(1);
});
