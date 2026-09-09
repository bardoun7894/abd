<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoiceExtractionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair pass for the 2026-09-09 client report «الفواتير ما تطلع في بحث شهر 8».
 * Until the prompt fix, Gemini returned Saudi DD-MM dates month-first whenever the
 * day was <= 12, so 04-08-2026 was stored as 2026-04-08 and dropped out of the
 * August date filter — in `invoices` AND in the `purchase` row it was pushed to.
 *
 * Uses each batch's own dates as ground truth (InvoiceExtractionService::dateOutliers):
 * a swapped date is rewritten in invoices.invoice_date and, when the linked
 * purchase still carries the same wrong date, in purchase.purchase_dt. Dates that
 * cannot be explained by a swap (OCR misreads) are only listed — never guessed.
 *
 * Idempotent: a corrected batch has nothing left outside its month.
 */
class RepairSwappedInvoiceDates extends Command
{
    protected $signature = 'invoices:repair-swapped-dates {--dry-run : Report only, no writes} {--batch= : Only this batch id}';

    protected $description = 'Fix invoice dates stored month-first (04-08 → April) using the batch\'s dominant month';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $tag = $dry ? '[dry-run] ' : '';
        $swapped = 0;
        $purchasesFixed = 0;
        $outliers = 0;
        $batchesTouched = 0;

        $q = InvoiceBatch::query()->orderBy('id');
        if ($this->option('batch')) {
            $q->where('id', (int) $this->option('batch'));
        }

        $q->chunk(50, function ($batches) use ($dry, $tag, &$swapped, &$purchasesFixed, &$outliers, &$batchesTouched) {
            foreach ($batches as $batch) {
                $invoices = Invoice::where('batch_id', $batch->id)->get();
                $dates = [];
                foreach ($invoices as $inv) {
                    $dates[$inv->id] = $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : null;
                }
                $r = InvoiceExtractionService::dateOutliers($dates);
                if (! $r['dominant'] || (! $r['swap'] && ! $r['outlier'])) {
                    continue;
                }
                $batchesTouched++;
                $byId = $invoices->keyBy('id');

                foreach ($r['swap'] as $id => $new) {
                    $inv = $byId[$id];
                    $old = $dates[$id];
                    $this->line("{$tag}batch#{$batch->id} invoice {$inv->invoice_number}: {$old} → {$new}"
                        .($inv->purchase_id ? " (purchase #{$inv->purchase_id})" : ''));
                    $swapped++;
                    if ($dry) {
                        continue;
                    }
                    $notes = trim((string) $inv->validation_notes);
                    $note = 'تم تصحيح ترتيب اليوم والشهر في التاريخ ('.$old.' ← '.$new.')';
                    $inv->forceFill([
                        'invoice_date' => $new,
                        'validation_notes' => $notes !== '' ? $notes.' | '.$note : $note,
                    ])->save();

                    // Only rewrite the purchase when it still carries the SAME wrong date —
                    // a date the client already corrected by hand is theirs, not ours.
                    if ($inv->purchase_id) {
                        $purchasesFixed += DB::table('purchase')
                            ->where('purchase_id', $inv->purchase_id)
                            ->whereDate('purchase_dt', $old)
                            ->update(['purchase_dt' => $new]);
                    }
                }

                foreach ($r['outlier'] as $id => $d) {
                    $inv = $byId[$id];
                    $this->warn("{$tag}batch#{$batch->id} invoice {$inv->invoice_number}: {$d} is outside {$r['dominant']} and is not a swap — check the printed date"
                        .($inv->purchase_id ? " (purchase #{$inv->purchase_id})" : ''));
                    $outliers++;
                }
            }
        });

        $this->info("{$tag}batches={$batchesTouched} swapped={$swapped} purchases_fixed={$purchasesFixed} outliers={$outliers}");

        return self::SUCCESS;
    }
}
