<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Database queue backing table. The AI invoice/lease pipeline dispatches
// ProcessInvoiceBatch / lease jobs; with QUEUE_CONNECTION=database a persistent
// worker (deploy/abd-queue.service) processes them in the background so uploads
// return immediately instead of blocking the web request (which caused 504s
// under QUEUE_CONNECTION=sync). Anonymous class + hasTable guard so it's safe
// to run on a server where the table was already created by hand.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jobs')) {
            return;
        }
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
