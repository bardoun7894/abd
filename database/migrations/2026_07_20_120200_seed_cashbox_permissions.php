<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox): register the الصندوق screens in the app's own
 * permission system, same insert-if-missing pattern as
 * 2026_07_16_000030_seed_ai_permissions.php / 2026_07_20_000010_seed_ai_feature_permissions.php.
 *
 * ID SPACE — coordinated with bundle 2 (ai-permissions): controller 100 and
 * function ids 210-213 are already taken by ai-permissions (Perm::AI_MASTER=210
 * … Perm::AI_SETTINGS=213). Because that seed's migration timestamp (…000010)
 * runs BEFORE this one (…120200), reusing 210/211 here would find them already
 * inserted and silently no-op — CashboxController's permission checks would then
 * be reading rows that actually mean "AI master" / "AI lease". To avoid that,
 * cashbox gets its own controller (101) and its own fresh function ids (220
 * view, 221 void) — verified free (no code referenced 220/221 before this).
 */
class SeedCashboxPermissions extends Migration
{
    private const CONTROLLER_ID = 101;

    private const FUNCTIONS = [
        220 => 'الصندوق وسندات القبض',
        221 => 'إلغاء سند قبض',
    ];

    public function up()
    {
        if (! Schema::hasTable('per_controller') || ! Schema::hasTable('per_function')) {
            return; // permission subsystem not present in this DB — nothing to seed
        }

        if (! DB::table('per_controller')->where('id', self::CONTROLLER_ID)->exists()) {
            DB::table('per_controller')->insert([
                'id' => self::CONTROLLER_ID,
                'name' => 'الصندوق',
                'controller_name' => 'CashboxController',
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
