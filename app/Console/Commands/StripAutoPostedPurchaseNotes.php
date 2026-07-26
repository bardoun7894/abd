<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off, idempotent cleanup for `purchase.note` (client instruction 2026-07-26).
 *
 * Rows posted from the AI invoice extraction carried a trailing part naming the
 * batch and page, e.g.
 *
 *   قبل الضريبة: 78.26 | ضريبة: 11.74 | مُرحّل آلياً من استخراج الفواتير (دفعة #15 صفحة 26)
 *
 * That note is shown verbatim on the المشتريات screen, so it advertised the
 * automatic posting to everyone who opened the list. The client's rule is that a
 * record must read like an employee entry. InvoicePurchaseMapper no longer writes
 * the part; this command removes it from rows written before that change.
 *
 * Deliberately conservative — this edits financial records, so:
 *   - it only ever REMOVES the machine part and the separator in front of it;
 *     the VAT breakdown a user may rely on is preserved verbatim,
 *   - a note that is nothing BUT the machine part becomes NULL, matching a
 *     purchase the employee entered without a note,
 *   - rows whose note does not contain the marker are never touched,
 *   - --dry-run prints the plan without writing.
 *
 * Safe to re-run: a second pass finds nothing left to strip.
 */
class StripAutoPostedPurchaseNotes extends Command
{
    protected $signature = 'purchases:strip-auto-notes
                            {--dry-run : Print what would change without writing}';

    protected $description = 'Remove the «مُرحّل آلياً من استخراج الفواتير» part from purchase notes written before 2026-07-26';

    /** The marker that identifies the machine-written tail. */
    private const MARKER = 'مُرحّل آلياً من استخراج الفواتير';

    public function handle(): int
    {
        if (! Schema::hasTable('purchase')) {
            $this->error('purchase table not found — nothing to do.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        $rows = DB::table('purchase')
            ->select('purchase_id', 'note')
            ->where('note', 'like', '%'.self::MARKER.'%')
            ->orderBy('purchase_id')
            ->get();

        if ($rows->isEmpty()) {
            $this->info('No purchase notes carry the marker — nothing to do.');

            return self::SUCCESS;
        }

        $this->info(($dry ? '[dry-run] ' : '').'Found '.$rows->count().' purchase note(s) to clean.');

        $changed = 0;
        $emptied = 0;

        foreach ($rows as $row) {
            $clean = $this->strip((string) $row->note);

            if ($clean === (string) $row->note) {
                continue; // marker present but shape unexpected — leave it alone
            }

            if ($changed < 5) {
                $this->line('  #'.$row->purchase_id);
                $this->line('    before: '.$row->note);
                $this->line('    after : '.($clean === '' ? '(NULL)' : $clean));
            }

            if (! $dry) {
                DB::table('purchase')
                    ->where('purchase_id', $row->purchase_id)
                    ->update(['note' => $clean === '' ? null : $clean]);
            }

            $changed++;
            if ($clean === '') {
                $emptied++;
            }
        }

        if ($changed > 5) {
            $this->line('  … and '.($changed - 5).' more.');
        }

        $this->info(($dry ? '[dry-run] would update ' : 'Updated ').$changed.' row(s)'
            .($emptied > 0 ? " ({$emptied} became NULL)" : '').'.');

        return self::SUCCESS;
    }

    /**
     * Drop the machine part and the ' | ' separator preceding it, keeping every
     * other segment exactly as the user would read it.
     */
    private function strip(string $note): string
    {
        $parts = preg_split('/\s*\|\s*/u', $note) ?: [];

        $kept = array_values(array_filter(
            $parts,
            fn ($part) => mb_strpos($part, self::MARKER) === false && trim($part) !== ''
        ));

        return implode(' | ', $kept);
    }
}
