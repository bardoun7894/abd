<?php

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Models\InvoiceBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 012 bundle A — index() and exportBatches() honour date_from/date_to
 * (created_at) and min_count (processed_pages) alongside the existing q/status
 * filters. Same two-independent-:memory:-sqlite-DB pattern as
 * InvoiceBulkPushTest: main connection and the isolated `invoices` connection
 * are each their own sqlite :memory: DB, so index()/exportBatches() run
 * end-to-end (including Shop::get() + get_manager()) without touching Docker.
 */
beforeEach(function () {
    InvoiceBatch::flushEventListeners();

    // Main (mysql in prod) connection -> sqlite :memory: for this run.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    // Isolated `invoices` connection -> its OWN sqlite :memory: DB.
    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    // Minimal main-schema tables index()/exportBatches() touch via Shop::get()
    // and get_manager() (admin branch just reads `manager`).
    Schema::create('shop', function ($t) {
        $t->id('shop_id');
        $t->string('shop_name')->nullable();
    });
    Schema::create('manager', function ($t) {
        $t->id('manager_id');
    });
    // Non-admin branch of get_manager() joins manager -> workers_manager.
    Schema::create('workers_manager', function ($t) {
        $t->id();
        $t->unsignedBigInteger('manager_id')->nullable();
        $t->unsignedBigInteger('user_id')->nullable();
    });

    // Real invoices-side schema on the isolated connection.
    require_once base_path('database/migrations/invoices/2026_06_16_000001_create_invoice_batches_table.php');
    require_once base_path('database/migrations/invoices/2026_06_16_000002_create_invoices_table.php');
    require_once base_path('database/migrations/invoices/2026_06_17_000005_add_purchase_mapping_to_invoices.php');
    require_once base_path('database/migrations/invoices/2026_06_23_000011_create_invoice_items_table.php');

    (new CreateInvoiceBatchesTable())->up();
    (new CreateInvoicesTable())->up();
    (new AddPurchaseMappingToInvoices())->up();
    (new CreateInvoiceItemsTable())->up();
});

afterEach(function () {
    InvoiceBatch::flushEventListeners();
    Mockery::close();
});

function filtersActingAsAdmin(int $id = 1): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => 1, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

function filtersActingAsNonAdmin(int $id): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => 0, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

/** Seed a batch (isolated conn) with a given created_at + processed_pages. */
function seedFilterBatch(int $userId, string $createdAt, int $processedPages): int
{
    return DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => $userId,
        'original_filename' => 'batch-'.$processedPages.'-'.$createdAt.'.pdf',
        'status' => 'done',
        'processed_pages' => $processedPages,
        'grand_total' => 100,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);
}

// ---- index() ------------------------------------------------------------

it('index() filters by date_from/date_to on created_at', function () {
    filtersActingAsAdmin();

    $old = seedFilterBatch(1, '2026-01-01 10:00:00', 3);
    $mid = seedFilterBatch(1, '2026-06-15 10:00:00', 3);
    $new = seedFilterBatch(1, '2026-12-31 10:00:00', 3);

    $request = Request::create('/', 'GET', ['date_from' => '2026-06-01', 'date_to' => '2026-06-30']);
    $view = (new InvoiceController())->index($request);
    $ids = collect($view->getData()['batches']->items())->pluck('id')->all();

    expect($ids)->toBe([$mid]);
    expect($ids)->not->toContain($old);
    expect($ids)->not->toContain($new);
});

it('index() filters by min_count on processed_pages', function () {
    filtersActingAsAdmin();

    $small = seedFilterBatch(1, '2026-05-01 10:00:00', 2);
    $big = seedFilterBatch(1, '2026-05-02 10:00:00', 10);

    $request = Request::create('/', 'GET', ['min_count' => '5']);
    $view = (new InvoiceController())->index($request);
    $ids = collect($view->getData()['batches']->items())->pluck('id')->all();

    expect($ids)->toBe([$big]);
    expect($ids)->not->toContain($small);
});

it('index() ignores a non-numeric min_count instead of erroring', function () {
    filtersActingAsAdmin();

    $a = seedFilterBatch(1, '2026-05-01 10:00:00', 2);
    $b = seedFilterBatch(1, '2026-05-02 10:00:00', 10);

    $request = Request::create('/', 'GET', ['min_count' => 'abc']);
    $view = (new InvoiceController())->index($request);
    $ids = collect($view->getData()['batches']->items())->pluck('id')->all();

    sort($ids);
    expect($ids)->toBe([$a, $b]); // filter silently skipped, both returned
});

it('index() combines date_from/date_to and min_count (AND semantics)', function () {
    filtersActingAsAdmin();

    $matches = seedFilterBatch(1, '2026-06-15 10:00:00', 10);
    seedFilterBatch(1, '2026-06-15 10:00:00', 1);   // in range, too few invoices
    seedFilterBatch(1, '2026-01-01 10:00:00', 10);  // enough invoices, out of range

    $request = Request::create('/', 'GET', [
        'date_from' => '2026-06-01', 'date_to' => '2026-06-30', 'min_count' => '5',
    ]);
    $view = (new InvoiceController())->index($request);
    $ids = collect($view->getData()['batches']->items())->pluck('id')->all();

    expect($ids)->toBe([$matches]);
});

it('index() combines a date filter with non-admin user_id ownership scoping', function () {
    filtersActingAsNonAdmin(42);
    DB::table('workers_manager')->insert(['manager_id' => 1, 'user_id' => 42]);

    $mine = seedFilterBatch(42, '2026-06-15 10:00:00', 3);
    seedFilterBatch(99, '2026-06-15 10:00:00', 3); // in range, but another user's batch

    $request = Request::create('/', 'GET', ['date_from' => '2026-06-01', 'date_to' => '2026-06-30']);
    $view = (new InvoiceController())->index($request);
    $ids = collect($view->getData()['batches']->items())->pluck('id')->all();

    expect($ids)->toBe([$mine]);
});

// ---- exportBatches() mirrors index() -------------------------------------

it('exportBatches() mirrors index()\'s date_from/date_to/min_count filters', function () {
    filtersActingAsAdmin();

    $old = seedFilterBatch(1, '2026-01-01 10:00:00', 10);
    $matches = seedFilterBatch(1, '2026-06-15 10:00:00', 10);
    $tooFew = seedFilterBatch(1, '2026-06-16 10:00:00', 1);

    $request = Request::create('/', 'GET', [
        'date_from' => '2026-06-01', 'date_to' => '2026-06-30', 'min_count' => '5',
    ]);
    $resp = (new InvoiceController())->exportBatches($request);

    ob_start();
    $resp->sendContent();
    $bytes = ob_get_clean();

    $tmp = tempnam(sys_get_temp_dir(), 'filterxlsx').'.xlsx';
    file_put_contents($tmp, $bytes);
    $loaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
    @unlink($tmp);

    $ids = collect($loaded->getActiveSheet()->toArray())
        ->map(fn ($r) => $r[0] ?? null)
        ->filter(fn ($v) => is_numeric($v))
        ->map(fn ($v) => (int) $v)
        ->values()
        ->all();

    expect($ids)->toBe([$matches]);
    expect($ids)->not->toContain($old);
    expect($ids)->not->toContain($tooFew);
});
