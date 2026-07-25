<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 Feature 1 — per-invoice branch transfer between shops/managers.
 * Denormalized columns on the isolated `invoices` connection so the UI can
 * cheaply show "الفرع المُرحّل إليه" + transfer date + employee without a
 * join back to `purchase` (main connection), and so the label survives a
 * later re-route (each call overwrites these three columns).
 * ADDITIVE + nullable only, hasColumn-guarded — mirrors
 * 2026_07_18_120000_add_paid_status_to_shop_rentpay.php's dual-DB-safe shape.
 */
class AddTransferFieldsToInvoices extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        if (! Schema::connection('invoices')->hasTable('invoices')) {
            return;
        }
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            if (! Schema::connection('invoices')->hasColumn('invoices', 'transferred_branch_label')) {
                $table->string('transferred_branch_label')->nullable()->after('mapped_at');
            }
            if (! Schema::connection('invoices')->hasColumn('invoices', 'transferred_at')) {
                $table->dateTime('transferred_at')->nullable()->after('transferred_branch_label');
            }
            if (! Schema::connection('invoices')->hasColumn('invoices', 'transferred_by')) {
                $table->unsignedBigInteger('transferred_by')->nullable()->after('transferred_at');
            }
        });
    }

    public function down()
    {
        if (! Schema::connection('invoices')->hasTable('invoices')) {
            return;
        }
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            foreach (['transferred_branch_label', 'transferred_at', 'transferred_by'] as $col) {
                if (Schema::connection('invoices')->hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
