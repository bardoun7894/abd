<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseMappingToInvoices extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            // Links an extracted invoice to the main-schema purchase row it was pushed to.
            // Lives on the isolated side only — makes re-pushing idempotent.
            $table->unsignedBigInteger('purchase_id')->nullable()->after('status');
            $table->timestamp('mapped_at')->nullable()->after('purchase_id');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            $table->dropColumn(['purchase_id', 'mapped_at']);
        });
    }
}
