<?php

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 024 Feature 1 — guard maybeAutoPost(): only auto-inherit a sibling's
 * shop/manager when the batch's ALREADY-posted invoices all point at the SAME
 * (shop_id, manager_id) target. If they span >1 distinct target, never guess
 * — return [false, null] so the user must post the fixed invoice explicitly.
 * Exercised indirectly via correct() (Spec 020's public entry point into the
 * private maybeAutoPost()), same controller-direct-call pattern as
 * InvoiceReviewControllerTest / CashboxControllerTest.
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
});

afterEach(function () {
    Invoice::flushEventListeners();
    \App\Models\InvoiceItem::flushEventListeners();
    Mockery::close();
});

function guardActingAsAdmin(int $id = 1): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => 1, 'emp_name' => 'Admin']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

it('does not auto-post when the batch already has posted siblings pointing at TWO distinct targets', function () {
    guardActingAsAdmin();

    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => 1, 'status' => 'done', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $p1 = DB::table('purchase')->insertGetId(['purchase_no' => 'P1', 'shop_id' => 10, 'created_at' => now()]);
    $p2 = DB::table('purchase')->insertGetId(['purchase_no' => 'P2', 'shop_id' => 20, 'created_at' => now()]);

    // Two posted siblings, different shop_id -> spans 2 distinct targets.
    DB::connection('invoices')->table('invoices')->insert([
        'batch_id' => $batchId, 'page_number' => 1, 'invoice_number' => 'A1',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'purchase_id' => $p1, 'mapped_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::connection('invoices')->table('invoices')->insert([
        'batch_id' => $batchId, 'page_number' => 2, 'invoice_number' => 'A2',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'purchase_id' => $p2, 'mapped_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // A third, blocked invoice missing invoice_number -> ineligible until corrected.
    $blockedId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 3, 'invoice_number' => null,
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // Fixing invoice_number makes it fully eligible and flips needs_review off,
    // which is exactly what should trigger maybeAutoPost() inside correct().
    $request = Request::create('/', 'POST', ['field' => 'invoice_number', 'value' => 'A3-FIXED']);
    $response = (new InvoiceController())->correct($request, $blockedId);
    $data = $response->getData(true);

    expect($data['auto_posted'])->toBeFalse();
    expect(Invoice::find($blockedId)->purchase_id)->toBeNull();
});

it('still auto-posts when every posted sibling shares the SAME single target (no regression)', function () {
    guardActingAsAdmin();

    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => 1, 'status' => 'done', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $p1 = DB::table('purchase')->insertGetId(['purchase_no' => 'P1', 'shop_id' => 10, 'created_at' => now()]);

    DB::connection('invoices')->table('invoices')->insert([
        'batch_id' => $batchId, 'page_number' => 1, 'invoice_number' => 'B1',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'purchase_id' => $p1, 'mapped_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $blockedId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 2, 'invoice_number' => null,
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $request = Request::create('/', 'POST', ['field' => 'invoice_number', 'value' => 'B2-FIXED']);
    $response = (new InvoiceController())->correct($request, $blockedId);
    $data = $response->getData(true);

    expect($data['auto_posted'])->toBeTrue();
    expect(Invoice::find($blockedId)->purchase_id)->not->toBeNull();
});
