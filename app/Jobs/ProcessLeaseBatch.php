<?php

namespace App\Jobs;

use App\Models\LeaseBatch;
use App\Services\LeasePipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs the shared LeasePipeline for one uploaded lease batch in the background:
 * rasterize/split → one AI read per page → persist, updating processed_pages as it
 * goes so the UI can poll progress. Mirrors ProcessInvoiceBatch.
 *
 * With QUEUE_CONNECTION=sync this runs inline (fine for small PDFs). For larger
 * documents set QUEUE_CONNECTION=invoices and run a (cron) `queue:work invoices`.
 */
class ProcessLeaseBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;

    public $tries = 3;

    public $backoff = [60, 300, 900];

    public function __construct(
        public int $batchId,
        public ?string $model = null,
    ) {}

    public function handle(LeasePipeline $pipeline): void
    {
        $batch = LeaseBatch::find($this->batchId);
        if (! $batch) {
            return;
        }

        Log::info('Lease batch processing started', [
            'batch_id' => $this->batchId,
            'model' => $this->model,
        ]);

        $abs = public_path($batch->pdf_path);
        if (! is_file($abs)) {
            Log::error('Lease batch PDF not found', [
                'batch_id' => $this->batchId,
                'pdf_path' => $batch->pdf_path,
            ]);
            $batch->update(['status' => 'failed', 'error_message' => 'PDF not found: '.$batch->pdf_path]);

            return;
        }

        try {
            $deadline = microtime(true) + $this->timeout;
            $pipeline->run($batch, $abs, $this->model, function ($done, $total) use ($batch) {
                $batch->forceFill(['processed_pages' => $done, 'total_pages' => $total])->save();
            }, $deadline);
            Log::info('Lease batch processing completed', ['batch_id' => $this->batchId]);
        } catch (\Throwable $e) {
            Log::error('Lease batch processing failed', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
            ]);
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Lease batch job marked as failed', [
            'batch_id' => $this->batchId,
            'error' => $e->getMessage(),
        ]);
        if ($batch = LeaseBatch::find($this->batchId)) {
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }
}
