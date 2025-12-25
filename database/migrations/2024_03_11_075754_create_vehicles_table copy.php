<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('owner_id');
            $table->string('vehicle_type');
            $table->string('plate_number');
            $table->string('serial_number');
            $table->string('model');
            $table->string('color');
            $table->string('license_id');
            $table->string('license_serial');
            $table->string('license_image')->nullable();
            $table->date('license_expiry');
            $table->string('custodian_name');
            $table->string('custodian_phone');
            $table->string('insurance_company');
            $table->string('policy_number');
            $table->date('insurance_issue');
            $table->date('insurance_expiry');
            $table->string('insurance_image')->nullable();
            $table->string('operation_card_number');
            $table->date('operation_card_issue');
            $table->date('operation_card_expiry');
            $table->string('operation_card_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}