<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Governance: reprocess-with-history. Before a re-extraction overwrites an invoice
 * row, its previous state is snapshotted here so no prior version is ever lost
 * ("الحفاظ على جميع النسخ السابقة وعدم حذفها"). Read-only audit of past extractions.
 */
class CreateInvoiceVersionsTable extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->create('invoice_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('batch_id')->nullable()->index();
            $table->unsignedInteger('page_number')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->json('snapshot')->nullable();   // full prior attributes of the invoice row
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('invoices')->dropIfExists('invoice_versions');
    }
}
