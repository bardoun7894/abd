<?php

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoicePurchaseMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 009 bundle B-c — cross-connection atomicity hardening. push() writes a
 * `purchase` row on the MAIN connection inside a DB::transaction, then (after
 * commit) writes the link (purchase_id/mapped_at) on the isolated `invoices`
 * (sqlite) connection. These two writes are NOT one atomic unit — they can't
 * be, they're different databases — so this file exercises the real cross-
 * connection push() against two independent sqlite :memory: DBs (mirrors
 * CashboxMigrationsTest's pattern) rather than mocking, since the exact thing
 * under test is the boundary between the two commits.
 */
beforeEach(function () {
    Invoice::flushEventListeners();

    // Main (mysql in prod) connection -> sqlite :memory: for this test run.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    // Isolated invoices connection -> its OWN sqlite :memory: DB, exactly like
    // production keeps it a separate physical database from the main schema.
    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    // Minimal `purchase` (legacy table, no create-migration exists in this repo —
    // see extend_purchase_for_ai.php which only ALTERs it) + `purchase_items`.
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

    // Real migration files -> real `invoices`/`invoice_batches`/`invoice_items`
    // schema (including the purchase_id/mapped_at link columns) on the isolated
    // sqlite connection, same as CashboxMigrationsTest's approach.
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
});

/** Seed a one-invoice batch on the isolated `invoices` sqlite connection. */
function seedPushBatch(array $invoiceOverrides = []): array
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'status' => 'done',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $invId = DB::connection('invoices')->table('invoices')->insertGetId(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        // supplier fields intentionally blank: resolveSupplierId() short-circuits
        // to null with no name/tax, avoiding a `suppliers` table dependency here.
        'supplier_name' => null,
        'supplier_tax_number' => null,
        'invoice_number' => 'PUSH-TEST-'.uniqid(),
        'invoice_date' => '2026-07-01',
        'total_incl_vat' => 100,
        'status' => 'done',
        'needs_review' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ], $invoiceOverrides));

    return [$batchId, $invId];
}

it('happy path: pushes an eligible invoice, sets purchase_id + mapped_at, counts pushed', function () {
    [$batchId, $invId] = seedPushBatch();
    $batch = InvoiceBatch::find($batchId);

    $summary = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);

    expect($summary['pushed'])->toBe(1);
    expect($summary['pushed_ids'])->toHaveCount(1);
    expect($summary['link_errors'])->toBe([]);

    $inv = Invoice::find($invId);
    expect($inv->purchase_id)->not->toBeNull();
    expect($inv->mapped_at)->not->toBeNull();
    expect(DB::table('purchase')->count())->toBe(1);
});

it('post-commit link write failure: purchase stays committed, pushed++, link_errors records it, invoice stays unlinked', function () {
    [$batchId, $invId] = seedPushBatch();
    $batch = InvoiceBatch::find($batchId);

    // Simulate the sqlite link write failing AFTER the mysql purchase commit —
    // exactly the divergence window B-c closes. The purchase insert itself
    // (DB::table()->insertGetId on the main connection) is untouched by this;
    // only the Eloquent ->save() that records purchase_id/mapped_at is hooked.
    Invoice::saving(function ($model) {
        if ($model->isDirty('purchase_id')) {
            throw new \RuntimeException('simulated sqlite link failure');
        }
    });

    $summary = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);

    // (a) the purchase row + purchase_items table exist in mysql (line items were
    // empty here, but the purchase itself genuinely committed).
    expect(DB::table('purchase')->count())->toBe(1);

    // (b) pushed++ still counted — the purchase genuinely committed.
    expect($summary['pushed'])->toBe(1);
    expect($summary['pushed_ids'])->toHaveCount(1);

    // (c) link_errors has one entry with the purchase_id.
    expect($summary['link_errors'])->toHaveCount(1);
    expect($summary['link_errors'][0]['purchase_id'])->toBe($summary['pushed_ids'][0]);
    expect($summary['link_errors'][0]['invoice_id'])->toBe($invId);

    // (d) invoice.purchase_id remains NULL — the link write never completed.
    Invoice::flushEventListeners(); // stop hooking so this read-check is clean
    $inv = Invoice::find($invId);
    expect($inv->purchase_id)->toBeNull();
});

it('re-push after a link failure is classified as a duplicate, never double-creates the purchase', function () {
    [$batchId, $invId] = seedPushBatch();
    $batch = InvoiceBatch::find($batchId);

    Invoice::saving(function ($model) {
        if ($model->isDirty('purchase_id')) {
            throw new \RuntimeException('simulated sqlite link failure');
        }
    });

    $first = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);
    expect($first['pushed'])->toBe(1);
    expect($first['link_errors'])->toHaveCount(1);

    // Invoice is still unlinked (purchase_id null on the isolated side) so it is
    // still isEligible() on re-push; the purchase_no UNIQUE-in-practice exists()
    // guard must catch it as a duplicate instead of inserting a second row.
    $second = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);

    expect($second['pushed'])->toBe(0);
    expect($second['duplicates'])->toHaveCount(1);
    expect(DB::table('purchase')->count())->toBe(1); // never double-created
});

it('mysql rollback (copyLineItems throws) keeps the invoice unlinked and leaves no purchase row', function () {
    [$batchId, $invId] = seedPushBatch();
    $batch = InvoiceBatch::find($batchId);

    // Seed one line item so copyLineItems()'s ->get() actually retrieves a row
    // (the retrieved() hook below only fires when a model is loaded).
    DB::connection('invoices')->table('invoice_items')->insert([
        'invoice_id' => $invId,
        'batch_id' => $batchId,
        'line_no' => 1,
        'name' => 'بند تجريبي',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Force copyLineItems() to throw INSIDE the mysql DB::transaction() closure —
    // a plain application-level \Throwable (not a DB constraint error), so this
    // is unambiguous evidence copyLineItems() itself failing rolls back the
    // whole transaction (not a duplicate-key/constraint side effect).
    \App\Models\InvoiceItem::retrieved(function () {
        throw new \RuntimeException('simulated copyLineItems failure');
    });

    $summary = app(InvoicePurchaseMapper::class)->push($batch, 12, null, 3);

    expect($summary['pushed'])->toBe(0);
    expect($summary['errors'])->toHaveCount(1);
    expect(DB::table('purchase')->count())->toBe(0); // rolled back, no orphan row

    \App\Models\InvoiceItem::flushEventListeners();
    $inv = Invoice::find($invId);
    expect($inv->purchase_id)->toBeNull(); // re-postable, no phantom-posted divergence
});
