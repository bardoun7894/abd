<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 003 FR-203: the generated payment schedule for a lease contract. Each row is
 * one scheduled installment with due date, amount, status, remaining, penalty.
 */
class CreateLeasePaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('lease_payments')) {
            return;
        }
        Schema::create('lease_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->index();
            $table->unsignedInteger('payment_no');
            $table->date('due_date')->index();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending')->index();  // pending|paid|overdue|partial
            $table->date('paid_date')->nullable();
            $table->decimal('paid_amount', 14, 2)->nullable();
            $table->decimal('remaining', 14, 2)->nullable();
            $table->decimal('penalty', 14, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('lease_contracts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lease_payments');
    }
}
