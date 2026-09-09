<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\GeminiClient;
use App\Services\InvoiceExtractionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Second half of the 2026-09-09 date repair. invoices:repair-swapped-dates fixes
 * the day/month swaps deterministically; what it leaves are dates that are simply
 * misread (2026-05-25 for a printed 25-08-2026, a wrong year, …). The scan is
 * still on disk, so this asks the model ONE narrow question per batch — "for
 * these invoice numbers, copy the printed date verbatim" — and accepts the answer
 * only when it lands inside the batch's own date window. Anything else stays
 * needs_review for a human; a re-read never replaces one guess with another.
 *
 * One Gemini call per batch that has outliers, not per invoice: the whole PDF has
 * to be sent either way (proc_open is disabled on the host, so pages are not
 * rasterized) and the free tier is 500 requests/day.
 */
class RereadOutlierInvoiceDates extends Command
{
    protected $signature = 'invoices:reread-outlier-dates {--dry-run : Ask the model, write nothing} {--batch= : Only this batch id} {--key= : Gemini API key to use instead of the configured one}';

    protected $description = 'Re-read outlier invoice dates from the scanned PDF and repair invoices + purchases';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $tag = $dry ? '[dry-run] ' : '';
        if ($this->option('key')) {
            config()->set('services.gemini.key', $this->option('key'));
        }

        $fixed = 0;
        $unresolved = 0;
        $confirmed = 0;
        $calls = 0;

        $q = InvoiceBatch::query()->orderBy('id');
        if ($this->option('batch')) {
            $q->where('id', (int) $this->option('batch'));
        }

        foreach ($q->get() as $batch) {
            $invoices = Invoice::where('batch_id', $batch->id)->get();
            $dates = [];
            foreach ($invoices as $inv) {
                $dates[$inv->id] = $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : null;
            }
            // A batch with no readable majority (mostly unclear scans) is anchored on
            // its upload month: the client scans the current month's invoices. Only
            // used to decide what to ask about and what answer to accept.
            $fallback = $batch->created_at ? $batch->created_at->format('Y-m') : null;
            $r = InvoiceExtractionService::dateOutliers($dates, $fallback);
            if (! $r['dominant'] || ! $r['outlier']) {
                continue;
            }
            $byId = $invoices->keyBy('id');
            $targets = [];
            foreach (array_keys($r['outlier']) as $id) {
                if (filled($byId[$id]->invoice_number)) {
                    $targets[$byId[$id]->invoice_number] = $id;
                }
            }
            if (! $targets) {
                continue;
            }

            $pdf = public_path($batch->pdf_path);
            if (! is_file($pdf)) {
                $this->warn("{$tag}batch#{$batch->id}: PDF missing ({$batch->pdf_path}) — ".count($targets).' left unresolved');
                $unresolved += count($targets);

                continue;
            }

            try {
                $calls++;
                $answer = (new GeminiClient())->extract($this->prompt(array_keys($targets)), $pdf, $this->schema());
            } catch (\Throwable $e) {
                $this->error("{$tag}batch#{$batch->id}: model call failed — ".mb_substr($e->getMessage(), 0, 160));
                Log::warning('reread-outlier-dates failed', ['batch' => $batch->id, 'reason' => $e->getMessage()]);
                $unresolved += count($targets);

                continue;
            }

            $svc = new InvoiceExtractionService();
            $answered = [];
            foreach ((array) ($answer['dates'] ?? []) as $row) {
                $no = InvoiceExtractionService::normNumber($row['invoice_number'] ?? '');
                foreach ($targets as $num => $id) {
                    if (InvoiceExtractionService::normNumber($num) === $no) {
                        $answered[$id] = (string) ($row['invoice_date'] ?? '');
                    }
                }
            }

            foreach ($targets as $num => $id) {
                $inv = $byId[$id];
                $old = $dates[$id];
                $raw = $answered[$id] ?? null;
                $new = $raw ? $svc->normalize(['invoice_date' => $raw])['invoice_date'] : null;
                // Accept only a date the batch itself vouches for: inside the window AND
                // not swap-shaped ("08:43 02/08/2026" once parsed to 2026-02-08, which is
                // a swap of the truth, not the truth).
                $check = $new ? InvoiceExtractionService::dateOutliers(array_replace($dates, [$id => $new]), $fallback) : null;
                $ok = $new && ! isset($check['outlier'][$id]) && ! isset($check['swap'][$id]);

                // The model read the same date again: the scan really says so — an old
                // invoice filed with this month's run. Confirmed, not suspicious.
                if (! $ok && $new && $new === $old) {
                    $this->line("{$tag}batch#{$batch->id} invoice {$num}: {$old} confirmed from the scan (printed '{$raw}')"
                        .($inv->purchase_id ? " (purchase #{$inv->purchase_id})" : ''));
                    $confirmed++;
                    if (! $dry) {
                        $notes = trim((string) $inv->validation_notes);
                        $note = 'تم التأكد من التاريخ من الصورة (المطبوع: '.$raw.')';
                        $inv->forceFill(['validation_notes' => $notes !== '' ? $notes.' | '.$note : $note])->save();
                    }

                    continue;
                }

                if (! $ok) {
                    $this->warn("{$tag}batch#{$batch->id} invoice {$num}: {$old} — model read '".($raw ?? '∅')."' → ".($new ?? '∅').", still outside {$r['dominant']}; left for review"
                        .($inv->purchase_id ? " (purchase #{$inv->purchase_id})" : ''));
                    $unresolved++;
                    if (! $dry && ! $inv->needs_review) {
                        $notes = trim((string) $inv->validation_notes);
                        $note = 'تاريخ الفاتورة خارج شهر بقية الدفعة ('.$r['dominant'].') — تحقّق من التاريخ المطبوع';
                        $inv->forceFill(['needs_review' => true, 'validation_notes' => $notes !== '' ? $notes.' | '.$note : $note])->save();
                    }

                    continue;
                }

                $this->line("{$tag}batch#{$batch->id} invoice {$num}: {$old} → {$new} (printed '{$raw}')"
                    .($inv->purchase_id ? " (purchase #{$inv->purchase_id})" : ''));
                $fixed++;
                if ($dry) {
                    continue;
                }
                // A previous pass may have flagged this row as an outlier; that reason is
                // gone now, so drop our note and our flag (never anyone else's).
                $notes = (string) $inv->validation_notes;
                $ours = '/\s*\|?\s*تاريخ الفاتورة خارج شهر بقية الدفعة \([^)]*\) — تحقّق من التاريخ المطبوع/u';
                $wasOurs = (bool) preg_match($ours, $notes);
                $notes = trim(preg_replace($ours, '', $notes), " |");
                $note = 'أُعيدت قراءة التاريخ من الصورة ('.$old.' ← '.$new.'، المطبوع: '.$raw.')';
                $fill = [
                    'invoice_date' => $new,
                    'invoice_date_raw' => $raw,
                    'validation_notes' => $notes !== '' ? $notes.' | '.$note : $note,
                ];
                if ($wasOurs) {
                    $fill['needs_review'] = false;
                }
                $inv->forceFill($fill)->save();
                if ($inv->purchase_id) {
                    DB::table('purchase')->where('purchase_id', $inv->purchase_id)
                        ->whereDate('purchase_dt', $old)
                        ->update(['purchase_dt' => $new]);
                }
            }
        }

        $this->info("{$tag}calls={$calls} fixed={$fixed} confirmed={$confirmed} unresolved={$unresolved}");

        return self::SUCCESS;
    }

    private function prompt(array $numbers): string
    {
        return "هذا ملف PDF يحتوي عدة فواتير ضريبية سعودية. ابحث عن الفواتير التي أرقامها التالية فقط:\n"
            .implode("\n", array_map(fn ($n) => "- {$n}", $numbers))
            ."\n\nلكل فاتورة منها أعد رقمها وتاريخ إصدارها **كما هو مطبوع حرفيًا** (مثل 25-08-2026 أو 15-Aug-26) "
            ."دون أي تحويل أو إعادة ترتيب. التواريخ السعودية تُكتب يوم/شهر/سنة. "
            ."إذا لم تجد الفاتورة أو كان التاريخ غير مقروء فأعد null للتاريخ. أعد JSON فقط.\n\n"
            ."For each listed invoice number return {invoice_number, invoice_date} with invoice_date copied verbatim as printed (DD/MM/YYYY is day first). JSON only.";
    }

    private function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'dates' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'invoice_number' => ['type' => 'STRING'],
                            'invoice_date' => ['type' => 'STRING', 'nullable' => true],
                        ],
                        'required' => ['invoice_number'],
                    ],
                ],
            ],
            'required' => ['dates'],
        ];
    }
}
