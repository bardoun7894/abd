<?php

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox) — migrations must be guarded/idempotent (re-run
 * safe) and must not collide with bundle 2's (ai-permissions) per_function id
 * space (210-213, see app/Helpers/Perm.php). Runs the actual migration files'
 * up() methods twice against an isolated sqlite :memory: DB.
 */
beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');
    DB::beginTransaction();

    require_once base_path('database/migrations/2026_07_20_120000_create_cash_receipt_table.php');
    require_once base_path('database/migrations/2026_07_20_120100_create_cashbox_ledger_table.php');
    require_once base_path('database/migrations/2026_07_20_120200_seed_cashbox_permissions.php');
});

afterEach(function () {
    DB::rollBack();
});

it('creates cash_receipt and cashbox_ledger idempotently', function () {
    (new CreateCashReceiptTable())->up();
    (new CreateCashReceiptTable())->up(); // second run must no-op, not throw

    (new CreateCashboxLedgerTable())->up();
    (new CreateCashboxLedgerTable())->up();

    expect(Schema::hasTable('cash_receipt'))->toBeTrue();
    expect(Schema::hasTable('cashbox_ledger'))->toBeTrue();
});

it('seeds cashbox permissions at 101/220/221, disjoint from the AI id space (100/210-213), idempotently', function () {
    Schema::create('per_controller', function ($table) {
        $table->unsignedBigInteger('id')->primary();
        $table->string('name');
        $table->string('controller_name')->nullable();
        $table->tinyInteger('is_delete')->default(0);
        $table->integer('order_c')->nullable();
        $table->tinyInteger('is_active')->default(1);
    });
    Schema::create('per_function', function ($table) {
        $table->unsignedBigInteger('id')->primary();
        $table->unsignedBigInteger('parent_id');
        $table->string('name');
        $table->tinyInteger('is_delete')->default(0);
        $table->integer('order_p')->nullable();
        $table->tinyInteger('is_branch')->default(0);
    });

    // Simulate bundle 2 (ai-permissions) having already seeded 100/210-213,
    // exactly as it would on a real deploy since its migration timestamp runs first.
    DB::table('per_controller')->insert(['id' => 100, 'name' => 'الذكاء الاصطناعي', 'controller_name' => 'InvoiceController']);
    foreach ([210, 211, 212, 213] as $id) {
        DB::table('per_function')->insert(['id' => $id, 'parent_id' => 100, 'name' => "AI $id"]);
    }

    (new SeedCashboxPermissions())->up();
    (new SeedCashboxPermissions())->up(); // idempotent re-run

    expect(DB::table('per_controller')->where('id', 101)->exists())->toBeTrue();
    expect(DB::table('per_function')->whereIn('id', [220, 221])->count())->toBe(2);

    // The AI rows must be completely untouched (proves no collision/overwrite).
    foreach ([210, 211, 212, 213] as $id) {
        expect(DB::table('per_function')->where('id', $id)->value('name'))->toBe("AI $id");
    }

    // No cashbox function id may fall inside the AI space, and vice versa.
    $aiIds = [210, 211, 212, 213];
    $cashboxIds = [220, 221];
    expect(array_intersect($aiIds, $cashboxIds))->toBe([]);

    expect(DB::table('per_function')->count())->toBe(6); // 4 AI + 2 cashbox, no duplicates from the re-run
});
