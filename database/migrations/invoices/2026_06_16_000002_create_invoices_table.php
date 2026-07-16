<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->index();
            $table->unsignedInteger('page_number')->default(1);
            $table->string('image_path')->nullable();

            // Extracted fields (nullable — OCR can miss any of them)
            $table->string('supplier_name')->nullable();
            $table->string('supplier_tax_number', 20)->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('invoice_date_raw')->nullable();
            $table->decimal('amount_before_vat', 14, 2)->nullable();
            $table->decimal('vat_amount', 14, 2)->nullable();
            $table->decimal('total_incl_vat', 14, 2)->nullable();

            $table->json('raw_json')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('status')->default('pending')->index(); // pending|done|failed
            $table->boolean('needs_review')->default(false)->index();
            $table->text('validation_notes')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'page_number']);
            $table->foreign('batch_id')->references('id')->on('invoice_batches')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->dropIfExists('invoices');
    }
}
