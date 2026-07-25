<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F1 follow-up — one-off, idempotent backfill of "الفرع المُرحّل إليه"
 * for invoices that were transferred BEFORE the stamping fix landed.
 *
 * Until then only the per-invoice push wrote the denormalized columns, so every
 * invoice posted through pushToPurchase()/bulkPush() shows a dash in that
 * column — the employee cannot tell which branch it went to, which is exactly
 * the complaint this closes. The information was never lost: the linked
 * `purchase` row still carries shop_id / manager_id, so the branch is fully
 * recoverable.
 *
 * Deliberate limits:
 *   - `transferred_at` is taken from `mapped_at`, which IS the moment the push
 *     linked the invoice to its purchase — not "now", which would be a lie.
 *   - `transferred_by` is left NULL. The historical rows never recorded who ran
 *     the push, and the batch uploader is NOT necessarily that person; guessing
 *     would forge an attribution in an audit-facing field.
 *   - only NULL labels are written, so a label produced by the live code is
 *     never overwritten.
 *
 * Safe to re-run: a second pass finds nothing left to fill.
 */
class BackfillInvoiceTransferLabels extends Command
{
    protected $signature = 'invoices:backfill-transfer-labels
                            {--dry-run : Print what would change without writing}';

    protected $description = 'Backfill "الفرع المُرحّل إليه" for invoices transferred before Spec 024 F1 stamping';

    public function handle(): int
    {
        if (! Schema::connection('invoices')->hasColumn('invoices', 'transferred_branch_label')) {
            $this->error('invoices.transferred_branch_label missing — run: php artisan migrate --database=invoices --path=database/migrations/invoices --force');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $pending = Invoice::whereNotNull('purchase_id')
            ->whereNull('transferred_branch_label')
            ->orderBy('id');

        $total = (clone $pending)->count();
        if ($total === 0) {
            $this->info('No invoices to backfill.');

            return self::SUCCESS;
        }

        $this->info("Found {$total} transferred invoice(s) with no branch label.");

        // Resolve every branch label once instead of per invoice — 235 invoices
        // across a handful of shops would otherwise be 235 lookups.
        $labels = [];
        $updated = 0;
        $orphaned = 0;

        $pending->chunkById(200, function ($invoices) use (&$labels, &$updated, &$orphaned, $dryRun) {
            $purchaseIds = $invoices->pluck('purchase_id')->filter()->unique()->all();
            $purchases = DB::table('purchase')
                ->whereIn('purchase_id', $purchaseIds)
                ->get(['purchase_id', 'shop_id', 'manager_id'])
                ->keyBy('purchase_id');

            foreach ($invoices as $inv) {
                $purchase = $purchases->get($inv->purchase_id);
                if (! $purchase) {
                    // The purchase was deleted (e.g. an إرجاع) but the link was left
                    // behind — labelling it would invent a branch it is not in.
                    $orphaned++;

                    continue;
                }

                $key = ($purchase->shop_id ?? 'n').'/'.($purchase->manager_id ?? 'n');
                $labels[$key] ??= $this->branchLabel($purchase->shop_id, $purchase->manager_id);
                $label = $labels[$key];

                if ($label === '') {
                    $orphaned++;

                    continue;
                }

                if (! $dryRun) {
                    $inv->forceFill([
                        'transferred_branch_label' => $label,
                        'transferred_at' => $inv->mapped_at,   // when the push actually happened
                        // transferred_by stays NULL — unknown, never guessed.
                    ])->save();
                }
                $updated++;
            }
        });

        foreach ($labels as $key => $label) {
            $this->line("  {$key} → {$label}");
        }

        $verb = $dryRun ? 'سيتم تحديث' : 'تم تحديث';
        $this->info("{$verb} {$updated} فاتورة — {$orphaned} بلا مشترى مرتبط (تُركت كما هي).");

        return self::SUCCESS;
    }

    /**
     * Same "CODE - NAME" shape as InvoiceController::branchLabel() so a
     * backfilled row is indistinguishable from a freshly stamped one.
     */
    private function branchLabel($shopId, $managerId): string
    {
        if ($shopId) {
            $shop = Schema::hasTable('shop')
                ? DB::table('shop')->where('shop_id', $shopId)->first()
                : null;
            $name = $shop->shop_name ?? ('محل #'.$shopId);
            $code = ($shop && Schema::hasColumn('shop', 'shop_code')) ? ($shop->shop_code ?? null) : null;

            return $code ? $code.' - '.$name : $name;
        }
        if ($managerId) {
            $manager = Schema::hasTable('manager')
                ? DB::table('manager')->where('manager_id', $managerId)->first()
                : null;

            return $manager->manager_name ?? ('قائد مجموعة #'.$managerId);
        }

        return '';
    }
}
