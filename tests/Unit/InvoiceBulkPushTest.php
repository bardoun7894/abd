<?php

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Models\Invoice;
use App\Models\InvoiceBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 012 bundle C — bulkPush() aggregation + ownership, and exportBatches()'
 * optional batch_ids[] filter. Same two-independent-:memory:-sqlite-DB pattern as
 * InvoicePurchaseMapperPushTest: the MAIN (mysql-in-prod) connection and the
 * isolated `invoices` connection are each their own sqlite :memory: DB, so the
 * real cross-connection push() runs end-to-end without touching Docker.
 */
beforeEach(function () {
    Invoice::flushEventListeners();

    // Main (mysql in prod) connection -> sqlite :memory: for this run.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    // Isolated `invoices` connection -> its OWN sqlite :memory: DB.
    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    // Minimal main-schema tables push() touches.
    Schema::create('purchase', function ($t) {
        $t->id();
        $t->string('purchase_no')->nullable();
        $t->decimal('purchase_price', 15, 3)->nullable();
        $t->date('purchase_dt')->nullable();
        $t->string('tax_number')->nullable();
        $t->string('purchase_respon')->nullable();
        $t->unsignedBigInteger('shop_id')->nullable();
        $t->unsignedBigInteger('manager_id')->nullable();
        $t->string('purchasefile')->nullable();
        $t->text('note')->nullable();
        $t->unsignedBigInteger('create_user')->nullable();
        $t->decimal('amount_before_vat', 15, 3)->nullable();
        $t->decimal('vat_amount', 15, 3)->nullable();
        $t->decimal('vat_rate', 6, 3)->nullable();
        $t->decimal('discount_total', 15, 3)->nullable();
        $t->string('currency', 10)->nullable();
        $t->string('invoice_type', 20)->nullable();
        $t->string('payment_method', 60)->nullable();
        $t->string('commercial_registration', 30)->nullable();
        $t->date('due_date')->nullable();
        $t->string('source', 20)->nullable();
        $t->unsignedBigInteger('supplier_id')->nullable();
        $t->timestamp('created_at')->nullable();
    });

    Schema::create('purchase_items', function ($t) {
        $t->id();
        $t->unsignedBigInteger('purchase_id')->index();
        $t->unsignedInteger('line_no')->default(1);
        $t->string('name')->nullable();
        $t->decimal('quantity', 14, 3)->nullable();
        $t->string('unit', 40)->nullable();
        $t->decimal('unit_price', 14, 2)->nullable();
        $t->decimal('line_total', 14, 2)->nullable();
        $t->decimal('vat_rate', 6, 3)->nullable();
        $t->decimal('vat_amount', 14, 2)->nullable();
        $t->timestamps();
    });

    // Perm::get_function_access(55) for a NON-admin reads this table.
    Schema::create('permission', function ($t) {
        $t->id();
        $t->unsignedBigInteger('emp_id')->nullable();
        $t->unsignedBigInteger('function_id')->nullable();
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
    Invoice::flushEventListeners();
    \App\Models\InvoiceItem::flushEventListeners();
    Mockery::close();
});

function bulkActingAs(int $id, int $empJob): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => $empJob, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

/** Seed a batch (isolated conn) with one invoice per row-spec; returns the batch id. */
function seedBatch(int $userId, array $invoices): int
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => $userId,
        'original_filename' => 'b'.$userId.'.pdf',
        'status' => 'done',
        'processed_pages' => count($invoices),
        'grand_total' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $page = 1;
    foreach ($invoices as $ov) {
        DB::connection('invoices')->table('invoices')->insert(array_merge([
            'batch_id' => $batchId,
            'page_number' => $page++,
            'supplier_name' => null,
            'supplier_tax_number' => null,
            'invoice_number' => 'INV-'.uniqid(),
            'invoice_date' => '2026-07-01',
            'total_incl_vat' => 100,
            'status' => 'done',
            'needs_review' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $ov));
    }

    return $batchId;
}

// ---- bulkPush() aggregation --------------------------------------------------

it('aggregates pushed/duplicates/ineligible across several batches (admin)', function () {
    bulkActingAs(1, 1);

    // Batch A: two eligible invoices -> pushed 2.
    $a = seedBatch(1, [[], []]);
    // Batch B: one eligible whose number already exists in purchase (duplicate),
    // plus one ineligible (needs_review).
    DB::table('purchase')->insert(['purchase_no' => 'DUP-1', 'created_at' => now()]);
    $b = seedBatch(1, [
        ['invoice_number' => 'DUP-1'],
        ['needs_review' => 1],
    ]);

    $request = Request::create('/', 'POST', ['batch_ids' => [$a, $b], 'shop_id' => 12]);
    $resp = (new InvoiceController())->bulkPush($request);
    $data = $resp->getData(true);

    expect($data['status'])->toBeTrue();
    $s = $data['summary'];
    expect($s['batches'])->toBe(2);
    expect($s['pushed'])->toBe(2);         // two clean invoices in batch A
    expect($s['duplicates'])->toBe(1);     // DUP-1 already in purchase
    expect($s['ineligible'])->toBe(1);     // needs_review invoice
    expect($s['not_found'])->toBe([]);
    expect($s['per_batch'])->toHaveCount(2);

    // Exactly the two clean rows were created (DUP-1 pre-seed is the 3rd).
    expect(DB::table('purchase')->count())->toBe(3);
});

it('enforces non-admin ownership: only the caller\'s own batches are pushed', function () {
    bulkActingAs(42, 0);
    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => 55]); // Perm gate passes

    $mine = seedBatch(42, [[]]);
    $theirs = seedBatch(99, [[]]);

    $request = Request::create('/', 'POST', ['batch_ids' => [$mine, $theirs], 'shop_id' => 5]);
    $resp = (new InvoiceController())->bulkPush($request);
    $data = $resp->getData(true);

    $s = $data['summary'];
    expect($s['batches'])->toBe(1);              // only the owned batch processed
    expect($s['pushed'])->toBe(1);
    expect($s['not_found'])->toBe([$theirs]);    // other user's batch reported, never pushed
    expect(DB::table('purchase')->count())->toBe(1);

    // The other user's invoice was never linked.
    $otherInv = DB::connection('invoices')->table('invoices')->where('batch_id', $theirs)->first();
    expect($otherInv->purchase_id)->toBeNull();
});

it('rejects when neither shop nor manager is chosen', function () {
    bulkActingAs(1, 1);
    $a = seedBatch(1, [[]]);

    $request = Request::create('/', 'POST', ['batch_ids' => [$a]]);
    $resp = (new InvoiceController())->bulkPush($request);

    expect($resp->getStatusCode())->toBe(422);
    expect(DB::table('purchase')->count())->toBe(0);
});

it('rejects when BOTH shop and manager are chosen', function () {
    bulkActingAs(1, 1);
    $a = seedBatch(1, [[]]);

    $request = Request::create('/', 'POST', ['batch_ids' => [$a], 'shop_id' => 5, 'manager_id' => 7]);
    $resp = (new InvoiceController())->bulkPush($request);

    expect($resp->getStatusCode())->toBe(422);
    expect(DB::table('purchase')->count())->toBe(0);
});

// ---- exportBatches() batch_ids[] filter --------------------------------------

it('exportBatches() honours batch_ids[] — exports only the selected batches', function () {
    bulkActingAs(1, 1);

    $b1 = seedBatch(1, [[]]);
    $b2 = seedBatch(1, [[]]);
    $b3 = seedBatch(1, [[]]);

    $request = Request::create('/', 'GET', ['batch_ids' => [$b1, $b3]]);
    $resp = (new InvoiceController())->exportBatches($request);

    // Run the streamed writer and capture the xlsx bytes.
    ob_start();
    $resp->sendContent();
    $bytes = ob_get_clean();

    $tmp = tempnam(sys_get_temp_dir(), 'bulkxlsx').'.xlsx';
    file_put_contents($tmp, $bytes);
    $loaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
    @unlink($tmp);

    // The export opens on the "الفواتير المستخرجة" sheet (invoices-first UX), so read
    // the batch-log sheet BY NAME. Column A = batch id, rows 1-2 are the brand title +
    // header; the last row is the totals row (empty id). Collect the numeric ids only.
    $ids = collect($loaded->getSheetByName('سجل عمليات الاستخراج')->toArray())
        ->map(fn ($r) => $r[0] ?? null)
        ->filter(fn ($v) => is_numeric($v))
        ->map(fn ($v) => (int) $v)
        ->values()
        ->all();

    sort($ids);
    expect($ids)->toBe([$b1, $b3]);       // b2 excluded
    expect($ids)->not->toContain($b2);
});

it('exportBatches() with no batch_ids[] exports all (with-filters) batches', function () {
    bulkActingAs(1, 1);

    $b1 = seedBatch(1, [[]]);
    $b2 = seedBatch(1, [[]]);

    $request = Request::create('/', 'GET', []);
    $resp = (new InvoiceController())->exportBatches($request);

    ob_start();
    $resp->sendContent();
    $bytes = ob_get_clean();

    $tmp = tempnam(sys_get_temp_dir(), 'bulkxlsx').'.xlsx';
    file_put_contents($tmp, $bytes);
    $loaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
    @unlink($tmp);

    $ids = collect($loaded->getSheetByName('سجل عمليات الاستخراج')->toArray())
        ->map(fn ($r) => $r[0] ?? null)
        ->filter(fn ($v) => is_numeric($v))
        ->map(fn ($v) => (int) $v)
        ->values()
        ->all();

    sort($ids);
    expect($ids)->toBe([$b1, $b2]);
});

// ---- bulkPush all=1 ("ترحيل الكل") -------------------------------------------

it('bulkPush all=1 posts every owned batch and skips already-posted (no duplicates)', function () {
    bulkActingAs(1, 1); // admin sees all

    $b1 = seedBatch(1, [[], []]);                  // 2 eligible invoices
    $b2 = seedBatch(1, [['purchase_id' => 777]]);  // 1 already posted -> skipped

    $request = Request::create('/', 'POST', ['all' => 1, 'shop_id' => 5]);
    $resp = json_decode((new InvoiceController())->bulkPush($request)->getContent(), true);

    expect($resp['status'])->toBeTrue();
    expect($resp['summary']['pushed'])->toBe(2);          // only b1's eligible pair
    expect($resp['summary']['already_mapped'])->toBe(1);  // b2's posted one, untouched
    expect(DB::table('purchase')->count())->toBe(2);      // no duplicate of the posted one
    expect($b2)->toBeGreaterThan(0);
});

it('bulkPush all=1 with no matching batches returns 422', function () {
    bulkActingAs(1, 1);
    // min_count filter that matches nothing (batches have 1-2 pages).
    $request = Request::create('/', 'POST', ['all' => 1, 'shop_id' => 5, 'min_count' => 999]);
    seedBatch(1, [[]]);
    $resp = (new InvoiceController())->bulkPush($request);
    expect($resp->getStatusCode())->toBe(422);
});
