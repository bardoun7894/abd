<?php

namespace App\Console\Commands;

use App\Models\CashReceipt;
use App\Services\RentpayVoucherContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — one-off, idempotent backfill for سند قبض vouchers that
 * were issued BEFORE the voucher-enrichment columns existed (or before the
 * ordinal/period logic was corrected), so re-printing an old voucher shows the
 * same اسم المحل / رقم العقد / رقم الدفعة / الفترة المستحقة as a new one.
 *
 * Deliberately conservative — this rewrites a financial document, so:
 *   - only `shop_rentpay` receipts, only NON-void ones (a voided سند is history;
 *     it must keep showing what it said when it was voided),
 *   - only fields that are currently NULL are written; anything already filled
 *     is left exactly as-is (pass --force to also refresh filled fields),
 *   - --dry-run prints the plan without touching a row.
 *
 * Safe to re-run: a second pass finds nothing left to fill.
 */
class BackfillRentpayVoucherFields extends Command
{
    protected $signature = 'leases:backfill-voucher-fields
                            {--dry-run : Print what would change without writing}
                            {--force : Also overwrite fields that already have a value}';

    protected $description = 'Backfill اسم المحل/رقم العقد/رقم الدفعة/الفترة on lease سند قبض vouchers issued before Spec 024 F2';

    public function handle(): int
    {
        if (! Schema::hasTable('cash_receipt')) {
            $this->error('cash_receipt table not found — nothing to do.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $context = new RentpayVoucherContext();

        $receipts = CashReceipt::where('source_type', 'shop_rentpay')
            ->where('is_void', 0)
            ->orderBy('receipt_id')
            ->get();

        if ($receipts->isEmpty()) {
            $this->info('No lease vouchers to backfill.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($receipts as $receipt) {
            $rentpay = DB::table('shop_rentpay')->where('rentpay_id', $receipt->source_id)->first();
            if (! $rentpay) {
                $this->warn("سند {$receipt->receipt_no}: الدفعة #{$receipt->source_id} غير موجودة — تخطٍّ.");
                $skipped++;

                continue;
            }

            $fresh = $context->build($rentpay);
            $changes = [];
            foreach ($fresh as $column => $value) {
                if ($value === null) {
                    continue;
                }
                if (! $force && filled($receipt->{$column})) {
                    continue; // already carries a value — never silently rewrite it
                }
                if ((string) $receipt->{$column} === (string) $value) {
                    continue;
                }
                $changes[$column] = $value;
            }

            if (! $changes) {
                $skipped++;

                continue;
            }

            $this->line("سند {$receipt->receipt_no}: ".json_encode($changes, JSON_UNESCAPED_UNICODE));

            if (! $dryRun) {
                $receipt->forceFill($changes)->save();
            }
            $updated++;
        }

        $verb = $dryRun ? 'سيتم تحديث' : 'تم تحديث';
        $this->info("{$verb} {$updated} سند — تخطّي {$skipped}.");

        return self::SUCCESS;
    }
}
