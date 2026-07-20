<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ShopController;
use App\Models\CashboxLedger;
use App\Models\CashReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox) — reference integration: shop_rentpay's
 * unpaid<->paid transition now flows through CashboxService instead of the
 * old silent toggle. Follows the house direct-controller-call test pattern
 * (tests/Unit/ShopRentPaymentGenerationTest.php) rather than full HTTP
 * dispatch, since this app has no precedent for route-level feature tests
 * and the permission/DB setup they'd require.
 */
beforeEach(function () {
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
        $table->date('paid_date')->nullable();
        $table->timestamps();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->unsignedBigInteger('update_user')->nullable();
    });

    Schema::create('cash_receipt', function ($table) {
        $table->id('receipt_id');
        $table->string('receipt_no', 30)->nullable()->unique();
        $table->string('source_type', 20);
        $table->unsignedBigInteger('source_id');
        $table->string('direction', 3);
        $table->decimal('amount', 14, 2);
        $table->date('receipt_date');
        $table->string('payer_name')->nullable();
        $table->unsignedBigInteger('received_by')->nullable();
        $table->text('note')->nullable();
        $table->tinyInteger('is_void')->default(0);
        $table->text('void_reason')->nullable();
        $table->unsignedBigInteger('void_user')->nullable();
        $table->dateTime('void_date')->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->dateTime('created_at')->nullable();
    });

    Schema::create('cashbox_ledger', function ($table) {
        $table->id('entry_id');
        $table->unsignedBigInteger('receipt_id');
        $table->string('source_type', 20);
        $table->unsignedBigInteger('source_id');
        $table->string('direction', 3);
        $table->decimal('amount', 14, 2);
        $table->decimal('balance_after', 14, 2)->nullable();
        $table->unsignedBigInteger('reversal_of_entry_id')->nullable();
        $table->unsignedBigInteger('change_user')->nullable();
        $table->dateTime('change_at')->nullable();
        $table->text('note')->nullable();
    });

    Auth::shouldReceive('user')->andReturn((object) ['id' => 1, 'emp_job' => 1, 'emp_name' => 'Test Admin']);
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('check')->andReturn(true);

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function seedRentpay(array $overrides = []): int
{
    return DB::table('shop_rentpay')->insertGetId(array_merge([
        'shop_id' => 1,
        'rentpay_dt' => '2026-07-01',
        'rentpay_price' => 2000.0,
        'rentpay_status' => 'unpaid',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides), 'rentpay_id');
}

it('unpaid->paid via rentpayReceipt inserts exactly one cash_receipt (in) + one in-ledger entry and flips status', function () {
    $rentpayId = seedRentpay();

    $request = Request::create('/dashboard/shop/rentpay/receipt', 'POST', [
        'id' => $rentpayId,
        'amount' => 2000.0,
        'receipt_date' => '2026-07-20',
        'payer_name' => 'مستأجر',
    ]);

    $response = (new ShopController())->rentpayReceipt($request);
    $data = json_decode($response->getContent(), true);

    expect($data['status'])->toBeTrue();
    expect($data['rentpay_status'])->toBe('paid');

    expect(CashReceipt::count())->toBe(1);
    $receipt = CashReceipt::first();
    expect($receipt->source_type)->toBe('shop_rentpay');
    expect((int) $receipt->source_id)->toBe($rentpayId);
    expect($receipt->direction)->toBe('in');
    expect((float) $receipt->amount)->toBe(2000.0);

    expect(CashboxLedger::count())->toBe(1);
    $entry = CashboxLedger::first();
    expect($entry->direction)->toBe('in');
    expect((float) $entry->balance_after)->toBe(2000.0);

    $row = DB::table('shop_rentpay')->where('rentpay_id', $rentpayId)->first();
    expect($row->rentpay_status)->toBe('paid');
    expect($row->paid_date)->not->toBeNull();
});

it('is impossible to mark a rentpay paid without a receipt — the old direct toggle endpoint always rejects with 422 and leaves status unchanged', function () {
    $rentpayId = seedRentpay(['rentpay_status' => 'unpaid']);

    $request = Request::create('/dashboard/shop/toggle_rentpay', 'POST', ['id' => $rentpayId]);
    $response = (new ShopController())->toggle_rentpay($request);

    expect($response->getStatusCode())->toBe(422);
    $data = json_decode($response->getContent(), true);
    expect($data['status'])->toBeFalse();

    $row = DB::table('shop_rentpay')->where('rentpay_id', $rentpayId)->first();
    expect($row->rentpay_status)->toBe('unpaid'); // unchanged — no bypass

    expect(CashReceipt::count())->toBe(0);
    expect(CashboxLedger::count())->toBe(0);
});

it('rejects rentpayVoid without a reason (422) and does not touch the paid rentpay', function () {
    $rentpayId = seedRentpay(['rentpay_status' => 'unpaid']);
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));

    $response = (new ShopController())->rentpayVoid(Request::create('/x', 'POST', ['id' => $rentpayId]));

    expect($response->getStatusCode())->toBe(422);
    $row = DB::table('shop_rentpay')->where('rentpay_id', $rentpayId)->first();
    expect($row->rentpay_status)->toBe('paid'); // unchanged
    expect(CashReceipt::first()->is_void)->toBe(0);
});

it('paid->unpaid via rentpayVoid with a reason voids the receipt, appends a reversal entry, and flips status back — original rows untouched', function () {
    $rentpayId = seedRentpay(['rentpay_status' => 'unpaid']);
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));

    $originalReceipt = CashReceipt::first();
    $originalEntry = CashboxLedger::first();

    $response = (new ShopController())->rentpayVoid(Request::create('/x', 'POST', [
        'id' => $rentpayId,
        'reason' => 'الدفعة أُدخلت بالخطأ',
    ]));
    $data = json_decode($response->getContent(), true);

    expect($data['status'])->toBeTrue();
    expect($data['rentpay_status'])->toBe('unpaid');

    $row = DB::table('shop_rentpay')->where('rentpay_id', $rentpayId)->first();
    expect($row->rentpay_status)->toBe('unpaid');
    expect($row->paid_date)->toBeNull();

    expect(CashReceipt::count())->toBe(1); // never deleted
    $originalReceipt->refresh();
    expect((int) $originalReceipt->is_void)->toBe(1);
    expect($originalReceipt->void_reason)->toBe('الدفعة أُدخلت بالخطأ');
    expect((float) $originalReceipt->amount)->toBe(2000.0); // untouched

    expect(CashboxLedger::count())->toBe(2);
    $originalEntry->refresh();
    expect($originalEntry->direction)->toBe('in');
    expect((float) $originalEntry->amount)->toBe(2000.0); // untouched, never rewritten

    $reversal = CashboxLedger::where('entry_id', '!=', $originalEntry->entry_id)->first();
    expect($reversal->direction)->toBe('out');
    expect((int) $reversal->reversal_of_entry_id)->toBe($originalEntry->entry_id);
});
