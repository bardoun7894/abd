<?php

uses(Tests\TestCase::class);

use App\Services\InvoiceExtractionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Client report (2026-08-17): «لما يحمل ملف PDF وفيه عدة فواتير، النظام ما يفرز
 * الفواتير … الذكاء حط كل الفواتير ما عزل كل فاتورة لحالها».
 *
 * The system was built one-invoice-per-page from top to bottom:
 *   - invoices table: unique(batch_id, page_number)
 *   - perPage(): extractInvoice() + singleSchema() + "استخرج سجلًا واحدًا"
 *   - persist(): updateOrCreate on (batch_id, page_number)
 * so a page carrying 2+ invoices was merged into one row (or silently overwritten
 * when the whole-document path returned several rows for the same page).
 */
beforeEach(function () {
    config()->set('database.connections.invoices', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => false,
    ]);
    DB::purge('invoices');

    Schema::connection('invoices')->create('invoices', function ($t) {
        $t->increments('id');
        $t->unsignedBigInteger('batch_id');
        $t->unsignedInteger('page_number')->default(1);
        $t->string('invoice_number')->nullable();
        $t->unique(['batch_id', 'page_number']);
    });

    require_once base_path('database/migrations/invoices/2026_08_17_000017_add_seq_for_multi_invoice_pages.php');
});

afterEach(function () {
    Schema::connection('invoices')->dropIfExists('invoices');
});

it('adds seq and lets two invoices live on the SAME page', function () {
    (new AddSeqForMultiInvoicePages())->up();

    expect(Schema::connection('invoices')->hasColumn('invoices', 'seq'))->toBeTrue();

    DB::connection('invoices')->table('invoices')->insert([
        ['batch_id' => 1, 'page_number' => 1, 'seq' => 1, 'invoice_number' => 'A-1'],
        ['batch_id' => 1, 'page_number' => 1, 'seq' => 2, 'invoice_number' => 'A-2'],
        ['batch_id' => 1, 'page_number' => 1, 'seq' => 3, 'invoice_number' => 'A-3'],
    ]);

    expect(DB::connection('invoices')->table('invoices')->where('batch_id', 1)->count())->toBe(3);
});

it('still rejects a true duplicate of the same (page, seq)', function () {
    (new AddSeqForMultiInvoicePages())->up();

    DB::connection('invoices')->table('invoices')->insert(
        ['batch_id' => 1, 'page_number' => 2, 'seq' => 1, 'invoice_number' => 'B-1']
    );

    expect(fn () => DB::connection('invoices')->table('invoices')->insert(
        ['batch_id' => 1, 'page_number' => 2, 'seq' => 1, 'invoice_number' => 'B-DUP']
    ))->toThrow(\Illuminate\Database\QueryException::class);
});

it('is re-runnable and defaults every historical row to seq=1', function () {
    DB::connection('invoices')->table('invoices')->insert(
        ['batch_id' => 9, 'page_number' => 4, 'invoice_number' => 'OLD-1']
    );

    (new AddSeqForMultiInvoicePages())->up();
    (new AddSeqForMultiInvoicePages())->up(); // idempotent

    $row = DB::connection('invoices')->table('invoices')->where('invoice_number', 'OLD-1')->first();
    expect((int) $row->seq)->toBe(1);
});

/*
 * Extraction side: a single page must be allowed to yield MORE THAN ONE invoice.
 * The page-scoped multi extractor keeps every returned record on the page it was
 * read from (the whole-document extractor auto-increments page_number, which is
 * wrong when every invoice sits on the same physical page).
 */
it('keeps every invoice of one page on that page instead of auto-incrementing', function () {
    $svc = new InvoiceExtractionService();

    $rows = (new ReflectionMethod(InvoiceExtractionService::class, 'normalizePageInvoices'))
        ->invoke($svc, [
            ['invoice_number' => 'P-1', 'total_incl_vat' => 10],
            ['invoice_number' => 'P-2', 'total_incl_vat' => 20],
            ['invoice_number' => 'P-3', 'total_incl_vat' => 30],
        ], 7);

    expect($rows)->toHaveCount(3);
    foreach ($rows as $i => $r) {
        expect($r['page_number'])->toBe(7);   // all on page 7
        expect($r['seq'])->toBe($i + 1);      // ordinal within the page
    }
});

it('treats a page with a single invoice exactly as before', function () {
    $svc = new InvoiceExtractionService();

    $rows = (new ReflectionMethod(InvoiceExtractionService::class, 'normalizePageInvoices'))
        ->invoke($svc, [['invoice_number' => 'SOLO', 'total_incl_vat' => 5]], 3);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['page_number'])->toBe(3);
    expect($rows[0]['seq'])->toBe(1);
});

it('asks the model for EVERY invoice on the page, not just one', function () {
    $svc = new InvoiceExtractionService();
    $prompt = (new ReflectionMethod(InvoiceExtractionService::class, 'prompt'))->invoke($svc, true);

    // The multi prompt must not claim the file holds exactly one invoice.
    expect($prompt)->not->toContain('استخرج سجلًا واحدًا بكل الحقول');
    expect($prompt)->toContain('لكل فاتورة مستقلة');
});
