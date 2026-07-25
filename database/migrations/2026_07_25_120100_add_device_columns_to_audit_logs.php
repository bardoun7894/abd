<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — "سجل تدقيق يتضمن اسم المستخدم، وتاريخ ووقت العملية،
 * والجهاز أو عنوان الـ IP".
 *
 * Before this migration the two audit trails were split and incomplete:
 *   - ai_audit_log            : user + timestamp, NO ip, NO device.
 *   - employee_activity_log   : user + timestamp + ip, NO device.
 *
 * This closes both gaps so either table alone answers "who / when / from where
 * / on what device". ADDITIVE + nullable only, hasColumn-guarded.
 *
 * NOTE: AuditLogger/ActivityLogger both probe Schema::hasColumn (statically
 * cached) before including these keys, so an environment that has NOT run this
 * migration keeps logging exactly as before instead of silently losing every
 * audit row to a swallowed "column not found".
 */
class AddDeviceColumnsToAuditLogs extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_audit_log')) {
            Schema::table('ai_audit_log', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_audit_log', 'ip')) {
                    $table->string('ip', 45)->nullable(); // IPv6-safe
                }
                if (! Schema::hasColumn('ai_audit_log', 'user_agent')) {
                    $table->string('user_agent', 255)->nullable();
                }
            });
        }

        if (Schema::hasTable('employee_activity_log')) {
            Schema::table('employee_activity_log', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_activity_log', 'user_agent')) {
                    $table->string('user_agent', 255)->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ai_audit_log')) {
            Schema::table('ai_audit_log', function (Blueprint $table) {
                foreach (['ip', 'user_agent'] as $col) {
                    if (Schema::hasColumn('ai_audit_log', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('employee_activity_log')) {
            Schema::table('employee_activity_log', function (Blueprint $table) {
                if (Schema::hasColumn('employee_activity_log', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
            });
        }
    }
}
