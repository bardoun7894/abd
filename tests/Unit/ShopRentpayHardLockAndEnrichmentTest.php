<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ShopController;
use App\Models\CashReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 — controller-level coverage for ShopController::rentpayReceipt()
 * / rentpayVoid(): permanent hard-lock (only the issuer or a system admin may
 * revert a paid rentpay), voucher enrichment (contract_no/payment_no/
 * period_from/period_to pulled from shop_rent), and the ai_audit_log trail
 * (paid/void/blocked). Follows the house direct-controller-call pattern
 * (tests/Unit/ShopRentpayCashboxTest.php) rather than full HTTP dispatch.
 *
 * Each test sets its own Auth::shouldReceive mock (rather than relying on a
 * shared beforeEach mock) so switching between issuer/non-issuer/admin actors
 * within the suite is unambiguous.
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

    Schema::create('shop_rent', function ($table) {
        $table->increments('shop_rent_id');
        $table->unsignedBigInteger('shop_id');
        $table->string('rent_no')->nullable();
        $table->string('rent_name')->nullable();
        $table->date('rent_sdt')->nullable();
        $table->date('rent_edt')->nullable();
    });

    Schema::create('shop', function ($table) {
        $table->increments('shop_id');
        $table->string('shop_name')->nullable();
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
        $table->string('contract_no', 100)->nullable();
        $table->string('payment_no', 50)->nullable();
        $table->date('period_from')->nullable();
        $table->date('period_to')->nullable();
        $table->string('shop_name', 255)->nullable();
        $table->string('payment_total', 20)->nullable();
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

    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    Schema::create('permission', function ($table) {
        $table->id();
        $table->unsignedBigInteger('emp_id');
        $table->unsignedBigInteger('function_id');
    });

    Schema::create('ai_audit_log', function ($table) {
        $table->id();
        $table->string('document_type', 20);
        $table->unsignedBigInteger('document_id')->nullable();
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->string('action', 20);
        $table->string('field')->nullable();
        $table->text('old_value')->nullable();
        $table->text('new_value')->nullable();
        $table->unsignedBigInteger('change_user')->nullable();
        $table->dateTime('change_at')->nullable();
        $table->text('note')->nullable();
    });

    DB::table('users')->insert([
        ['id' => 10, 'name' => 'موظف الإصدار'],
        ['id' => 20, 'name' => 'موظف آخر'],
    ]);

    // Stub the Auth facade ONCE with a closure that reads the current actor from
    // mutable state, so actAs() can switch the acting user mid-test without
    // re-mocking (re-mocking the facade redeclares the Mockery class and fatals).
    $GLOBALS['__actor'] = ['id' => 0, 'emp_job' => 0];
    Auth::shouldReceive('user')->andReturnUsing(fn () => (object) [
        'id' => $GLOBALS['__actor']['id'],
        'emp_job' => $GLOBALS['__actor']['emp_job'],
        'emp_name' => 'user' . $GLOBALS['__actor']['id'],
    ]);
    Auth::shouldReceive('id')->andReturnUsing(fn () => $GLOBALS['__actor']['id']);
    Auth::shouldReceive('check')->andReturn(true);

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function actAs(int $id, int $empJob): void
{
    // Mutate the actor the beforeEach Auth stub reads from — a plain state swap,
    // no re-mocking, so mid-test issuer -> non-issuer switches take effect.
    $GLOBALS['__actor'] = ['id' => $id, 'emp_job' => $empJob];
}

/** function_id=33 == "دفعات الإيجار" gate ShopController checks throughout. */
function grantRentpayPermission(int $empId): void
{
    DB::table('permission')->insert(['emp_id' => $empId, 'function_id' => 33]);
}

function seedRentpayWithContract(array $overrides = []): int
{
    $shopId = 1;
    DB::table('shop')->insert(['shop_id' => $shopId, 'shop_name' => 'محل الأندلس']);
    DB::table('shop_rent')->insert([
        'shop_id' => $shopId,
        'rent_no' => 'RENT-2026-05',
        'rent_name' => 'مالك المحل',
        'rent_sdt' => '2026-01-01',
        'rent_edt' => '2026-12-31',
    ]);

    return DB::table('shop_rentpay')->insertGetId(array_merge([
        'shop_id' => $shopId,
        'rentpay_dt' => '2026-07-01',
        'rentpay_price' => 2000.0,
        'rentpay_status' => 'unpaid',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides), 'rentpay_id');
}

it('enriches the سند with shop_name/contract_no/payment_no/period and logs a PAID audit row', function () {
    actAs(10, 1); // admin, also the issuer
    $rentpayId = seedRentpayWithContract();

    $response = (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));
    expect(json_decode($response->getContent(), true)['status'])->toBeTrue();

    $receipt = CashReceipt::first();
    expect($receipt->shop_name)->toBe('محل الأندلس');
    expect($receipt->contract_no)->toBe('RENT-2026-05');
    // Sole installment of the contract: ordinal 1 of 1, covering the full span.
    expect($receipt->payment_no)->toBe('1');
    expect($receipt->payment_total)->toBe('1');
    expect($receipt->period_from)->toBe('2026-01-01');
    expect($receipt->period_to)->toBe('2026-12-31');

    $audit = DB::table('ai_audit_log')->where('action', 'paid')->first();
    expect($audit)->not->toBeNull();
    expect($audit->document_type)->toBe('lease');
    expect((int) $audit->document_id)->toBe($rentpayId);
});

it('blocks a non-issuer non-admin rentpayVoid with the exact message and logs a BLOCKED audit row', function () {
    actAs(10, 1); // issuer creates the receipt as admin
    $rentpayId = seedRentpayWithContract();
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));

    // Switch actor: non-issuer, non-admin, but DOES have the rentpay permission
    // (so we hit the hard-lock, not the unrelated Perm gate).
    actAs(20, 0);
    grantRentpayPermission(20);

    $response = (new ShopController())->rentpayVoid(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'reason' => 'محاولة إلغاء',
    ]));

    expect($response->getStatusCode())->toBe(403);
    $data = json_decode($response->getContent(), true);
    expect($data['message_out'])->toBe(
        // Verbatim client wording — any drift here is a spec violation, not a nit.
        'لا يمكن تعديل حالة هذه الدفعة لأنها مرتبطة بسند سداد تم تحريره بواسطة الموظف (موظف الإصدار). '
        . 'في حال الحاجة إلى التعديل، يجب أن يتم ذلك من خلال الموظف الذي حرر السند '
        . 'أو من خلال مستخدم يمتلك صلاحية مدير النظام.'
    );

    expect(CashReceipt::first()->is_void)->toBe(0);
    $row = DB::table('shop_rentpay')->where('rentpay_id', $rentpayId)->first();
    expect($row->rentpay_status)->toBe('paid');

    $audit = DB::table('ai_audit_log')->where('action', 'blocked')->first();
    expect($audit)->not->toBeNull();
    expect((int) $audit->document_id)->toBe($rentpayId);
});

it('allows the issuer to void their own rentpay سند and logs a VOID audit row', function () {
    actAs(10, 0);
    grantRentpayPermission(10);
    $rentpayId = seedRentpayWithContract();
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));

    $response = (new ShopController())->rentpayVoid(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'reason' => 'رجوع عن الدفع',
    ]));

    expect($response->getStatusCode())->toBe(200);
    expect(CashReceipt::first()->is_void)->toBe(1);

    $audit = DB::table('ai_audit_log')->where('action', 'void')->first();
    expect($audit)->not->toBeNull();
});

it('allows a system admin (non-issuer) to void a rentpay سند', function () {
    actAs(10, 0);
    grantRentpayPermission(10);
    $rentpayId = seedRentpayWithContract();
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-20',
    ]));

    actAs(20, 1); // system admin, not the issuer
    $response = (new ShopController())->rentpayVoid(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'reason' => 'تصحيح إداري',
    ]));

    expect($response->getStatusCode())->toBe(200);
    expect(CashReceipt::first()->is_void)->toBe(1);
});
