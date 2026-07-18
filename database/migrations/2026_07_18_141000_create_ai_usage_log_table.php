<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI usage ledger: one row per AI call (cache hit or miss) so spend is visible and
 * tunable — powers the admin "AI usage & cost" dashboard (tokens, cost, cache-hit
 * rate per module/day) and complements the subscription quota.
 */
class CreateAiUsageLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_usage_log')) {
            return;
        }
        Schema::create('ai_usage_log', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60)->nullable()->index();
            $table->string('model', 80)->nullable();
            $table->boolean('cache_hit')->default(false);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('est_cost_usd', 12, 6)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_usage_log');
    }
}
