<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 FR-102: line items (بنود الفاتورة). One row per invoice line, linked to
 * the staging invoice row. Lives in the isolated invoices DB alongside the extraction.
 */
class CreateInvoiceItemsTable extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name')->nullable();
            $table->decimal('quantity', 14, 3)->nullable();
            $table->string('unit', 40)->nullable();
            $table->decimal('unit_price', 14, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->decimal('vat_rate', 6, 3)->nullable();
            $table->decimal('vat_amount', 14, 2)->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->dropIfExists('invoice_items');
    }
}
