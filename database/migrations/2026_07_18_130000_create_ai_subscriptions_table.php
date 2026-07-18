<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Spec 007 — single-row AI subscription config gating every Gemini
// extraction call (invoices, leases, shop, ...). Anonymous class so a
// double require during migrate:fresh / RefreshDatabase can't "Cannot
// redeclare" fatal (mirrors 2026_07_16_100000_create_app_settings_table).
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_subscriptions')) {
            return;
        }
        Schema::create('ai_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('quota_pages')->nullable(); // null = unlimited
            $table->unsignedInteger('used_pages')->default(0);
            $table->dateTime('renewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_subscriptions');
    }
};
