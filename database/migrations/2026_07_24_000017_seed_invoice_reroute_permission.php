<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 Feature 1 — re-routing an ALREADY-posted invoice between branches
 * (shop/manager) is a distinct, higher-risk action from the ordinary "push to
 * purchases" permission (55): it mutates a purchase row that already fed the
 * cashbox. Mints its own per_function id under the existing controller 100
 * ("الذكاء الاصطناعي" / InvoiceController, seeded by
 * 2026_07_16_000030_seed_ai_permissions.php).
 *
 * ID SPACE — verified free: ids 1-91 are legacy per-controller functions,
 * 210-213 are ai-permissions (2026_07_20_000010), 220-221 are cashbox
 * (2026_07_20_120200). 222 is the next free id. Same insert-if-missing,
 * guarded, portable (Oracle + MySQL) pattern as those two migrations.
 */
class SeedInvoiceReroutePermission extends Migration
{
    private const CONTROLLER_ID = 100;

    private const FUNCTION_ID = 222;

    private const FUNCTION_NAME = 'إعادة توجيه الفاتورة بين الفروع (بعد الترحيل)';

    public function up()
    {
        if (! Schema::hasTable('per_controller') || ! Schema::hasTable('per_function')) {
            return; // permission subsystem not present in this DB — nothing to seed
        }

        // Defensive: controller 100 should already exist from 2026_07_16_000030.
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

        if (! DB::table('per_function')->where('id', self::FUNCTION_ID)->exists()) {
            DB::table('per_function')->insert([
                'id' => self::FUNCTION_ID,
                'parent_id' => self::CONTROLLER_ID,
                'name' => self::FUNCTION_NAME,
                'is_delete' => 0,
                'order_p' => self::FUNCTION_ID,
                'is_branch' => 0,
            ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('per_function')) {
            DB::table('per_function')->where('id', self::FUNCTION_ID)->delete();
        }
    }
}
