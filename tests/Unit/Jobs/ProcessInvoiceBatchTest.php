<?php

// Verifies ProcessInvoiceBatch retry/backoff configuration and exception handling.
uses(Tests\TestCase::class);

use App\Jobs\ProcessInvoiceBatch;
use App\Models\InvoiceBatch;
use App\Services\InvoicePipeline;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
    Mockery::close();
});

function makeInvoiceBatchForJob(): InvoiceBatch
{
    return InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'job-test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/job-test.pdf',
        'status' => 'processing',
    ]);
}

function makeInvoiceBatchPdf(): string
{
    $path = public_path('uploads/invoices/pdf/job-test.pdf');
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, '%PDF-1.4 fake');

    return $path;
}

it('configures three tries and exponential backoff', function () {
    $job = new ProcessInvoiceBatch(1);
    $ref = new ReflectionClass($job);

    expect($ref->getProperty('tries')->getValue($job))->toBe(3);
    expect($ref->getProperty('backoff')->getValue($job))->toBe([60, 300, 900]);
});

it('marks the batch failed and rethrows pipeline exceptions', function () {
    $batch = makeInvoiceBatchForJob();
    $pdfPath = makeInvoiceBatchPdf();

    $pipeline = Mockery::mock(InvoicePipeline::class);
    $pipeline->shouldReceive('run')
        ->once()
        ->andThrow(new RuntimeException('AI overload'));

    $job = new ProcessInvoiceBatch($batch->id);

    try {
        $job->handle($pipeline);
        $this->fail('Expected RuntimeException to be rethrown');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('AI overload');
    }

    $batch->refresh();
    expect($batch->status)->toBe('failed');
    expect($batch->error_message)->toBe('AI overload');

    @unlink($pdfPath);
});
