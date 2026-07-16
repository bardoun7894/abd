<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Governance (صلاحيات المستخدمين): register the AI screens in the app's own
 * permission system so they appear in the role/permission UI and can be gated in
 * the sidebar exactly like every other module. Uses FIXED high ids (100 / 201-204)
 * so the same ids exist on local and server regardless of auto-increment state.
 * Admins (emp_job==1) bypass Perm and see the menu immediately; other users are
 * granted through the existing role-management screens.
 */
class SeedAiPermissions extends Migration
{
    private const CONTROLLER_ID = 100;

    private const FUNCTIONS = [
        201 => 'استخراج الفواتير (الذكاء الاصطناعي)',
        202 => 'عقود الإيجار (الذكاء الاصطناعي)',
        203 => 'تقارير الفواتير',
        204 => 'تحليلات الإيجارات',
    ];

    public function up()
    {
        if (! Schema::hasTable('per_controller') || ! Schema::hasTable('per_function')) {
            return; // permission subsystem not present in this DB — nothing to seed
        }

        if (! DB::table('per_controller')->where('id', self::CONTROLLER_ID)->exists()) {
            DB::table('per_controller')->insert([
                'id' => self::CONTROLLER_ID,
                'name' => 'الذكاء الاصطناعي',
                'controller_name' => 'InvoiceController',
                'is_delete' => 0,
                'order_c' => self::CONTROLLER_ID,
                'is_active' => 1,
            ]);
        }

        foreach (self::FUNCTIONS as $id => $name) {
            if (! DB::table('per_function')->where('id', $id)->exists()) {
                DB::table('per_function')->insert([
                    'id' => $id,
                    'parent_id' => self::CONTROLLER_ID,
                    'name' => $name,
                    'is_delete' => 0,
                    'order_p' => $id,
                    'is_branch' => 0,
                ]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('per_function')) {
            DB::table('per_function')->whereIn('id', array_keys(self::FUNCTIONS))->delete();
        }
        if (Schema::hasTable('per_controller')) {
            DB::table('per_controller')->where('id', self::CONTROLLER_ID)->delete();
        }
    }
}
