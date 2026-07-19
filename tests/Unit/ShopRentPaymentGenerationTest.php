<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Use an isolated SQLite :memory: DB for the main connection so these tests
    // do not depend on the local MySQL server.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('shop_rentpay', function ($table) {
        $table->increments('rentpay_id');
        $table->unsignedBigInteger('shop_id');
        $table->date('rentpay_dt')->nullable();
        $table->decimal('rentpay_price', 15, 2)->nullable();
        $table->string('rentpay_note')->nullable();
        $table->string('rentpay_status')->nullable();
        $table->timestamps();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->unsignedBigInteger('update_user')->nullable();
    });

    Auth::shouldReceive('user')->andReturn((object) ['id' => 1, 'emp_job' => 1]);
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('check')->andReturn(true);

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function callMaybeGenerateRentPayments(Request $request, $shop_id): void
{
    $controller = new ShopController();
    $method = new ReflectionMethod($controller, 'maybeGenerateRentPayments');
    $method->invoke($controller, $request, $shop_id);
}

it('generates shop_rentpay rows for a valid lease schedule', function () {
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2020-01-01',
        'rent_edt' => '2025-01-01',
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
        'rent_sched_value' => 20000.0,
        'rent_sched_freq' => 'semi-annual',
    ]);

    callMaybeGenerateRentPayments($request, 99);

    $rows = DB::table('shop_rentpay')->where('shop_id', 99)->get();
    expect($rows)->toHaveCount(10);
    expect((float) $rows->sum('rentpay_price'))->toBe(200000.0);
});

it('skips generation when schedule dates exceed rent_edt', function () {
    Log::shouldReceive('warning')->once();

    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2026-01-01',
        'rent_edt' => '2025-01-01',
        'rent_sched_num' => 12,
        'rent_sched_rentval' => 12000.0,
        'rent_sched_value' => 1000.0,
        'rent_sched_freq' => 'monthly',
    ]);

    callMaybeGenerateRentPayments($request, 100);

    expect(DB::table('shop_rentpay')->where('shop_id', 100)->exists())->toBeFalse();
});

it('does not regenerate payments when shop already has rows', function () {
    DB::table('shop_rentpay')->insert([
        'shop_id' => 101,
        'rentpay_dt' => '2026-01-01',
        'rentpay_price' => 1000.0,
        'rentpay_status' => 'unpaid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2020-01-01',
        'rent_edt' => '2025-01-01',
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
        'rent_sched_value' => 20000.0,
        'rent_sched_freq' => 'semi-annual',
    ]);

    callMaybeGenerateRentPayments($request, 101);

    expect(DB::table('shop_rentpay')->where('shop_id', 101)->count())->toBe(1);
});

it('skips generation when start_date is missing', function () {
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
    ]);

    callMaybeGenerateRentPayments($request, 102);

    expect(DB::table('shop_rentpay')->where('shop_id', 102)->exists())->toBeFalse();
});
