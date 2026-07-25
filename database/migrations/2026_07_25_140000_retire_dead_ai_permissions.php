<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire the first-generation AI permission rows (201-204).
 *
 * Two generations of AI permissions were seeded:
 *   201-204  2026_07_16_000030_seed_ai_permissions        — the original set
 *   210-213  2026_07_20_000010_seed_ai_feature_permissions — the set actually enforced
 *
 * Only 210-213 are referenced by code (Perm::AI_MASTER / AI_LEASE /
 * AI_PURCHASE_INVOICE / AI_SETTINGS, checked in LeaseController,
 * PurchaseController, InvoiceController, SettingsController and
 * layouts/page_sidebar.blade.php). A repo-wide grep for 201-204 returns zero
 * hits outside their own seeder.
 *
 * They are nevertheless still listed on the role screen — emps/add_role.blade.php
 * selects every per_function row with is_delete=0 — under names that read as
 * exactly the right thing ("استخراج الفواتير (الذكاء الاصطناعي)"). An admin
 * granting 201 to an employee gets NO error and NO access: the employee is still
 * denied, and nothing anywhere explains why. That silent-failure shape is the
 * same class of trap this project has been bitten by before.
 *
 * is_delete=1 hides them from the role screen without deleting history — any
 * existing grant row keeps its referent, and down() restores them.
 */
return new class extends Migration
{
    /** First-generation AI function ids — superseded by 210-213. */
    private const DEAD = [201, 202, 203, 204];

    public function up(): void
    {
        if (! Schema::hasTable('per_function')) {
            return;
        }

        DB::table('per_function')->whereIn('id', self::DEAD)->update(['is_delete' => 1]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('per_function')) {
            return;
        }

        DB::table('per_function')->whereIn('id', self::DEAD)->update(['is_delete' => 0]);
    }
};
