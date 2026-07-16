<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Anonymous class (no global name) so a double require during migrate:fresh /
// RefreshDatabase can't "Cannot redeclare" fatal. Filename is unchanged on purpose:
// the recorded migration name must stay matched so this never re-runs on prod.
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('driver_card_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('driver_license_category')->nullable();
            $table->string('driver_license_image')->nullable();
            $table->date('driver_license_expiry')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'driver_card_number',
                'driver_name',
                'driver_id',
                'driver_license_category',
                'driver_license_image',
                'driver_license_expiry',
            ]);
        });
    }
};
