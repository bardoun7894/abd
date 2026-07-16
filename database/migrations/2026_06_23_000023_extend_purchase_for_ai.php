<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 FR-101/FR-104: additive columns on the core `purchase` table so an
 * AI-created purchase can carry the full invoice data (VAT breakdown, discount,
 * currency, invoice type, payment method, commercial registration, supplier link,
 * due date). ADDITIVE + nullable only — never touches existing columns/data.
 */
class ExtendPurchaseForAi extends Migration
{
    public function up()
    {
        Schema::table('purchase', function (Blueprint $table) {
            $add = function ($name, $cb) use ($table) {
                if (! Schema::hasColumn('purchase', $name)) {
                    $cb($table);
                }
            };
            $add('amount_before_vat', fn ($t) => $t->decimal('amount_before_vat', 15, 3)->nullable());
            $add('vat_amount',        fn ($t) => $t->decimal('vat_amount', 15, 3)->nullable());
            $add('vat_rate',          fn ($t) => $t->decimal('vat_rate', 6, 3)->nullable());
            $add('discount_total',    fn ($t) => $t->decimal('discount_total', 15, 3)->nullable());
            $add('currency',          fn ($t) => $t->string('currency', 10)->nullable());
            $add('invoice_type',      fn ($t) => $t->string('invoice_type', 20)->nullable());
            $add('payment_method',    fn ($t) => $t->string('payment_method', 60)->nullable());
            $add('commercial_registration', fn ($t) => $t->string('commercial_registration', 30)->nullable());
            $add('supplier_id',       fn ($t) => $t->unsignedBigInteger('supplier_id')->nullable()->index());
            $add('due_date',          fn ($t) => $t->date('due_date')->nullable());
            $add('source',            fn ($t) => $t->string('source', 20)->nullable()); // 'ai' | 'manual'
        });
    }

    public function down()
    {
        Schema::table('purchase', function (Blueprint $table) {
            foreach ([
                'amount_before_vat', 'vat_amount', 'vat_rate', 'discount_total', 'currency',
                'invoice_type', 'payment_method', 'commercial_registration', 'supplier_id',
                'due_date', 'source',
            ] as $col) {
                if (Schema::hasColumn('purchase', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
