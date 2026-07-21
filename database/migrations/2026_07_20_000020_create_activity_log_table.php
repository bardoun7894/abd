<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 3 (activity-log) — system-wide, append-only employee activity
 * log. Captures WRITES ONLY (create/update/delete) plus login/logout — never
 * page views. Follows the house append-only `*_history`/`ai_audit_log`
 * convention (see 2026_06_23_000026_create_ai_audit_log_table.php). Guarded +
 * portable across Oracle and MySQL: no engine-specific SQL, no FK constraint
 * (portability + append-only; rows are never updated or deleted).
 *
 * NOTE: file is dated 2026_07_20_000020 (not _000010 as originally sketched) to
 * avoid sharing a timestamp prefix with 2026_07_20_000010_seed_ai_feature_permissions.php
 * from the sibling ai-permissions bundle — functionally harmless (Laravel keys
 * on the full filename) but keeps migration ordering unambiguous at a glance.
 */
class CreateActivityLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('activity_log')) {
            return;
        }

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 30)->index(); // create|update|delete|login|logout|write
            $table->string('entity_type', 60)->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('summary')->nullable();
            $table->string('route', 150)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip', 45)->nullable(); // IPv6-safe
            $table->dateTime('created_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
}
