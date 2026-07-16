<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageQualityToInvoices extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            // AI's legibility rating of the source scan: clear | medium | unclear.
            $table->string('image_quality', 16)->nullable()->after('confidence');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            $table->dropColumn('image_quality');
        });
    }
}
