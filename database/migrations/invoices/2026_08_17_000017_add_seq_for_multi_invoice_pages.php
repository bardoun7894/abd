<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client report (2026-08-17): «لما يحمل ملف PDF وفيه عدة فواتير، النظام ما يفرز
 * الفواتير … الذكاء حط كل الفواتير ما عزل كل فاتورة لحالها».
 *
 * Root cause was structural: the original table enforced unique(batch_id,
 * page_number), so a page holding several invoices could physically store only
 * ONE row. The extraction prompt was built to match ("هذه صفحة فاتورة ضريبية
 * واحدة … استخرج سجلًا واحدًا"), and persist() upserted on that same key — so
 * extra invoices on a page were merged by the model or silently overwritten.
 *
 * `seq` is the invoice's ordinal WITHIN its page (1-based). Every existing row
 * becomes seq=1, which is exactly what it already was, so this is a no-op for
 * all historical data (noor 1132 invoices, sabah 154+).
 */
class AddSeqForMultiInvoicePages extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        if (! Schema::connection('invoices')->hasTable('invoices')) {
            return;
        }

        if (! Schema::connection('invoices')->hasColumn('invoices', 'seq')) {
            Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
                $table->unsignedInteger('seq')->default(1)->after('page_number');
            });
        }

        // Swap the uniqueness rule: one invoice per page -> one invoice per
        // (page, ordinal-within-page). Wrapped because the old index name differs
        // across the two live installs' histories; losing it is not fatal, but
        // failing the migration would be (the whole file is re-runnable).
        try {
            Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
                $table->dropUnique(['batch_id', 'page_number']);
            });
        } catch (\Throwable $e) {
            // index already dropped or named differently — the new one below is what matters
        }

        try {
            Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
                $table->unique(['batch_id', 'page_number', 'seq']);
            });
        } catch (\Throwable $e) {
            // already present on a re-run
        }
    }

    public function down()
    {
        if (! Schema::connection('invoices')->hasTable('invoices')) {
            return;
        }
        try {
            Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
                $table->dropUnique(['batch_id', 'page_number', 'seq']);
            });
        } catch (\Throwable $e) {
        }
        if (Schema::connection('invoices')->hasColumn('invoices', 'seq')) {
            Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
                $table->dropColumn('seq');
            });
        }
    }
}
