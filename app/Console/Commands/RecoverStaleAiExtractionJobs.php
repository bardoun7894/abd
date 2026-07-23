<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInteractiveExtraction;
use App\Models\AiExtractionJob;
use App\Models\InvoiceBatch;
use App\Models\LeaseBatch;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Scheduled sweep that recovers AI extraction work stuck in `processing` long
 * after it should have finished. Without this, a killed queue worker (or a
 * ConnectionException that bypasses GeminiClient's retry) leaves the row
 * spinning forever and the polling UI never resolves.
 *
 * Covers three independent surfaces:
 *   1. AiExtractionJob    — the interactive (single-form) extractor.
 *   2. InvoiceBatch       — the bulk invoice-PDF pipeline (ProcessInvoiceBatch).
 *   3. LeaseBatch         — the bulk lease-PDF pipeline (ProcessLeaseBatch).
 * The two batch pipelines had NO sweeper before this — a batch left in
 * `processing` by a crashed worker was unrecoverable from the UI.
 */
class RecoverStaleAiExtractionJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:recover-stale-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark AI extraction jobs and batches stuck in processing as failed';

    /** Arabic note stamped on any batch (and its in-flight rows) we time out. */
    private const BATCH_NOTE = 'تجاوزت عملية الاستخراج الوقت المحدد ولم تكتمل (تم إيقافها تلقائيًا بعد تعطّل المعالجة).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jobCount = $this->sweepInteractiveJobs();
        $invoiceCount = $this->sweepBatches(InvoiceBatch::class, fn (InvoiceBatch $b): HasMany => $b->invoices());
        $leaseCount = $this->sweepBatches(LeaseBatch::class, fn (LeaseBatch $b): HasMany => $b->extractions());

        $this->info("Recovered {$jobCount} stale AI extraction job(s), {$invoiceCount} invoice batch(es), {$leaseCount} lease batch(es).");

        return self::SUCCESS;
    }

    /**
     * Original behaviour — interactive AiExtractionJob rows stuck in `processing`
     * past twice the job's own declared timeout.
     */
    private function sweepInteractiveJobs(): int
    {
        // Use the same timeout the job declares so the threshold stays in sync.
        $timeout = (new ProcessInteractiveExtraction(0))->timeout;
        $threshold = now()->subSeconds($timeout * 2);

        $stale = AiExtractionJob::where('status', 'processing')
            ->where('updated_at', '<=', $threshold)
            ->get();

        foreach ($stale as $job) {
            $job->forceFill([
                'status' => 'failed',
                'error' => 'تجاوزت مهمة الاستخراج الوقت المحدد ولم تُكمل (انتهت صلاحية المهمة).',
            ])->save();
        }

        return $stale->count();
    }

    /**
     * Sweep a bulk-batch model (InvoiceBatch / LeaseBatch) for rows stuck in
     * `processing` past the whole-batch deadline plus a buffer. The batch is
     * marked `failed`, and any child rows (invoices / extractions) still
     * `pending` or `processing` are failed too so the polling UI resolves and
     * the operator sees a clear reason instead of an eternal spinner.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  callable(\Illuminate\Database\Eloquent\Model): HasMany  $childRel
     */
    private function sweepBatches(string $modelClass, callable $childRel): int
    {
        $threshold = $this->batchThreshold();

        $stale = $modelClass::where('status', 'processing')
            ->where('updated_at', '<=', $threshold)
            ->get();

        foreach ($stale as $batch) {
            // Fail the child rows still mid-flight (created rows are already
            // done/failed; these are the never-reached pages).
            $childRel($batch)
                ->whereIn('status', ['pending', 'processing'])
                ->update([
                    'status' => 'failed',
                    'needs_review' => true,
                    'error_message' => self::BATCH_NOTE,
                    'updated_at' => now(),
                ]);

            $batch->forceFill([
                'status' => 'failed',
                'error_message' => self::BATCH_NOTE,
            ])->save();
        }

        return $stale->count();
    }

    /**
     * A batch is stale once it has sat in `processing` longer than the whole-batch
     * deadline (the same config the pipeline soft-deadline and the queue hard-kill
     * use) plus one page_timeout of slack, so we never race a still-running job.
     */
    private function batchThreshold(): \Illuminate\Support\Carbon
    {
        $deadline = (int) config('services.gemini.batch_timeout', 3600);
        $buffer = (int) config('services.gemini.page_timeout', 120);

        return now()->subSeconds($deadline + $buffer);
    }
}
