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

it('the shop migration down() removes shop_code and its unique index cleanly', function () {
    Schema::create('shop', function ($table) {
        $table->id('shop_id');
        $table->string('shop_name');
    });

    (new AddShopCodeToShop())->up();
    (new AddShopCodeToShop())->down();

    expect(Schema::hasColumn('shop', 'shop_code'))->toBeFalse();
});
