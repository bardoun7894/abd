<?php

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoicePurchaseMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 024 Feature 1 — InvoicePurchaseMapper::push() gains an optional
 * `?array $onlyInvoiceIds` (last param) so a subset of a batch can be posted
 * without touching the rest. Same two-independent-:memory:-sqlite-DB pattern
 * as InvoicePurchaseMapperPushTest.
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

    Schema::create('purchase', function ($table) {
        $table->id();
        $table->string('purchase_no')->nullable();
        $table->decimal('purchase_price', 15, 3)->nullable();
        $table->date('purchase_dt')->nullable();
        $table->string('tax_number')->nullable();
        $table->string('purchase_respon')->nullable();
        $table->unsignedBigInteger('shop_id')->nullable();
        $table->unsignedBigInteger('manager_id')->nullable();
        $table->string('purchasefile')->nullable();
        $table->text('note')->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->decimal('amount_before_vat', 15, 3)->nullable();
        $table->decimal('vat_amount', 15, 3)->nullable();
        $table->decimal('vat_rate', 6, 3)->nullable();
        $table->decimal('discount_total', 15, 3)->nullable();
        $table->string('currency', 10)->nullable();
        $table->string('invoice_type', 20)->nullable();
        $table->string('payment_method', 60)->nullable();
        $table->string('commercial_registration', 30)->nullable();
        $table->date('due_date')->nullable();
        $table->string('source', 20)->nullable();
        $table->unsignedBigInteger('supplier_id')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('purchase_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('purchase_id')->index();
        $table->unsignedInteger('line_no')->default(1);
        $table->string('name')->nullable();
        $table->decimal('quantity', 14, 3)->nullable();
        $table->string('unit', 40)->nullable();
        $table->decimal('unit_price', 14, 2)->nullable();
        $table->decimal('line_total', 14, 2)->nullable();
        $table->decimal('vat_rate', 6, 3)->nullable();
        $table->decimal('vat_amount', 14, 2)->nullable();
        $table->timestamps();
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
});

/** Seed a batch (isolated conn) with one invoice per row-spec; returns [batchId, invoiceIds[]]. */
function seedSubsetBatch(array $invoiceOverridesList): array
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
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
            'supplier_name' => null,
            'supplier_tax_number' => null,
            'invoice_number' => 'SUBSET-'.uniqid(),
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

it('posts only the requested subset of invoices, leaving the rest of the batch untouched', function () {
    [$batchId, $ids] = seedSubsetBatch([[], [], [], []]);
    $batch = InvoiceBatch::find($batchId);

    [$id1, $id2, $id3, $id4] = $ids;

    $summary = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3, [], [$id1, $id2]);

    expect($summary['pushed'])->toBe(2);

    expect(Invoice::find($id1)->purchase_id)->not->toBeNull();
    expect(Invoice::find($id2)->purchase_id)->not->toBeNull();
    expect(Invoice::find($id3)->purchase_id)->toBeNull();
    expect(Invoice::find($id4)->purchase_id)->toBeNull();

    expect(DB::table('purchase')->count())->toBe(2);
});

it('with no onlyInvoiceIds (existing callers) still pushes the whole eligible batch — unchanged behaviour', function () {
    [$batchId, $ids] = seedSubsetBatch([[], []]);
    $batch = InvoiceBatch::find($batchId);

    $summary = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);

    expect($summary['pushed'])->toBe(2);
    foreach ($ids as $id) {
        expect(Invoice::find($id)->purchase_id)->not->toBeNull();
    }
});
