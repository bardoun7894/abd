<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 FR-105: suppliers master, so extracted invoices can be matched to a
 * known supplier (tax-number exact + name fuzzy) and the correct supplier suggested
 * on mismatch. Today suppliers are only free-text on each purchase row.
 */
class CreateSuppliersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('suppliers')) {
            return;
        }
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('tax_number', 20)->nullable()->index();
            $table->string('cr_number', 30)->nullable();       // commercial registration
            $table->string('phone', 40)->nullable();
            $table->string('address', 1000)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('create_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
