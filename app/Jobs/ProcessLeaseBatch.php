<?php

namespace App\Jobs;

use App\Models\LeaseBatch;
use App\Services\LeasePipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    public $tries = 1;

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

        $abs = public_path($batch->pdf_path);
        if (! is_file($abs)) {
            $batch->update(['status' => 'failed', 'error_message' => 'PDF not found: '.$batch->pdf_path]);

            return;
        }

        try {
            $pipeline->run($batch, $abs, $this->model, function ($done, $total) use ($batch) {
                $batch->forceFill(['processed_pages' => $done, 'total_pages' => $total])->save();
            });
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        if ($batch = LeaseBatch::find($this->batchId)) {
            $batch->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }
}
