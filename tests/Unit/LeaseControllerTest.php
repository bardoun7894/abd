<?php

// Boot the Laravel app (facades + container) but DO NOT use RefreshDatabase — the
// main DB is a local Docker MySQL and the `invoices` DB is an isolated sqlite file
// we wrap in a transaction per test so nothing is left behind.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\LeaseController;
use App\Jobs\ProcessLeaseBatch;
use App\Models\LeaseBatch;
use App\Models\LeaseContract;
use App\Models\LeaseExtraction;
use App\Models\LeasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Run controller tests that persist LeaseContract/LeasePayment against an
    // isolated SQLite :memory: DB so they do not require the local MySQL server.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('lease_contracts', function ($table) {
        $table->id();
        $table->unsignedBigInteger('shop_id')->nullable();
        $table->string('contract_no')->nullable();
        $table->string('tenant_name')->nullable();
        $table->string('tenant_id_no')->nullable();
        $table->string('landlord_name')->nullable();
        $table->string('landlord_id_no')->nullable();
        $table->string('property_no')->nullable();
        $table->string('unit')->nullable();
        $table->string('property_type')->nullable();
        $table->text('address')->nullable();
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->string('duration', 60)->nullable();
        $table->decimal('rent_value', 15, 2)->nullable();
        $table->integer('num_payments')->nullable();
        $table->decimal('payment_value', 15, 2)->nullable();
        $table->string('payment_frequency')->nullable();
        $table->decimal('deposit', 15, 2)->nullable();
        $table->string('payment_method')->nullable();
        $table->text('renewal_terms')->nullable();
        $table->text('cancellation_terms')->nullable();
        $table->text('increase_terms')->nullable();
        $table->text('extra_terms')->nullable();
        $table->string('attach_url')->nullable();
        $table->text('extracted_text')->nullable();
        $table->string('status')->nullable();
        $table->unsignedBigInteger('extraction_id')->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('lease_payments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('contract_id');
        $table->integer('payment_no')->nullable();
        $table->date('due_date')->nullable();
        $table->decimal('amount', 15, 2)->nullable();
        $table->string('status')->nullable();
        $table->date('paid_date')->nullable();
        $table->decimal('paid_amount', 15, 2)->nullable();
        $table->decimal('remaining', 15, 2)->nullable();
        $table->decimal('penalty', 15, 2)->nullable();
        $table->text('note')->nullable();
        $table->timestamps();
    });

    Schema::create('ai_audit_log', function ($table) {
        $table->id();
        $table->string('document_type')->nullable();
        $table->unsignedBigInteger('document_id')->nullable();
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->string('action')->nullable();
        $table->string('field')->nullable();
        $table->text('old_value')->nullable();
        $table->text('new_value')->nullable();
        $table->unsignedBigInteger('change_user')->nullable();
        $table->timestamp('change_at')->nullable();
        $table->text('note')->nullable();
        $table->timestamps();
    });

    DB::connection('invoices')->beginTransaction();
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
    DB::connection('invoices')->rollBack();
});

function actingAsLeaseAdmin(int $id = 1): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => 1]);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

function makeLeaseBatch(array $overrides = []): LeaseBatch
{
    return LeaseBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'test.pdf',
        'pdf_path' => 'uploads/leases/pdf/test.pdf',
        'status' => 'done',
        'total_pages' => 1,
        'processed_pages' => 1,
    ], $overrides));
}

function makeLeaseExtraction(int $batchId, array $overrides = []): LeaseExtraction
{
    return LeaseExtraction::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        'contract_no' => 'L-1',
        'tenant_name' => 'شركة الاختبار',
        'landlord_name' => 'محمد العتيبي',
        'status' => 'failed',
        'needs_review' => true,
    ], $overrides));
}

it('reprocess() re-dispatches ProcessLeaseBatch for the extraction batch and logs it', function () {
    actingAsLeaseAdmin();
    Queue::fake();
    $batch = makeLeaseBatch();
    $extraction = makeLeaseExtraction($batch->id);

    $controller = new LeaseController();
    $response = $controller->reprocess($extraction->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    Queue::assertPushed(ProcessLeaseBatch::class, fn ($job) => $job->batchId === $batch->id);
    expect(DB::table('ai_audit_log')->where('document_id', $extraction->id)->where('action', 'reprocess')->exists())->toBeTrue();
});

it('reprocess() returns a conflict when the batch is already processing', function () {
    actingAsLeaseAdmin();
    Queue::fake();
    $batch = makeLeaseBatch(['status' => 'processing']);
    $extraction = makeLeaseExtraction($batch->id, ['status' => 'failed']);

    $controller = new LeaseController();
    $response = $controller->reprocess($extraction->id);
    $payload = $response->getData(true);

    expect($response->status())->toBe(409);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('قيد المعالجة');
    Queue::assertNothingPushed();
});

it('approve() blocks when extraction needs_review and no force parameter', function () {
    actingAsLeaseAdmin();
    $batch = makeLeaseBatch();
    $extraction = makeLeaseExtraction($batch->id, [
        'status' => 'done',
        'needs_review' => true,
        'start_date' => '2026-01-01',
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => 1000.0,
        'payment_frequency' => 'monthly',
    ]);

    $controller = new LeaseController();
    $response = $controller->approve(Request::create('/dashboard/leases/'.$extraction->id.'/approve', 'POST'), $extraction->id);
    $payload = $response->getData(true);

    expect($response->status())->toBe(422);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('مراجعة');
    expect(LeaseContract::where('extraction_id', $extraction->id)->exists())->toBeFalse();
});

it('approve() succeeds when extraction needs_review and force parameter is provided', function () {
    actingAsLeaseAdmin();
    $batch = makeLeaseBatch();
    $extraction = makeLeaseExtraction($batch->id, [
        'status' => 'done',
        'needs_review' => true,
        'start_date' => '2020-01-01',
        'end_date' => '2025-01-01',
        'duration' => '5 years',
        'rent_value' => 40000.0,
        'num_payments' => 10,
        'payment_value' => 20000.0,
        'payment_frequency' => 'semi-annual',
    ]);

    $controller = new LeaseController();
    $request = Request::create('/dashboard/leases/'.$extraction->id.'/approve', 'POST', ['force' => '1']);
    $response = $controller->approve($request, $extraction->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    expect($payload)->toHaveKey('contract_id');
    $contract = LeaseContract::where('extraction_id', $extraction->id)->first();
    expect($contract)->not->toBeNull();
    expect(LeasePayment::where('contract_id', $contract->id)->count())->toBe(10);
});

it('approve() blocks when schedule dates fall outside the lease term', function () {
    actingAsLeaseAdmin();
    $batch = makeLeaseBatch();
    $extraction = makeLeaseExtraction($batch->id, [
        'status' => 'done',
        'needs_review' => false,
        'start_date' => '2026-01-01',
        'end_date' => '2025-01-01',
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => 1000.0,
        'payment_frequency' => 'monthly',
    ]);

    $controller = new LeaseController();
    $response = $controller->approve(Request::create('/dashboard/leases/'.$extraction->id.'/approve', 'POST'), $extraction->id);
    $payload = $response->getData(true);

    expect($response->status())->toBe(422);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('جدول الدفعات');
    expect(LeaseContract::where('extraction_id', $extraction->id)->exists())->toBeFalse();
});
