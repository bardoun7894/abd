<?php

namespace App\Console\Commands;

use App\Services\InvoicePurchaseMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair pass for the 2026-09-10 report «الإشكالية تصير في شركة نهلة الوادي».
 *
 * A supplier with a bilingual invoice header came back from the model spelled a
 * different way almost every time — 34 spellings of نهلة الوادي on نور الصباح, 16
 * on صباح النور, one tax number. purchase.purchase_respon stored the raw text and
 * «اسم المورد» searches LIKE against it, so «نهلة الوادي للتجارة» found 662 of
 * 1,012 rows and «نهلة الوادي التجارية» found 175. Same company, one name.
 *
 * Rewrites purchase_respon to the suppliers-master name for every row whose tax
 * number the master knows. Only that: rows with an unknown tax number, or a
 * supplier with no name on file, are left exactly as they are. Idempotent.
 */
class UnifySupplierNames extends Command
{
    protected $signature = 'purchases:unify-supplier-names
        {--dry-run : Report only, no writes}
        {--tax= : The one company to unify (a 15-digit Saudi VAT number)}
        {--name= : Use this name instead of the one in the suppliers master}
        {--all : Sweep every supplier — only with a checked dry run first}';

    protected $description = 'Store one canonical supplier name per company on purchase rows';

    /**
     * A Saudi VAT number is 15 digits, starts with 3 and ends with 3.
     *
     * The first sweep over this data would have merged «محطة سهل» with «WALEED
     * TURNERY» under a tax number of "300", and pulled 22 unrelated vendors under
     * one name via a misread 15-digit number. Tax numbers come off the same OCR as
     * everything else — anything that is not a well-formed VAT number is not
     * evidence that two invoices are the same company.
     */
    public static function isPlausibleTax($tax): bool
    {
        $t = preg_replace('/\D+/', '', (string) $tax);

        return strlen($t) === 15 && str_starts_with($t, '3') && str_ends_with($t, '3');
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $tag = $dry ? '[dry-run] ' : '';

        if (! Schema::hasTable('suppliers')) {
            $this->error('No suppliers table on this install — nothing to unify.');

            return self::SUCCESS;
        }

        if (! $this->option('tax') && ! $this->option('all')) {
            $this->error('Refusing to rename every supplier at once.');
            $this->line('  Pass --tax=<15-digit VAT> for one company (safe, what you normally want),');
            $this->line('  or --all to sweep them — read the --dry-run output first: a misread tax');
            $this->line('  number merges unrelated companies under one name.');

            return self::SUCCESS;
        }

        $suppliers = DB::table('suppliers')
            ->whereNotNull('tax_number')->where('tax_number', '<>', '')
            ->whereNotNull('name')->where('name', '<>', '')
            ->when($this->option('tax'), fn ($q) => $q->where('tax_number', preg_replace('/\D+/', '', (string) $this->option('tax'))))
            ->get()
            ->filter(fn ($s) => self::isPlausibleTax($s->tax_number));

        $companies = 0;
        $rows = 0;

        foreach ($suppliers as $s) {
            // --name lets the operator pick the spelling: نور الصباح's master happens
            // to hold the English name for نهلة while the UI is Arabic throughout.
            $canonical = trim((string) ($this->option('name') ?: $s->name));
            $q = DB::table('purchase')
                ->where('tax_number', $s->tax_number)
                ->where(function ($w) use ($canonical) {
                    $w->where('purchase_respon', '<>', $canonical)->orWhereNull('purchase_respon');
                });

            $n = (clone $q)->count();
            if ($n === 0) {
                continue;
            }
            $companies++;
            $rows += $n;

            $spellings = (clone $q)->select('purchase_respon', DB::raw('COUNT(*) c'))
                ->groupBy('purchase_respon')->orderByDesc('c')->limit(4)->get();
            $this->line("{$tag}{$canonical}  (tax {$s->tax_number}) — {$n} row(s) to unify:");
            foreach ($spellings as $sp) {
                $this->line("      x{$sp->c}  ".mb_substr(preg_replace('/\s+/', ' ', (string) $sp->purchase_respon), 0, 70));
            }

            if (! $dry) {
                (clone $q)->update(['purchase_respon' => $canonical]);
            }
        }

        $this->info("{$tag}companies={$companies} rows={$rows}");

        return self::SUCCESS;
    }
}
