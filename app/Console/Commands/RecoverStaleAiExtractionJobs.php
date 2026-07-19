<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInteractiveExtraction;
use App\Models\AiExtractionJob;
use Illuminate\Console\Command;

/**
 * Scheduled sweep that recovers AI extraction jobs stuck in `processing` long
 * after they should have finished. Without this, a killed queue worker leaves
 * the row spinning forever and the polling UI never resolves.
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
    protected $description = 'Mark AI extraction jobs stuck in processing as failed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Use the same timeout the job declares so the threshold stays in sync.
        $timeout = (new ProcessInteractiveExtraction(0))->timeout;
        $staleAfter = $timeout * 2;

        $threshold = now()->subSeconds($staleAfter);

        $stale = AiExtractionJob::where('status', 'processing')
            ->where('updated_at', '<=', $threshold)
            ->get();

        $count = 0;
        foreach ($stale as $job) {
            $job->forceFill([
                'status' => 'failed',
                'error' => 'تجاوزت مهمة الاستخراج الوقت المحدد ولم تُكمل (انتهت صلاحية المهمة).',
            ])->save();
            $count++;
        }

        $this->info("Recovered {$count} stale AI extraction job(s).");

        return self::SUCCESS;
    }
}
