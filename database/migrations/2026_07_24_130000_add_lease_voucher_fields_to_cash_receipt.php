<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 — enrich the lease سند قبض (cash_receipt) with the contract/
 * payment context the client asked for on the voucher: contract no., payment
 * no., due period (from -> to). ADDITIVE + nullable only, hasColumn-guarded so
 * this is safe to re-run on both Oracle and MySQL and never touches existing
 * rows or other source_types (purchase, expense, ...).
 */
class AddLeaseVoucherFieldsToCashReceipt extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('cash_receipt')) {
            return;
        }

        Schema::table('cash_receipt', function (Blueprint $table) {
            if (! Schema::hasColumn('cash_receipt', 'contract_no')) {
                $table->string('contract_no', 100)->nullable();
            }
            if (! Schema::hasColumn('cash_receipt', 'payment_no')) {
                $table->string('payment_no', 50)->nullable();
            }
            if (! Schema::hasColumn('cash_receipt', 'period_from')) {
                $table->date('period_from')->nullable();
            }
            if (! Schema::hasColumn('cash_receipt', 'period_to')) {
                $table->date('period_to')->nullable();
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('cash_receipt')) {
            return;
        }

        Schema::table('cash_receipt', function (Blueprint $table) {
            foreach (['contract_no', 'payment_no', 'period_from', 'period_to'] as $col) {
                if (Schema::hasColumn('cash_receipt', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
