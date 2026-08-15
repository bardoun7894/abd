<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoicePurchaseMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Repair pass for the 2026-08-14 client report «أي فاتورة أضغط يجي فاتورة رقم
 * واحد». Purchases pushed while copyImageToPurchases() still stripped the
 * "#page=K" fragment got the WHOLE source.pdf as their attachment, so every
 * invoice opened at page 1. The code fix only helps NEW pushes; this re-copies
 * the attachment for ALREADY-pushed invoices whose image_path still carries a
 * "#page=K" fragment and whose purchase row points at a copied file.
 *
 * Idempotent: once a purchase's purchasefile is a fresh single-page copy the
 * invoice's image_path no longer matches the "whole-document copy" shape, and
 * --dry-run reports without writing.
 */
class RepairPurchaseAttachmentPages extends Command
{
    protected $signature = 'invoices:repair-purchase-attachment-pages {--dry-run : Report only, no writes}';

    protected $description = 'Re-copy purchase attachments that got the whole source.pdf instead of the invoice page';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $fixed = 0;
        $skipped = 0;
        $failed = 0;

        Invoice::whereNotNull('purchase_id')
            ->where('image_path', 'like', '%#page=%')
            ->orderBy('id')
            ->chunk(100, function ($invoices) use ($dry, &$fixed, &$skipped, &$failed) {
                foreach ($invoices as $inv) {
                    $purchase = DB::table('purchase')->where('purchase_id', $inv->purchase_id)->first();
                    if (! $purchase) {
                        $skipped++;

                        continue;
                    }

                    // Only touch purchases whose attachment is one of OUR copies
                    // (uploads/users/images/inv_*.pdf) — a manually-replaced file
                    // or an original-path attachment is not ours to rewrite.
                    $current = (string) ($purchase->purchasefile ?? '');
                    if (! preg_match('#^uploads/users/images/inv_[A-Za-z0-9]+\.pdf$#', $current)) {
                        $skipped++;

                        continue;
                    }

                    $new = InvoicePurchaseMapper::copyImageToPurchases($inv->image_path);
                    if (! $new || $new === $inv->image_path || ! preg_match('#^uploads/users/images/inv_#', $new)) {
                        $failed++;
                        $this->warn("invoice #{$inv->id} (purchase #{$inv->purchase_id}): copy failed-open");

                        continue;
                    }

                    $this->line("invoice #{$inv->id} (purchase #{$inv->purchase_id}): {$inv->image_path} → {$new}".($dry ? ' [dry-run]' : ''));

                    if (! $dry) {
                        try {
                            DB::table('purchase')->where('purchase_id', $inv->purchase_id)
                                ->update(['purchasefile' => $new]);
                            // Keep the المرفقات list in sync when it mirrors the same file.
                            if (\Illuminate\Support\Facades\Schema::hasTable('purchase_attach')) {
                                $map = InvoicePurchaseMapper::detectAttachColumns(
                                    \Illuminate\Support\Facades\Schema::getColumnListing('purchase_attach')
                                );
                                if ($map) {
                                    DB::table('purchase_attach')
                                        ->where($map['fk'], $inv->purchase_id)
                                        ->where($map['file'], $current) // only rows pointing at the OLD copy
                                        ->update([$map['file'] => $new]);
                                }
                            }
                            // Free the old whole-document copy (25 MB each on sabah).
                            $oldAbs = public_path($current);
                            if (is_file($oldAbs)) {
                                @unlink($oldAbs);
                            }
                        } catch (\Throwable $e) {
                            $failed++;
                            Log::warning('repair-purchase-attachment-pages failed', [
                                'invoice_id' => $inv->id,
                                'purchase_id' => $inv->purchase_id,
                                'reason' => $e->getMessage(),
                            ]);
                            $this->warn("invoice #{$inv->id}: DB update failed — {$e->getMessage()}");

                            continue;
                        }
                    }
                    $fixed++;
                }
            });

        $this->info(($dry ? '[dry-run] ' : '')."fixed={$fixed} skipped={$skipped} failed={$failed}");

        return self::SUCCESS;
    }
}
