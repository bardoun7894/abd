<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceBatchesTable extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->create('invoice_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('original_filename')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('bus_batch_id')->nullable()->index();
            $table->string('status')->default('pending')->index(); // pending|processing|done|failed
            $table->unsignedInteger('total_pages')->default(0);
            $table->unsignedInteger('processed_pages')->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('model_used')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('invoices')->dropIfExists('invoice_batches');
    }
}
