<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F1 follow-up — "إرجاع الفاتورة من الفرع" gets its OWN per_function id
 * instead of sharing the re-route permission (222).
 *
 * They are not the same risk. Re-routing UPDATES a purchase row in place and
 * leaves the cashbox سند untouched. Returning REVERSES the whole posting: it
 * voids the سند صرف, deletes purchase_attach/purchase_items/purchase, and
 * unlinks the invoice. Granting someone the ability to move an invoice between
 * branches should not implicitly grant them the ability to delete the purchase
 * it created.
 *
 * ID SPACE — verified free: 1-91 legacy per-controller functions, 210-213
 * ai-permissions (2026_07_20_000010), 220-221 cashbox (2026_07_20_120200),
 * 222 invoice re-route (2026_07_24_000017). 223 is the next free id. Same
 * insert-if-missing, guarded, Oracle+MySQL-portable pattern as those.
 *
 * NOTE: existing holders of 222 do NOT inherit this. Grant 223 explicitly from
 * the permissions screen; until then only a system admin (emp_job==1) can
 * return an invoice — which is the safe default for a destructive action.
 */
class SeedInvoiceReturnPermission extends Migration
{
    private const CONTROLLER_ID = 100;

    private const FUNCTION_ID = 223;

    private const FUNCTION_NAME = 'إرجاع الفاتورة من الفرع (عكس الترحيل)';

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
