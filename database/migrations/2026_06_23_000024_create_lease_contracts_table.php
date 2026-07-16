<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 003 FR-202: the approved lease/contract entity in the main DB. Optional
 * link to an existing shop (shop_id) for the bridge to the legacy shop_rent data.
 */
class CreateLeaseContractsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('lease_contracts')) {
            return;
        }
        Schema::create('lease_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no')->nullable()->index();
            $table->unsignedBigInteger('shop_id')->nullable()->index();     // optional bridge to legacy shop
            $table->string('tenant_name')->nullable();
            $table->string('tenant_id_no', 40)->nullable();
            $table->string('landlord_name')->nullable();
            $table->string('landlord_id_no', 40)->nullable();
            $table->string('property_no')->nullable();
            $table->string('unit')->nullable();
            $table->string('property_type', 60)->nullable();
            $table->string('address', 1000)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->index();
            $table->string('duration', 60)->nullable();
            $table->decimal('rent_value', 14, 2)->nullable();
            $table->unsignedInteger('num_payments')->nullable();
            $table->decimal('payment_value', 14, 2)->nullable();
            $table->string('payment_frequency', 40)->nullable();            // monthly|quarterly|yearly...
            $table->decimal('deposit', 14, 2)->nullable();
            $table->string('payment_method', 60)->nullable();
            $table->text('renewal_terms')->nullable();
            $table->text('cancellation_terms')->nullable();
            $table->text('increase_terms')->nullable();
            $table->text('extra_terms')->nullable();
            $table->string('attach_url', 5000)->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('status', 20)->default('active')->index();       // active|ended|renewable|troubled
            $table->unsignedBigInteger('extraction_id')->nullable();        // staging lease_extractions.id
            $table->unsignedBigInteger('create_user')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lease_contracts');
    }
}
