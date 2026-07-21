<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox): permanent receipt-voucher record (سند قبض/صرف).
 * Append-only — a void never deletes or rewrites a row, it flips is_void + logs
 * who/why/when and CashboxService appends a compensating cashbox_ledger entry.
 * Follows the house `ai_audit_log` / `*_history` convention (change_user +
 * change_at style columns, portable types only — no engine-specific SQL, no
 * enforced FKs, decimal(14,2) for money) so it runs unmodified on Oracle+MySQL.
 */
class CreateCashReceiptTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cash_receipt')) {
            return;
        }

        Schema::create('cash_receipt', function (Blueprint $table) {
            $table->id('receipt_id');
            // Derived from receipt_id right after insert (e.g. 'R-'.$id) — unique,
            // gap-free-enough, never reused even across a void/reversal cycle.
            $table->string('receipt_no', 30)->nullable()->unique();
            $table->string('source_type', 20)->index();   // shop_rentpay | expense | purchase | accountings | financial | lease_payment
            $table->unsignedBigInteger('source_id')->index();
            $table->string('direction', 3);                // in | out
            $table->decimal('amount', 14, 2);
            $table->date('receipt_date');
            $table->string('payer_name')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('is_void')->default(0);
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('void_user')->nullable();
            $table->dateTime('void_date')->nullable();
            $table->unsignedBigInteger('create_user')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_receipt');
    }
}
