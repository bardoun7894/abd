<?php

namespace App\Console\Commands;

use App\Services\InvoicePurchaseMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The commercial registration is the second unique key a supplier can be recognised
 * by when OCR mangles the tax number (this data holds a "300" and a 14-digit one).
 * That fallback is only worth anything if the suppliers master actually carries CR
 * numbers — most rows were created before it was captured.
 *
 * Fills suppliers.cr_number from the invoices already on file: for each supplier,
 * the CR that its own invoices agree on. A supplier whose invoices carry more than one
 * well-formed CR is left alone and reported: any disagreement means at least one of them
 * is a misread, and a majority of misreads is still a misread.
 */
class BackfillSupplierCrNumbers extends Command
{
    protected $signature = 'suppliers:backfill-cr {--dry-run : Report only, no writes}';

    protected $description = 'Fill suppliers.cr_number from the CR its own invoices agree on';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $tag = $dry ? '[dry-run] ' : '';

        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'cr_number')) {
            $this->error('No suppliers.cr_number column on this install.');

            return self::SUCCESS;
        }

        $filled = 0;
        $contested = 0;
        $none = 0;
        $own = InvoicePurchaseMapper::ownCrNumbers();

        $suppliers = DB::table('suppliers')
            ->whereNotNull('tax_number')->where('tax_number', '<>', '')
            ->where(fn ($q) => $q->whereNull('cr_number')->orWhere('cr_number', ''))
            ->get();

        foreach ($suppliers as $s) {
            if (! InvoicePurchaseMapper::isPlausibleTax($s->tax_number)) {
                continue;
            }

            // What CR do this supplier's own purchases carry?
            $seen = [];
            $rows = DB::table('purchase')
                ->where('tax_number', $s->tax_number)
                ->whereNotNull('commercial_registration')->where('commercial_registration', '<>', '')
                ->pluck('commercial_registration');

            foreach ($rows as $cr) {
                $digits = preg_replace('/\D+/', '', (string) $cr);
                // The buyer's own CR on a supplier's invoice is not evidence about the supplier.
                if (InvoicePurchaseMapper::isPlausibleCr($digits) && ! in_array($digits, $own, true)) {
                    $seen[$digits] = ($seen[$digits] ?? 0) + 1;
                }
            }
            // One invoice is not agreement: a CR needs at least two invoices behind it.
            if (array_sum($seen) < 2) {
                $none++;

                continue;
            }
            arsort($seen);
            $best = array_key_first($seen);
            $total = array_sum($seen);

            // Unanimity, not a majority: 3 invoices saying X and 1 saying Y is contested.
            if (count($seen) > 1) {
                $this->warn("{$tag}{$s->name}: invoices disagree on the CR (".implode(', ', array_map(
                    fn ($k, $v) => "{$k} x{$v}", array_keys($seen), $seen
                )).') — left empty');
                $contested++;

                continue;
            }

            $this->line("{$tag}{$s->name}  tax={$s->tax_number}  ->  CR {$best} ({$seen[$best]}/{$total} invoices)");
            $filled++;
            if (! $dry) {
                DB::table('suppliers')->where('id', $s->id)->update(['cr_number' => $best]);
            }
        }

        $this->info("{$tag}filled={$filled} contested={$contested} no_cr_on_invoices={$none}");

        return self::SUCCESS;
    }
}
