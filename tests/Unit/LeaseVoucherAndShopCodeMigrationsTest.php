<?php

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2/F3 — the two new migrations must be additive, guarded, and
 * re-runnable (idempotent) on sqlite (dev) as a proxy for Oracle/MySQL.
 * Mirrors the pattern in tests/Unit/CashboxMigrationsTest.php.
 */
beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');
    DB::beginTransaction();

    require_once base_path('database/migrations/2026_07_20_120000_create_cash_receipt_table.php');
    require_once base_path('database/migrations/2026_07_24_130000_add_lease_voucher_fields_to_cash_receipt.php');
    require_once base_path('database/migrations/2026_07_24_130100_add_shop_code_to_shop.php');
    require_once base_path('database/migrations/2026_07_20_000020_create_activity_log_table.php');
    require_once base_path('database/migrations/2026_06_23_000026_create_ai_audit_log_table.php');
    require_once base_path('database/migrations/2026_07_25_120000_add_shop_and_payment_total_to_cash_receipt.php');
    require_once base_path('database/migrations/2026_07_25_120100_add_device_columns_to_audit_logs.php');
});

afterEach(function () {
    DB::rollBack();
});

it('adds the lease-voucher columns to cash_receipt idempotently, additive + nullable', function () {
    (new CreateCashReceiptTable())->up();

    (new AddLeaseVoucherFieldsToCashReceipt())->up();
    (new AddLeaseVoucherFieldsToCashReceipt())->up(); // second run must no-op, not throw

    foreach (['contract_no', 'payment_no', 'period_from', 'period_to'] as $col) {
        expect(Schema::hasColumn('cash_receipt', $col))->toBeTrue();
    }

    // A pre-existing insert without the new columns still works (additive/nullable).
    $id = DB::table('cash_receipt')->insertGetId([
        'source_type' => 'purchase',
        'source_id' => 1,
        'direction' => 'out',
        'amount' => 100,
        'receipt_date' => '2026-07-24',
        'is_void' => 0,
    ]);
    expect(DB::table('cash_receipt')->where('receipt_id', $id)->value('contract_no'))->toBeNull();
});

it('adds shop_code + a unique index to shop idempotently, additive + nullable', function () {
    Schema::create('shop', function ($table) {
        $table->id('shop_id');
        $table->string('shop_name');
    });

    (new AddShopCodeToShop())->up();
    (new AddShopCodeToShop())->up(); // second run must no-op, not throw

    expect(Schema::hasColumn('shop', 'shop_code'))->toBeTrue();

    DB::table('shop')->insert(['shop_name' => 'محل أ', 'shop_code' => 'A1']);
    DB::table('shop')->insert(['shop_name' => 'محل ب', 'shop_code' => null]);
    DB::table('shop')->insert(['shop_name' => 'محل ج', 'shop_code' => null]); // two NULLs must be allowed

    expect(fn () => DB::table('shop')->insert(['shop_name' => 'محل د', 'shop_code' => 'A1']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('adds shop_name + payment_total to cash_receipt idempotently, additive + nullable', function () {
    (new CreateCashReceiptTable())->up();
    (new AddLeaseVoucherFieldsToCashReceipt())->up();

    (new AddShopAndPaymentTotalToCashReceipt())->up();
    (new AddShopAndPaymentTotalToCashReceipt())->up(); // second run must no-op, not throw

    foreach (['shop_name', 'payment_total'] as $col) {
        expect(Schema::hasColumn('cash_receipt', $col))->toBeTrue();
    }

    // An insert that predates the columns still works (additive/nullable).
    $id = DB::table('cash_receipt')->insertGetId([
        'source_type' => 'expense',
        'source_id' => 7,
        'direction' => 'out',
        'amount' => 50,
        'receipt_date' => '2026-07-25',
        'is_void' => 0,
    ]);
    expect(DB::table('cash_receipt')->where('receipt_id', $id)->value('shop_name'))->toBeNull();

    (new AddShopAndPaymentTotalToCashReceipt())->down();
    expect(Schema::hasColumn('cash_receipt', 'shop_name'))->toBeFalse();
});

it('adds ip + user_agent to BOTH audit tables idempotently', function () {
    (new CreateAiAuditLogTable())->up();
    (new CreateActivityLogTable())->up();

    (new AddDeviceColumnsToAuditLogs())->up();
    (new AddDeviceColumnsToAuditLogs())->up(); // second run must no-op, not throw

    expect(Schema::hasColumn('ai_audit_log', 'ip'))->toBeTrue();
    expect(Schema::hasColumn('ai_audit_log', 'user_agent'))->toBeTrue();
    expect(Schema::hasColumn('employee_activity_log', 'user_agent'))->toBeTrue();
    // Pre-existing column must survive untouched.
    expect(Schema::hasColumn('employee_activity_log', 'ip'))->toBeTrue();

    (new AddDeviceColumnsToAuditLogs())->down();
    expect(Schema::hasColumn('ai_audit_log', 'ip'))->toBeFalse();
    expect(Schema::hasColumn('employee_activity_log', 'user_agent'))->toBeFalse();
    expect(Schema::hasColumn('employee_activity_log', 'ip'))->toBeTrue();
});

it('is a no-op when the audit tables do not exist yet', function () {
    // Migration order is not guaranteed across environments — running the
    // device migration before the tables exist must not throw.
    expect(fn () => (new AddDeviceColumnsToAuditLogs())->up())->not->toThrow(\Throwable::class);
});

it('the shop migration down() removes shop_code and its unique index cleanly', function () {
    Schema::create('shop', function ($table) {
        $table->id('shop_id');
        $table->string('shop_name');
    });

    (new AddShopCodeToShop())->up();
    (new AddShopCodeToShop())->down();

    expect(Schema::hasColumn('shop', 'shop_code'))->toBeFalse();
});
