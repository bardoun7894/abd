<?php

// Regression tests for InvoicePurchaseMapper and SupplierMatcher code-review findings:
//   - push() wraps purchase insert + line items + invoice mapping in a transaction
//     so a copyLineItems failure cannot leave an orphan purchase row.
//   - An optional $allowDuplicate flag lets the caller bypass the fuzzy duplicate
//     block; the controller surfaces it via confirm_duplicate.
//   - SupplierMatcher caches the suppliers master for the duration of the request.
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Models\InvoiceItem;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\InvoicePurchaseMapper;
use App\Services\SupplierMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $path = database_path('testing.sqlite');
    if (! is_file($path)) {
        touch($path);
    }
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => $path]);
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    // Minimal main-schema tables needed for the push path.
    if (! Schema::hasTable('purchase')) {
        Schema::create('purchase', function ($table) {
        $table->increments('purchase_id');
        $table->string('purchase_no')->unique();
        $table->date('purchase_dt')->nullable();
        $table->decimal('purchase_price', 15, 3)->nullable();
        $table->string('tax_number', 20)->nullable();
        $table->string('purchase_respon')->nullable();
        $table->unsignedBigInteger('shop_id')->nullable();
        $table->unsignedBigInteger('manager_id')->nullable();
        $table->string('purchasefile')->nullable();
        $table->text('note')->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->unsignedBigInteger('supplier_id')->nullable();
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
        $table->timestamps();
    });

    Schema::create('purchase_items', function ($table) {
        $table->increments('id');
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

    Schema::create('suppliers', function ($table) {
        $table->id();
        $table->string('name')->index();
        $table->string('tax_number', 20)->nullable()->index();
        $table->string('cr_number', 30)->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('ai_audit_log', function ($table) {
        $table->id();
        $table->string('document_type', 20)->index();
        $table->unsignedBigInteger('document_id')->nullable()->index();
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->string('action', 30)->index();
        $table->string('field')->nullable();
        $table->text('old_value')->nullable();
        $table->text('new_value')->nullable();
        $table->unsignedBigInteger('change_user')->nullable()->index();
        $table->dateTime('change_at')->nullable();
        $table->text('note')->nullable();
    });
});

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
    DB::connection('sqlite')->beginTransaction();
    SupplierMatcher::flushCache();
});

afterEach(function () {
    DB::connection('sqlite')->rollBack();
    DB::connection('invoices')->rollBack();
    SupplierMatcher::flushCache();
    Mockery::close();
});

function makeMapperBatch(array $overrides = []): InvoiceBatch
{
    return InvoiceBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'mapper-test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/mapper-test.pdf',
        'status' => 'done',
        'total_pages' => 1,
        'processed_pages' => 1,
        'grand_total' => 115,
    ], $overrides));
}

function makeMapperInvoice(int $batchId, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        'supplier_name' => 'شركة الاختبار',
        'supplier_tax_number' => '300000000000003',
        'invoice_number' => 'PUSH-1',
        'invoice_date' => '2026-07-01',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'status' => 'done',
        'needs_review' => false,
    ], $overrides));
}

// ---- Transaction rollback ----------------------------------------------------

it('rolls back the purchase row when line-item copy fails', function () {
    $batch = makeMapperBatch();
    $invoice = makeMapperInvoice($batch->id);

    // Simulate a schema mismatch that makes copyLineItems() fail.
    DB::connection('sqlite')->statement('DROP TABLE purchase_items');
    Schema::create('purchase_items', function ($table) {
        $table->increments('id');
        $table->unsignedBigInteger('purchase_id');
        // Deliberately missing columns required by copyLineItems().
    });

    $summary = (new InvoicePurchaseMapper())->push($batch, 5, null, 1);

    expect($summary['pushed'])->toBe(0);
    expect(DB::table('purchase')->where('purchase_no', 'PUSH-1')->exists())->toBeFalse();
    expect($invoice->fresh()->purchase_id)->toBeNull();
});

// ---- Duplicate override ------------------------------------------------------

it('pushes an invoice that would be blocked by fuzzy duplicate when override is true', function () {
    $batch1 = makeMapperBatch();
    $prior = makeMapperInvoice($batch1->id, [
        'invoice_number' => 'ABC123',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
        'invoice_date' => '2026-07-01',
    ]);

    $batch2 = makeMapperBatch();
    $later = makeMapperInvoice($batch2->id, [
        'invoice_number' => 'ABC 123',
        'supplier_tax_number' => '300000000000003',
        'total_incl_vat' => 115,
        'invoice_date' => '2026-07-01',
    ]);

    // Without override the fuzzy duplicate block stops the push.
    $summary = (new InvoicePurchaseMapper())->push($batch2, 5, null, 1);
    expect($summary['pushed'])->toBe(0);
    expect($summary['fuzzy_duplicates'])->toHaveCount(1);

    // With override the invoice is pushed.
    $summary = (new InvoicePurchaseMapper())->push($batch2, 5, null, 1, true);
    expect($summary['pushed'])->toBe(1);
    expect($later->fresh()->purchase_id)->not->toBeNull();

    // The override was audit-logged.
    expect(DB::table('ai_audit_log')
        ->where('document_id', $later->id)
        ->where('action', AuditLogger::DUP_OVERRIDE)
        ->exists())->toBeTrue();
});

it('does not use the duplicate override for exact purchase_no collisions', function () {
    DB::table('purchase')->insert([
        'purchase_no' => 'EXACT-1',
        'purchase_dt' => '2026-07-01',
        'purchase_price' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $batch = makeMapperBatch();
    $invoice = makeMapperInvoice($batch->id, ['invoice_number' => 'EXACT-1']);

    $summary = (new InvoicePurchaseMapper())->push($batch, 5, null, 1, true);

    expect($summary['pushed'])->toBe(0);
    expect($summary['duplicates'])->toContain('EXACT-1');
    expect($invoice->fresh()->purchase_id)->toBeNull();
});

// ---- SupplierMatcher caching -------------------------------------------------

it('caches the suppliers master between match calls', function () {
    Supplier::create(['name' => 'مورد ألف', 'tax_number' => '100000000000001']);
    Supplier::create(['name' => 'مورد باء', 'tax_number' => '100000000000002']);

    $matcher = new SupplierMatcher();

    // First call loads and caches the list.
    $matcher->match('100000000000001', 'مورد ألف');

    DB::connection('sqlite')->enableQueryLog();
    $result = $matcher->match(null, 'مورد باء');
    $queries = DB::connection('sqlite')->getQueryLog();

    expect($result['match'])->not->toBeNull();
    expect($result['match']->name)->toBe('مورد باء');
    // No SELECT against suppliers for the second call (name matching uses cache).
    $supplierSelects = collect($queries)->filter(fn ($q) => str_contains($q['query'] ?? '', 'from "suppliers"'))->count();
    expect($supplierSelects)->toBe(0);
});

it('still resolves by tax_number with a direct query', function () {
    $supplier = Supplier::create(['name' => 'مورد جيم', 'tax_number' => '100000000000003']);

    $matcher = new SupplierMatcher();
    $result = $matcher->match('100000000000003', null);

    expect($result['match'])->not->toBeNull();
    expect($result['match']->id)->toBe($supplier->id);
    expect($result['reason'])->toBe('tax_number');
});

// ---- Controller wiring -------------------------------------------------------

it('passes the confirm_duplicate flag from the controller to the mapper', function () {
    $batch = makeMapperBatch();
    $invoice = makeMapperInvoice($batch->id);

    $mapper = Mockery::mock(InvoicePurchaseMapper::class);
    $mapper->shouldReceive('push')
        ->onice()
        ->with(Mockery::on(fn ($b) => $b->id === $batch->id), 5, null, 1, true)
        ->andReturn(['pushed' => 1]);

    $controller = new \App\Http\Controllers\Dashboard\InvoiceController();
    $request = new \Illuminate\Http\Request(['shop_id' => 5, 'confirm_duplicate' => '1']);

    // The controller uses Auth::id() — mock Auth so the controller can resolve it.
    \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn(1);
    \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn((object) ['id' => 1, 'emp_job' => 1]);
    \Illuminate\Support\Facades\Auth::shouldReceive('check')->andReturn(true);

    // Mock permission gate.
    $perm = Mockery::mock('overload:Perm');
    $perm->shouldReceive('get_function_access')->with(55)->andReturn(true);

    app()->instance(InvoicePurchaseMapper::class, $mapper);

    // findOwned is private and looks up the batch; use reflection to invoke the
    // public method with a real batch while bypassing ownership checks.
    $method = new ReflectionMethod($controller, 'pushToPurchase');
    $method->setAccessible(true);
    $response = $method->invoke($controller, $request, $batch->id);

    expect($response->getData(true)['status'])->toBeTrue();
});
