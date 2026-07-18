<?php

namespace App\Jobs;

use App\Models\AiExtractionJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — runs one interactive document extraction (shop / worker / expense / ...)
 * in the background so the upload request returns instantly and the form polls the
 * result. With QUEUE_CONNECTION=sync this runs inline (same as before, just wrapped);
 * with a worker (database/redis) it is truly async and never blocks a web worker.
 *
 * tries=1: interactive prefill is cheap to retry manually; we don't want the queue
 * silently re-running (and re-billing, though the cache dedups) a user-facing action.
 */
class ProcessInteractiveExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    public $tries = 1;

    /** module => extractor service class. */
    private const EXTRACTORS = [
        'shop' => \App\Services\ShopAiExtractor::class,
        'worker' => \App\Services\WorkerAiExtractor::class,
        'expense' => \App\Services\ExpenseAiExtractor::class,
        'manager' => \App\Services\ManagerAiExtractor::class,
        'vehicle' => \App\Services\VehicleAiExtractor::class,
    ];

    public function __construct(public int $jobId) {}

    public function handle(): void
    {
        $row = AiExtractionJob::find($this->jobId);
        if (! $row || $row->status === 'done') {
            return;
        }

        $row->forceFill(['status' => 'processing'])->save();

        $tmp = null;
        try {
            $class = self::EXTRACTORS[$row->module] ?? null;
            if (! $class) {
                throw new \RuntimeException('Unknown extraction module: '.$row->module);
            }

            // Read the stored (possibly encrypted) upload back to a plaintext temp
            // file with the right extension so mime detection / PDF handling works.
            $doc = app(\App\Services\DocumentStorage::class)->read($row->module, $row->file_path);
            $ext = $this->extForMime($doc['mime'] ?? '') ?: (pathinfo($row->file_path, PATHINFO_EXTENSION) ?: 'bin');
            $tmp = tempnam(sys_get_temp_dir(), 'aix').'.'.$ext;
            file_put_contents($tmp, $doc['contents']);

            $data = app($class)->extract($tmp, $row->model);

            $row->forceFill([
                'status' => 'done',
                'result_json' => $data,
                'error' => null,
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Interactive extraction failed', [
                'job_id' => $this->jobId,
                'module' => $row->module,
                'error' => $e->getMessage(),
            ]);
            $row->forceFill([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ])->save();
        } finally {
            if ($tmp) {
                @unlink($tmp);
            }
        }
    }

    private function extForMime(string $mime): ?string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => null,
        };
    }

    /** If the whole job blows up (not caught above), mark the row failed. */
    public function failed(\Throwable $e): void
    {
        $row = AiExtractionJob::find($this->jobId);
        if ($row && $row->status !== 'done') {
            $row->forceFill(['status' => 'failed', 'error' => $e->getMessage()])->save();
        }
    }
}
