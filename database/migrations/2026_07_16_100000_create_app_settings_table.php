<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Spec 005 — key/value store for admin-editable API keys & settings
// (Gemini / SMS / ZATCA ...). Anonymous class so a double require during
// migrate:fresh / RefreshDatabase can't "Cannot redeclare" fatal.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_settings')) {
            return;
        }
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('skey')->unique();
            $table->text('svalue')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
