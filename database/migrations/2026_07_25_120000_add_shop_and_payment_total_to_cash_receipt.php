<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — the two voucher fields the client asked for that the
 * first F2 pass missed / got wrong:
 *
 *  - shop_name     : اسم المحل, snapshotted onto the سند at issue time. Stored
 *                    (not joined at print time) on purpose: a سند is a permanent
 *                    financial record, so renaming the shop later must NOT
 *                    rewrite vouchers already handed to a tenant.
 *  - payment_total : the contract's installment count, so the voucher can print
 *                    "رقم الدفعة: 3 من 12". payment_no itself stays a bare
 *                    machine-readable ordinal ('3') — sortable and queryable —
 *                    and the composition happens in the Blade at print time.
 *
 * ADDITIVE + nullable only, hasColumn-guarded, safe to re-run on Oracle+MySQL.
 */
class AddShopAndPaymentTotalToCashReceipt extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('cash_receipt')) {
            return;
        }

        Schema::table('cash_receipt', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_receipt', 'shop_name')) {
                $table->string('shop_name', 255)->nullable();
            }
            if (! Schema::hasColumn('cash_receipt', 'payment_total')) {
                $table->string('payment_total', 20)->nullable();
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('cash_receipt')) {
            return;
        }

        Schema::table('cash_receipt', function (Blueprint $table) {
            foreach (['shop_name', 'payment_total'] as $col) {
                if (Schema::hasColumn('cash_receipt', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
