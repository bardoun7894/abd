<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 FR-102/FR-104: line items on the approved (main-DB) purchase record,
 * copied from the staging invoice_items when a purchase is auto-created.
 */
class CreatePurchaseItemsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_items')) {
            return;
        }
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id')->index();
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name')->nullable();
            $table->decimal('quantity', 14, 3)->nullable();
            $table->string('unit', 40)->nullable();
            $table->decimal('unit_price', 14, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->decimal('vat_rate', 6, 3)->nullable();
            $table->decimal('vat_amount', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}
