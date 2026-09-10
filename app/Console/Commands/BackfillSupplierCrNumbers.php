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
 * the CR that its own invoices agree on. A supplier whose invoices disagree is left
 * alone and reported — a contested CR is a misread, not an identity.
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
                if (InvoicePurchaseMapper::isPlausibleCr($digits)) {
                    $seen[$digits] = ($seen[$digits] ?? 0) + 1;
                }
            }
            if (! $seen) {
                $none++;

                continue;
            }
            arsort($seen);
            $best = array_key_first($seen);
            $total = array_sum($seen);

            // Require a clear majority: a CR its invoices disagree about is a misread.
            if ($seen[$best] * 2 <= $total) {
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
