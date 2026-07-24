<?php

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 024 Feature 1 — InvoiceController::pushInvoices(): per-invoice checkbox
 * posting via POST /invoices/push-invoices (invoice_ids[] + shop_id XOR
 * manager_id), returning the SAME combined summary shape bulkPush() already
 * returns. Same two-independent-:memory:-sqlite-DB pattern as
 * InvoiceBulkPushTest.
 */
beforeEach(function () {
    Invoice::flushEventListeners();

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    Schema::create('purchase', function ($t) {
        $t->id('purchase_id');
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

    Schema::create('ai_audit_log', function ($t) {
        $t->id();
        $t->string('document_type', 20)->index();
        $t->unsignedBigInteger('document_id')->nullable()->index();
        $t->unsignedBigInteger('batch_id')->nullable();
        $t->string('action', 30)->index();
        $t->string('field')->nullable();
        $t->text('old_value')->nullable();
        $t->text('new_value')->nullable();
        $t->unsignedBigInteger('change_user')->nullable()->index();
        $t->dateTime('change_at')->nullable();
        $t->text('note')->nullable();
    });

    Schema::create('shop', function ($t) {
        $t->id('shop_id');
        $t->string('shop_name')->nullable();
    });

    // Perm::get_function_access(55) for a NON-admin reads this table.
    Schema::create('permission', function ($t) {
        $t->id();
        $t->unsignedBigInteger('emp_id')->nullable();
        $t->unsignedBigInteger('function_id')->nullable();
    });

    require_once base_path('database/migrations/invoices/2026_06_16_000001_create_invoice_batches_table.php');
    require_once base_path('database/migrations/invoices/2026_06_16_000002_create_invoices_table.php');
    require_once base_path('database/migrations/invoices/2026_06_17_000005_add_purchase_mapping_to_invoices.php');
    require_once base_path('database/migrations/invoices/2026_06_23_000011_create_invoice_items_table.php');
    require_once base_path('database/migrations/invoices/2026_07_24_000016_add_transfer_fields_to_invoices.php');

    (new CreateInvoiceBatchesTable())->up();
    (new CreateInvoicesTable())->up();
    (new AddPurchaseMappingToInvoices())->up();
    (new CreateInvoiceItemsTable())->up();
    (new AddTransferFieldsToInvoices())->up();

    DB::table('shop')->insert(['shop_id' => 20, 'shop_name' => 'محل تجريبي']);
});

afterEach(function () {
    Invoice::flushEventListeners();
    \App\Models\InvoiceItem::flushEventListeners();
    Mockery::close();
});

function pushInvActingAs(int $id, int $empJob): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => $empJob, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

/** Seed a batch (isolated conn) with one invoice per row-spec; returns [batchId, invoiceIds[]]. */
function seedPushInvoicesBatch(int $userId, array $invoiceOverridesList): array
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => $userId,
        'status' => 'done',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $ids = [];
    $page = 1;
    foreach ($invoiceOverridesList as $ov) {
        $ids[] = DB::connection('invoices')->table('invoices')->insertGetId(array_merge([
            'batch_id' => $batchId,
            'page_number' => $page++,
            'invoice_number' => 'PI-'.uniqid(),
            'invoice_date' => '2026-07-01',
            'total_incl_vat' => 100,
            'status' => 'done',
            'needs_review' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $ov));
    }

    return [$batchId, $ids];
}

it('pushes exactly the checked invoice_ids, sets denormalized transfer columns + writes per-invoice TRANSFER audit rows', function () {
    pushInvActingAs(1, 1);
    [$batchId, $ids] = seedPushInvoicesBatch(1, [[], [], [], []]);
    [$id1, $id2, $id3, $id4] = $ids;

    $request = Request::create('/', 'POST', ['invoice_ids' => [$id1, $id2], 'shop_id' => 20]);
    $response = (new InvoiceController())->pushInvoices($request);
    $data = $response->getData(true);

    expect($data['status'])->toBeTrue();
    expect($data['summary']['pushed'])->toBe(2);

    expect(Invoice::find($id1)->purchase_id)->not->toBeNull();
    expect(Invoice::find($id2)->purchase_id)->not->toBeNull();
    expect(Invoice::find($id3)->purchase_id)->toBeNull();
    expect(Invoice::find($id4)->purchase_id)->toBeNull();

    foreach ([$id1, $id2] as $id) {
        $inv = Invoice::find($id);
        expect($inv->transferred_at)->not->toBeNull();
        expect($inv->transferred_branch_label)->not->toBeNull();
        expect(DB::table('ai_audit_log')->where('document_id', $id)->where('action', 'transfer')->exists())->toBeTrue();
    }
});

it('rejects when neither shop nor manager is chosen', function () {
    pushInvActingAs(1, 1);
    [$batchId, $ids] = seedPushInvoicesBatch(1, [[]]);

    $request = Request::create('/', 'POST', ['invoice_ids' => $ids]);
    $response = (new InvoiceController())->pushInvoices($request);

    expect($response->getStatusCode())->toBe(422);
});

it('rejects when BOTH shop and manager are chosen', function () {
    pushInvActingAs(1, 1);
    [$batchId, $ids] = seedPushInvoicesBatch(1, [[]]);

    $request = Request::create('/', 'POST', ['invoice_ids' => $ids, 'shop_id' => 20, 'manager_id' => 7]);
    $response = (new InvoiceController())->pushInvoices($request);

    expect($response->getStatusCode())->toBe(422);
});

it('enforces non-admin ownership: invoices from another user\'s batch are reported not_found, never pushed', function () {
    pushInvActingAs(42, 0);
    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => 55]);

    [, $mineIds] = seedPushInvoicesBatch(42, [[]]);
    [, $theirIds] = seedPushInvoicesBatch(99, [[]]);

    $request = Request::create('/', 'POST', ['invoice_ids' => array_merge($mineIds, $theirIds), 'shop_id' => 20]);
    $response = (new InvoiceController())->pushInvoices($request);
    $data = $response->getData(true);

    expect($data['summary']['pushed'])->toBe(1);
    expect($data['summary']['not_found'])->toBe($theirIds);
    expect(Invoice::find($theirIds[0])->purchase_id)->toBeNull();
});

it('blocks a non-admin user with no push permission (403)', function () {
    pushInvActingAs(42, 0);
    [, $ids] = seedPushInvoicesBatch(42, [[]]);

    $request = Request::create('/', 'POST', ['invoice_ids' => $ids, 'shop_id' => 20]);
    $response = (new InvoiceController())->pushInvoices($request);

    expect($response->getStatusCode())->toBe(403);
});
