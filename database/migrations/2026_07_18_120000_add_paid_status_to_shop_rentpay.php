<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client feedback (2026-07): rent payments (shop_rentpay / دفعات الإيجار) must carry an
 * explicit paid/unpaid state an employee can toggle, instead of the date-inferred status.
 * ADDITIVE + nullable only — the legacy `shop_rentpay` table predates the migration set,
 * so guard every column with hasColumn and never touch existing columns/data.
 */
class AddPaidStatusToShopRentpay extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('shop_rentpay')) {
            return;
        }
        Schema::table('shop_rentpay', function (Blueprint $table) {
            if (! Schema::hasColumn('shop_rentpay', 'rentpay_status')) {
                $table->string('rentpay_status', 20)->default('unpaid'); // unpaid | paid
            }
            if (! Schema::hasColumn('shop_rentpay', 'paid_date')) {
                $table->date('paid_date')->nullable();
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('shop_rentpay')) {
            return;
        }
        Schema::table('shop_rentpay', function (Blueprint $table) {
            foreach (['rentpay_status', 'paid_date'] as $col) {
                if (Schema::hasColumn('shop_rentpay', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
