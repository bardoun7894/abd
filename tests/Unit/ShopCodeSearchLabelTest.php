<?php

/**
 * Spec 024 F3 — the shared shop picker (Shop::scopesel_shop_list, feeding every
 * module's shop dropdown) must (a) render "CODE - name" when a code exists and
 * (b) match on the code in its search box. Exercises the REAL scope against
 * seeded SQLite (admin path, emp_job=1, which skips the workers_manager join).
 */

uses(Tests\TestCase::class);

use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // The Shop constructor reads Auth::user()->emp_job/id on every instantiation;
    // emp_job=1 (admin) makes scopesel_shop_list skip the workers_manager join.
    Auth::shouldReceive('user')->andReturn((object) ['id' => 99, 'emp_job' => 1]);
    Auth::shouldReceive('id')->andReturn(99);
    Auth::shouldReceive('check')->andReturn(true);

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('shop', function ($t) {
        $t->increments('shop_id');
        $t->string('shop_name')->nullable();
        $t->string('shop_code', 50)->nullable();
        $t->string('shop_respon')->nullable();
    });
    Schema::create('shop_municip', function ($t) {
        $t->increments('id');
        $t->unsignedBigInteger('shop_id');
        $t->string('municip_no')->nullable();
    });

    DB::table('shop')->insert([
        ['shop_id' => 1, 'shop_name' => 'فوال نور الصباح', 'shop_code' => 'A1', 'shop_respon' => 'x'],
        ['shop_id' => 2, 'shop_name' => 'مطعم الخليج', 'shop_code' => 'B1', 'shop_respon' => 'y'],
        ['shop_id' => 3, 'shop_name' => 'بقالة بدون كود', 'shop_code' => null, 'shop_respon' => 'z'],
    ]);
});

it('renders every picker label as "CODE - name" when a code exists, plain name otherwise', function () {
    $rows = Shop::sel_shop_list('', 1); // static scope call, exactly as the controller does
    $byName = collect($rows)->keyBy('id');

    expect($byName[1]['ItemName'])->toBe('A1 - فوال نور الصباح');
    expect($byName[2]['ItemName'])->toBe('B1 - مطعم الخليج');
    expect($byName[3]['ItemName'])->toBe('بقالة بدون كود'); // no code -> bare name
});

it('matches a shop by its code in the shared picker search box', function () {
    $rows = Shop::sel_shop_list('A1', 1);

    expect(collect($rows)->pluck('id')->all())->toContain(1);
    expect(collect($rows)->pluck('id')->all())->not->toContain(2);
});

it('still matches a shop by its name', function () {
    $rows = Shop::sel_shop_list('الخليج', 1);

    expect(collect($rows)->pluck('id')->all())->toContain(2);
});
