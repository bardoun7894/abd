<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokensToInvoiceBatches extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->table('invoice_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('input_tokens')->default(0)->after('grand_total');
            $table->unsignedBigInteger('output_tokens')->default(0)->after('input_tokens');
            $table->decimal('est_cost_usd', 10, 5)->default(0)->after('output_tokens');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->table('invoice_batches', function (Blueprint $table) {
            $table->dropColumn(['input_tokens', 'output_tokens', 'est_cost_usd']);
        });
    }
}
