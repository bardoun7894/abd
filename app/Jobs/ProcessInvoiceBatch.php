<?php

namespace App\Jobs;

use App\Models\InvoiceBatch;
use App\Services\InvoicePipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs the shared InvoicePipeline for one uploaded batch in the background:
 * free count → split → one AI read per page → group → grand total, updating
 * processed_pages as it goes so the UI can poll progress.
 *
 * With QUEUE_CONNECTION=sync this runs inline (fine for small PDFs). For 100+
 * invoices set QUEUE_CONNECTION=invoices and run a (cron) `queue:work invoices`.
 */
class ProcessInvoiceBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;

    public $tries = 3;

    public $backoff = [60, 300, 900];

    public function __construct(
        public int $batchId,
        public ?string $model = null,
        public string $mode = 'whole',
    ) {}

    public function handle(InvoicePipeline $pipeline): void
    {
        $batch = InvoiceBatch::find($this->batchId);
        if (! $batch) {
            return;
        }

        Log::info('Invoice batch processing started', [
            'batch_id' => $this->batchId,
            'model' => $this->model,
            'mode' => $this->mode,
        ]);

        $abs = public_path($batch->pdf_path);
        if (! is_file($abs)) {
            Log::error('Invoice batch PDF not found', [
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
            }, $this->mode, $deadline);
            Log::info('Invoice batch processing completed', ['batch_id' => $this->batchId]);
        } catch (\Throwable $e) {
            Log::error('Invoice batch processing failed', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
            ]);
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Invoice batch job marked as failed', [
            'batch_id' => $this->batchId,
            'error' => $e->getMessage(),
        ]);
        if ($batch = InvoiceBatch::find($this->batchId)) {
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }
}
