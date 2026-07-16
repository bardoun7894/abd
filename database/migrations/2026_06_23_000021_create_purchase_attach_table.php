<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the `purchase_attach` table that InvoicePurchaseMapper::attachToPurchase()
 * and PurchaseController::delete_file() already reference but which does not exist in
 * the DB (attach is silently skipped today). Column names match the mapper's
 * detectAttachColumns() expectations: purchase_id (FK) + attach_url (file) + type +
 * create_user + created_at.
 */
class CreatePurchaseAttachTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_attach')) {
            return;
        }
        Schema::create('purchase_attach', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id')->index();
            $table->string('attach_url', 5000)->nullable();
            $table->string('type', 40)->nullable();            // e.g. 'invoice'
            $table->unsignedBigInteger('create_user')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_attach');
    }
}
