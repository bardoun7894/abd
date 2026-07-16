<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 FR-101 — the last two extracted fields: the issuing-entity name
 * (اسم المنشأة المصدرة، قد يختلف عن اسم المورد) and free-form notes/extra data
 * (أي ملاحظات أو بيانات إضافية).
 */
class AddIssuerAndNotesToInvoices extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            if (! Schema::connection('invoices')->hasColumn('invoices', 'issuer_establishment_name')) {
                $table->string('issuer_establishment_name')->nullable();
                $table->text('notes')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            foreach (['issuer_establishment_name', 'notes'] as $c) {
                if (Schema::connection('invoices')->hasColumn('invoices', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}
