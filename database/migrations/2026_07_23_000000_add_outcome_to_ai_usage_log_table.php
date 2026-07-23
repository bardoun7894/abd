<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 013 bundle B4 — the AI usage dashboard (SettingsController::aiUsage) had no
 * failure count and no 429-rate, because ai_usage_log only ever recorded successful
 * calls (GeminiClient::logAiUsage on hit/miss). This adds:
 *  - outcome: 'success' | 'failure' — existing rows all pre-date failure logging, so
 *    they default (and backfill) to 'success'.
 *  - status_code: the terminal HTTP status ('429', '500'...) or 'connection' for a
 *    ConnectionException, nullable — only meaningful for outcome='failure' rows.
 * GeminiClient now inserts a 'failure' row (zero tokens/cost) right before it throws,
 * after retries are exhausted, so admin can see failure volume and 429 rate per module/day.
 */
class AddOutcomeToAiUsageLogTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ai_usage_log')) {
            return;
        }
        Schema::table('ai_usage_log', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_usage_log', 'outcome')) {
                $table->string('outcome', 10)->default('success')->after('cache_hit');
            }
            if (! Schema::hasColumn('ai_usage_log', 'status_code')) {
                $table->string('status_code', 20)->nullable()->after('outcome');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('ai_usage_log')) {
            return;
        }
        Schema::table('ai_usage_log', function (Blueprint $table) {
            if (Schema::hasColumn('ai_usage_log', 'status_code')) {
                $table->dropColumn('status_code');
            }
            if (Schema::hasColumn('ai_usage_log', 'outcome')) {
                $table->dropColumn('outcome');
            }
        });
    }
}
