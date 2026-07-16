<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 001 FR-007: in-app notifications for the AlertDispatcher (surfaced in the
 * existing bell alongside moraslat). Records which channels an alert was sent on.
 */
class CreateAppNotificationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('app_notifications')) {
            return;
        }
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type', 40)->nullable();            // lease_due|lease_expiry|invoice_review...
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('ref_type', 30)->nullable();        // lease_contract|lease_payment|invoice
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_sms')->default(false);
            $table->string('dedup_key')->nullable()->index();  // prevents re-sending the same alert window
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_notifications');
    }
}
