<?php

/**
 * Spec 024 F3 — shop-code persistence contract.
 *
 * ShopController::store()/updstore() validate `shop_code` as
 * nullable|string|max:50|unique:shop,shop_code (ignoring self on update) and
 * surface the exact Arabic duplicate message. This test exercises those exact
 * validation rules against an in-memory `shop` table — the controller wraps
 * them behind a Perm gate + AJAX plumbing, but the rule + message ARE the
 * client-facing contract ("كود المحل مستخدم مسبقاً، يرجى إدخال كود آخر.").
 */

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

const SHOP_CODE_DUP_MESSAGE = 'كود المحل مستخدم مسبقاً، يرجى إدخال كود آخر.';

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('shop', function ($table) {
        $table->increments('shop_id');
        $table->string('shop_name')->nullable();
        $table->string('shop_code', 50)->nullable()->unique();
    });

    DB::table('shop')->insert(['shop_id' => 1, 'shop_name' => 'فوال نور الصباح', 'shop_code' => 'A1']);
});

/** Mirrors the store() rule set exactly. */
function storeShopCodeValidator(array $input)
{
    return Validator::make($input, [
        'shop_code' => ['nullable', 'string', 'max:50', 'unique:shop,shop_code'],
    ], ['shop_code.unique' => SHOP_CODE_DUP_MESSAGE]);
}

/** Mirrors the updstore() rule set exactly (ignore this shop's own row). */
function updateShopCodeValidator(array $input, int $shopId)
{
    return Validator::make($input, [
        'shop_code' => ['nullable', 'string', 'max:50', 'unique:shop,shop_code,' . $shopId . ',shop_id'],
    ], ['shop_code.unique' => SHOP_CODE_DUP_MESSAGE]);
}

it('rejects a duplicate shop_code on create with the exact Arabic message', function () {
    $v = storeShopCodeValidator(['shop_code' => 'A1']);

    expect($v->fails())->toBeTrue();
    expect($v->errors()->first('shop_code'))->toBe(SHOP_CODE_DUP_MESSAGE);
});

it('accepts a fresh, unused shop_code on create', function () {
    $v = storeShopCodeValidator(['shop_code' => 'B2']);

    expect($v->fails())->toBeFalse();
});

it('allows an empty shop_code on create (code is optional)', function () {
    expect(storeShopCodeValidator(['shop_code' => null])->fails())->toBeFalse();
    expect(storeShopCodeValidator([])->fails())->toBeFalse();
});

it('lets a shop keep its own code on update (ignores self)', function () {
    // Editing shop #1, keeping A1 — must NOT collide with its own row.
    $v = updateShopCodeValidator(['shop_code' => 'A1'], 1);

    expect($v->fails())->toBeFalse();
});

it('still rejects another shop\'s code on update', function () {
    DB::table('shop')->insert(['shop_id' => 2, 'shop_name' => 'مطعم الخليج', 'shop_code' => 'B1']);

    // Shop #2 trying to take shop #1's code A1 -> blocked.
    $v = updateShopCodeValidator(['shop_code' => 'A1'], 2);

    expect($v->fails())->toBeTrue();
    expect($v->errors()->first('shop_code'))->toBe(SHOP_CODE_DUP_MESSAGE);
});

it('rejects a shop_code longer than 50 characters', function () {
    $v = storeShopCodeValidator(['shop_code' => str_repeat('A', 51)]);

    expect($v->fails())->toBeTrue();
});
