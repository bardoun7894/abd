<?php

// Boot the Laravel app (facades + container) but DO NOT use RefreshDatabase — the
// main DB is a local Docker MySQL and the `invoices` DB is an isolated sqlite file
// we wrap in a transaction per test so nothing is left behind.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\LeaseController;
use App\Jobs\ProcessLeaseBatch;
use App\Models\LeaseBatch;
use App\Models\LeaseExtraction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
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
