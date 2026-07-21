<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 2 (ai-permissions): per-feature AI permission rows under the
 * existing controller 100 ("الذكاء الاصطناعي") seeded by 2026_07_16_000030.
 * Mints 4 fresh function ids (210 master, 211 lease extraction, 212
 * purchase-invoice reading, 213 AI settings) so granting one AI feature no
 * longer accidentally opens the others (see Perm::ai_access()).
 *
 * Mirrors 2026_07_16_000030_seed_ai_permissions.php exactly: guarded,
 * idempotent, plain DB::table inserts — portable across Oracle + MySQL.
 * is_delete=0 is required for the rows to surface in emps/add_role.blade.php's
 * per_function query, so the ids appear in the role-permission screen with no
 * hardcoded list to edit.
 */
class SeedAiFeaturePermissions extends Migration
{
    private const CONTROLLER_ID = 100;

    private const FUNCTIONS = [
        210 => 'كل ميزات الذكاء الاصطناعي (صلاحية جامعة)',
        211 => 'الذكاء الاصطناعي: استخراج عقود الإيجار',
        212 => 'الذكاء الاصطناعي: قراءة فواتير المشتريات',
        213 => 'الذكاء الاصطناعي: إعدادات المفاتيح والموديل (Gemini)',
    ];

    public function up()
    {
        if (! Schema::hasTable('per_controller') || ! Schema::hasTable('per_function')) {
            return; // permission subsystem not present in this DB — nothing to seed
        }

        // Defensive: controller 100 should already exist from 2026_07_16_000030, but
        // insert it if this migration is ever run standalone/out of order on a fresh DB.
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
    }
}
