<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox): append-only running cash ledger — one row per
 * movement. Every receipt inserts an 'in' row; every void appends a compensating
 * 'out' row (the original receipt/entry is never mutated or deleted). Modeled on
 * `ai_audit_log`. Portable Oracle+MySQL: no engine-specific SQL, no enforced FKs
 * (receipt_id/reversal_of_entry_id are FK-by-convention only).
 *
 * `reversal_of_entry_id` is the discriminator that keeps 'out' unambiguous once
 * real outflow source types (expense/purchase/...) start posting genuine 'out'
 * entries in a later bundle — without it there would be no way to tell a
 * void-reversal 'out' apart from a genuine outflow 'out' when walking the ledger.
 */
class CreateCashboxLedgerTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cashbox_ledger')) {
            return;
        }

        Schema::create('cashbox_ledger', function (Blueprint $table) {
            $table->id('entry_id');
            $table->unsignedBigInteger('receipt_id')->index();
            $table->string('source_type', 20)->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->string('direction', 3)->index(); // in | out
            $table->decimal('amount', 14, 2);
            // Running-balance cache computed under lock at write time. Treated as a
            // cache, not the sole source of truth — CashboxService::currentBalance()
            // re-derives it at read time via SUM(in)-SUM(out) as the authoritative check.
            $table->decimal('balance_after', 14, 2)->nullable();
            $table->unsignedBigInteger('reversal_of_entry_id')->nullable()->index();
            $table->unsignedBigInteger('change_user')->nullable()->index();
            $table->dateTime('change_at')->nullable();
            $table->text('note')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashbox_ledger');
    }
}
