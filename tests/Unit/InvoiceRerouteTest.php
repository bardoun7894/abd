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
 * Spec 024 Feature 1 — InvoiceController::rerouteInvoice(): re-route an
 * ALREADY-posted invoice between branches with a special permission. Must be
 * an in-place UPDATE of the `purchase` row (never reverse+repush), so it
 * creates NO new cashbox_ledger row, and it must write an ai_audit_log
 * TRANSFER row. Same two-independent-:memory:-sqlite-DB pattern as
 * InvoiceBulkPushTest / CashboxControllerTest.
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

    // Real production PK convention (see InvoiceController::maybeAutoPost, which
    // already queries DB::table('purchase')->where('purchase_id', ...)).
    Schema::create('purchase', function ($t) {
        $t->id('purchase_id');
        $t->string('purchase_no')->nullable();
        $t->unsignedBigInteger('shop_id')->nullable();
        $t->unsignedBigInteger('manager_id')->nullable();
        $t->timestamp('created_at')->nullable();
    });

    Schema::create('cashbox_ledger', function ($t) {
        $t->id('entry_id');
        $t->unsignedBigInteger('receipt_id')->nullable();
        $t->string('source_type', 20);
        $t->unsignedBigInteger('source_id');
        $t->string('direction', 3);
        $t->decimal('amount', 14, 2);
        $t->dateTime('change_at')->nullable();
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

    Schema::create('manager', function ($t) {
        $t->id('manager_id');
        $t->string('manager_name')->nullable();
    });

    // Perm::get_function_access(<reroute id>) for a NON-admin reads this table.
    Schema::create('permission', function ($t) {
        $t->id();
        $t->unsignedBigInteger('emp_id')->nullable();
        $t->unsignedBigInteger('function_id')->nullable();
    });

    require_once base_path('database/migrations/invoices/2026_06_16_000001_create_invoice_batches_table.php');
    require_once base_path('database/migrations/invoices/2026_06_16_000002_create_invoices_table.php');
    require_once base_path('database/migrations/invoices/2026_06_17_000005_add_purchase_mapping_to_invoices.php');
    require_once base_path('database/migrations/invoices/2026_07_24_000016_add_transfer_fields_to_invoices.php');

    (new CreateInvoiceBatchesTable())->up();
    (new CreateInvoicesTable())->up();
    (new AddPurchaseMappingToInvoices())->up();
    (new AddTransferFieldsToInvoices())->up();
});

afterEach(function () {
    Invoice::flushEventListeners();
    Mockery::close();
});

function rerouteActingAs(int $id, int $empJob): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => $empJob, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

/** Seed one owned+posted invoice + its purchase row; returns [invoiceId, purchaseId]. */
function seedPostedInvoice(int $userId, int $originalShopId): array
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => $userId,
        'status' => 'done',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchase')->insertGetId([
        'purchase_no' => 'PN-'.uniqid(),
        'shop_id' => $originalShopId,
        'manager_id' => null,
        'created_at' => now(),
    ]);

    $invId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId,
        'page_number' => 1,
        'invoice_number' => 'RR-'.uniqid(),
        'invoice_date' => '2026-07-01',
        'total_incl_vat' => 100,
        'status' => 'done',
        'needs_review' => 0,
        'purchase_id' => $purchaseId,
        'mapped_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$invId, $purchaseId];
}

it('reroutes a posted invoice: updates purchase.shop_id in place, creates no cashbox_ledger row, writes a TRANSFER audit row', function () {
    rerouteActingAs(1, 1); // admin bypasses the special permission
    [$invId, $purchaseId] = seedPostedInvoice(1, 10);

    $request = Request::create('/', 'POST', ['shop_id' => 20]);
    $response = (new InvoiceController())->rerouteInvoice($request, $invId);
    $data = $response->getData(true);

    expect($data['status'])->toBeTrue();
    expect((int) DB::table('purchase')->where('purchase_id', $purchaseId)->value('shop_id'))->toBe(20);
    expect(DB::table('cashbox_ledger')->count())->toBe(0);
    expect(DB::table('ai_audit_log')->where('document_id', $invId)->where('action', 'transfer')->exists())->toBeTrue();

    $inv = Invoice::find($invId);
    expect($inv->transferred_at)->not->toBeNull();
    expect($inv->transferred_by)->toBe(1);
});

it('blocks reroute for a non-admin user with no special-permission row (403), purchase unchanged', function () {
    rerouteActingAs(42, 0);
    [$invId, $purchaseId] = seedPostedInvoice(42, 10);

    $request = Request::create('/', 'POST', ['shop_id' => 20]);
    $response = (new InvoiceController())->rerouteInvoice($request, $invId);

    expect($response->getStatusCode())->toBe(403);
    expect((int) DB::table('purchase')->where('purchase_id', $purchaseId)->value('shop_id'))->toBe(10);
});

it('allows a non-admin user WITH the reroute special-permission row to reroute', function () {
    rerouteActingAs(42, 0);
    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => InvoiceController::REROUTE_FUNCTION_ID]);
    [$invId, $purchaseId] = seedPostedInvoice(42, 10);

    $request = Request::create('/', 'POST', ['shop_id' => 20]);
    $response = (new InvoiceController())->rerouteInvoice($request, $invId);

    expect($response->getStatusCode())->toBe(200);
    expect((int) DB::table('purchase')->where('purchase_id', $purchaseId)->value('shop_id'))->toBe(20);
});

it('rejects rerouting an invoice that is not yet posted (422)', function () {
    rerouteActingAs(1, 1);

    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => 1, 'status' => 'done', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $invId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 1, 'invoice_number' => 'NP-1',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $request = Request::create('/', 'POST', ['shop_id' => 20]);
    $response = (new InvoiceController())->rerouteInvoice($request, $invId);

    expect($response->getStatusCode())->toBe(422);
});
