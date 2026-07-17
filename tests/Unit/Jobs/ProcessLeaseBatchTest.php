<?php

// Verifies ProcessLeaseBatch backoff configuration and exception handling.
uses(Tests\TestCase::class);

use App\Jobs\ProcessLeaseBatch;
use App\Models\LeaseBatch;
use App\Services\LeasePipeline;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
    Mockery::close();
});

function makeLeaseBatchForJob(): LeaseBatch
{
    return LeaseBatch::create([
        'user_id' => 1,
        'original_filename' => 'lease-job-test.pdf',
        'pdf_path' => 'uploads/leases/pdf/lease-job-test.pdf',
        'status' => 'processing',
    ]);
}

function makeLeaseBatchPdf(): string
{
    $path = public_path('uploads/leases/pdf/lease-job-test.pdf');
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, '%PDF-1.4 fake');

    return $path;
}

it('configures exponential backoff', function () {
    $job = new ProcessLeaseBatch(1);
    $ref = new ReflectionClass($job);

    expect($ref->getProperty('backoff')->getValue($job))->toBe([60, 300, 900]);
});

it('keeps a single try and rethrows pipeline exceptions', function () {
    $batch = makeLeaseBatchForJob();
    $pdfPath = makeLeaseBatchPdf();

    $pipeline = Mockery::mock(LeasePipeline::class);
    $pipeline->shouldReceive('run')
        ->once()
        ->andThrow(new RuntimeException('Lease AI overload'));

    $job = new ProcessLeaseBatch($batch->id);

    try {
        $job->handle($pipeline);
        $this->fail('Expected RuntimeException to be rethrown');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Lease AI overload');
    }

    $batch->refresh();
    expect($batch->status)->toBe('failed');
    expect($batch->error_message)->toBe('Lease AI overload');

    @unlink($pdfPath);
});
