<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ShopController;
use App\Models\CashReceipt;
use App\Services\RentpayVoucherContext;
use App\Support\RequestFingerprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — covers the four defects the first F2 pass left behind:
 *
 *  1. اسم المحل was absent from the سند entirely.
 *  2. رقم الدفعة was the raw rentpay_id primary key, not the installment ordinal.
 *  3. الفترة المستحقة was the whole contract span, identical on every voucher.
 *  4. del_rentpay()/updrentpay() bypassed the hard-lock completely — any employee
 *     could delete or edit a payment already tied to an issued سند.
 *
 * Plus the audit-trail device/IP capture, and — most importantly — the guard that
 * the schema-probed device columns NEVER break logging on a schema that predates
 * the migration (both loggers swallow Throwable, so a bad insert would black out
 * the whole audit trail rather than just drop the device field).
 *
 * Follows the house direct-controller-call pattern (ShopRentpayCashboxTest).
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

    Schema::create('shop', function ($table) {
        $table->increments('shop_id');
        $table->string('shop_name')->nullable();
        $table->unsignedBigInteger('manager_id')->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
    });

    // ApimtitTrait::issamecreateshop() joins this for non-admin actors; without
    // it updrentpay() dies on the ownership check before reaching the lock.
    Schema::create('workers_manager', function ($table) {
        $table->id();
        $table->unsignedBigInteger('manager_id');
        $table->unsignedBigInteger('user_id');
    });

    Schema::create('shop_rent', function ($table) {
        $table->increments('shop_rent_id');
        $table->unsignedBigInteger('shop_id');
        $table->string('rent_no')->nullable();
        $table->string('rent_name')->nullable();
        $table->date('rent_sdt')->nullable();
        $table->date('rent_edt')->nullable();
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

    DB::table('users')->insert([
        ['id' => 10, 'name' => 'موظف الإصدار'],
        ['id' => 20, 'name' => 'موظف آخر'],
    ]);

    $GLOBALS['__vactor'] = ['id' => 0, 'emp_job' => 0];
    Auth::shouldReceive('user')->andReturnUsing(fn () => (object) [
        'id' => $GLOBALS['__vactor']['id'],
        'emp_job' => $GLOBALS['__vactor']['emp_job'],
        'emp_name' => 'user' . $GLOBALS['__vactor']['id'],
    ]);
    Auth::shouldReceive('id')->andReturnUsing(fn () => $GLOBALS['__vactor']['id']);
    Auth::shouldReceive('check')->andReturn(true);

    // The schema probes are memoised for the process; each test builds a fresh
    // in-memory schema, so the cache must not leak between them.
    RequestFingerprint::flushColumnCache();

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
    RequestFingerprint::flushColumnCache();
});

function vActAs(int $id, int $empJob): void
{
    $GLOBALS['__vactor'] = ['id' => $id, 'emp_job' => $empJob];
}

function vGrantPerm(int $empId, int $functionId = 33): void
{
    DB::table('permission')->insert(['emp_id' => $empId, 'function_id' => $functionId]);
}

/** ai_audit_log WITH the new device columns (post-migration environment). */
function vCreateAuditLog(bool $withDeviceColumns = true): void
{
    Schema::create('ai_audit_log', function ($table) use ($withDeviceColumns) {
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
        if ($withDeviceColumns) {
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
        }
    });
    RequestFingerprint::flushColumnCache();
}

/**
 * A 12-month contract paid in 4 quarterly installments, plus a second, older
 * contract on the same shop so contract selection is actually exercised.
 *
 * @return array<int,int> rentpay ids in due-date order
 */
function vSeedQuarterlySchedule(): array
{
    DB::table('shop')->insert(['shop_id' => 1, 'shop_name' => 'محل الأندلس', 'manager_id' => 5]);
    // Non-admin actors must own the shop through workers_manager, otherwise
    // updrentpay() stops at the ownership gate instead of the سند lock.
    DB::table('workers_manager')->insert([
        ['manager_id' => 5, 'user_id' => 10],
        ['manager_id' => 5, 'user_id' => 20],
    ]);
    DB::table('shop_rent')->insert([
        ['shop_id' => 1, 'rent_no' => 'RENT-2025-OLD', 'rent_sdt' => '2025-01-01', 'rent_edt' => '2025-12-31'],
        ['shop_id' => 1, 'rent_no' => 'RENT-2026-05', 'rent_sdt' => '2026-01-01', 'rent_edt' => '2026-12-31'],
    ]);

    // Decoy installments on another shop so rentpay_id never coincides with the
    // installment ordinal — that collision is exactly what hid the old bug.
    DB::table('shop')->insert(['shop_id' => 9, 'shop_name' => 'محل آخر']);
    foreach (['2026-01-01', '2026-02-01', '2026-03-01'] as $decoy) {
        DB::table('shop_rentpay')->insert([
            'shop_id' => 9, 'rentpay_dt' => $decoy, 'rentpay_price' => 100.0, 'rentpay_status' => 'unpaid',
        ]);
    }

    $ids = [];
    foreach (['2026-01-01', '2026-04-01', '2026-07-01', '2026-10-01'] as $due) {
        $ids[] = DB::table('shop_rentpay')->insertGetId([
            'shop_id' => 1,
            'rentpay_dt' => $due,
            'rentpay_price' => 2000.0,
            'rentpay_status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'rentpay_id');
    }

    return $ids;
}

function vRentpay(int $id)
{
    return DB::table('shop_rentpay')->where('rentpay_id', $id)->first();
}

// ---------------------------------------------------------------------------
// 1 + 2 + 3 — voucher context: shop name, installment ordinal, own period
// ---------------------------------------------------------------------------

it('numbers the installment by its rank in the contract, not by its rentpay_id', function () {
    $ids = vSeedQuarterlySchedule();

    $third = (new RentpayVoucherContext())->build(vRentpay($ids[2]));

    expect($third['payment_no'])->toBe('3');
    expect($third['payment_total'])->toBe('4');
    // The regression this replaces: payment_no used to be the raw primary key.
    expect($third['payment_no'])->not->toBe((string) $ids[2]);
});

it('gives each installment its OWN period, not the whole contract span', function () {
    $ids = vSeedQuarterlySchedule();

    $second = (new RentpayVoucherContext())->build(vRentpay($ids[1]));
    $third = (new RentpayVoucherContext())->build(vRentpay($ids[2]));

    expect($second['period_from'])->toBe('2026-04-01');
    expect($second['period_to'])->toBe('2026-06-30');   // day before the next due date
    expect($third['period_from'])->toBe('2026-07-01');
    expect($third['period_to'])->toBe('2026-09-30');
    // Two different installments must not print the same period.
    expect($second['period_from'])->not->toBe($third['period_from']);
});

it('runs the LAST installment through to the contract end date', function () {
    $ids = vSeedQuarterlySchedule();

    $last = (new RentpayVoucherContext())->build(vRentpay($ids[3]));

    expect($last['payment_no'])->toBe('4');
    expect($last['period_from'])->toBe('2026-10-01');
    expect($last['period_to'])->toBe('2026-12-31');
});

it('picks the contract whose window contains the due date, not an arbitrary row', function () {
    $ids = vSeedQuarterlySchedule();

    $ctx = (new RentpayVoucherContext())->build(vRentpay($ids[0]));

    expect($ctx['contract_no'])->toBe('RENT-2026-05');   // not the 2025 contract
    expect($ctx['shop_name'])->toBe('محل الأندلس');
});

it('falls back to the contract span for a single or undated installment', function () {
    DB::table('shop')->insert(['shop_id' => 2, 'shop_name' => 'محل الياسمين']);
    DB::table('shop_rent')->insert([
        'shop_id' => 2, 'rent_no' => 'RENT-ONE', 'rent_sdt' => '2026-03-01', 'rent_edt' => '2027-02-28',
    ]);
    $soleId = DB::table('shop_rentpay')->insertGetId([
        'shop_id' => 2, 'rentpay_dt' => null, 'rentpay_price' => 9000.0, 'rentpay_status' => 'unpaid',
    ], 'rentpay_id');

    $ctx = (new RentpayVoucherContext())->build(vRentpay($soleId));

    expect($ctx['payment_no'])->toBe('1');
    expect($ctx['payment_total'])->toBe('1');
    expect($ctx['period_from'])->toBe('2026-03-01');
    expect($ctx['period_to'])->toBe('2027-02-28');
});

it('drops keys the cash_receipt schema cannot store (pre-migration environment)', function () {
    vSeedQuarterlySchedule();
    Schema::table('cash_receipt', function ($table) {
        $table->dropColumn(['shop_name', 'payment_total']);
    });

    $ctx = (new RentpayVoucherContext())->build(vRentpay(1));

    expect($ctx)->not->toHaveKey('shop_name');
    expect($ctx)->not->toHaveKey('payment_total');
    expect($ctx)->toHaveKey('contract_no');
});

it('writes the corrected fields onto the سند through rentpayReceipt', function () {
    vCreateAuditLog();
    vActAs(10, 1);
    $ids = vSeedQuarterlySchedule();

    $response = (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $ids[2], 'amount' => 2000.0, 'receipt_date' => '2026-07-05',
    ]));
    expect($response->getStatusCode())->toBe(200);

    $receipt = CashReceipt::first();
    expect($receipt->shop_name)->toBe('محل الأندلس');
    expect($receipt->payment_no)->toBe('3');
    expect($receipt->payment_total)->toBe('4');
    expect($receipt->period_from)->toBe('2026-07-01');
    expect($receipt->period_to)->toBe('2026-09-30');
});

// ---------------------------------------------------------------------------
// 4 — del_rentpay / updrentpay hard-lock
// ---------------------------------------------------------------------------

function vIssueReceiptFor(int $rentpayId, int $issuerId = 10): void
{
    vActAs($issuerId, 1);
    (new ShopController())->rentpayReceipt(Request::create('/x', 'POST', [
        'id' => $rentpayId, 'amount' => 2000.0, 'receipt_date' => '2026-07-05',
    ]));
}

it('blocks del_rentpay on a payment with a live سند and keeps the row', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(20, 0);
    vGrantPerm(20);

    ob_start();
    (new ShopController())->del_rentpay(Request::create('/x', 'POST', ['id' => $ids[2]]));
    $out = json_decode(ob_get_clean(), true);

    expect($out['status'])->toBeFalse();
    expect($out['message'])->toContain('لا يمكن تعديل حالة هذه الدفعة');
    expect($out['message'])->toContain('موظف الإصدار');
    expect(DB::table('shop_rentpay')->where('rentpay_id', $ids[2])->exists())->toBeTrue();
    expect(DB::table('ai_audit_log')->where('action', 'blocked')->count())->toBe(1);
});

it('blocks del_rentpay even for the issuer while the سند is live — void it first', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(10, 1); // the issuer, and a system admin
    ob_start();
    (new ShopController())->del_rentpay(Request::create('/x', 'POST', ['id' => $ids[2]]));
    $out = json_decode(ob_get_clean(), true);

    expect($out['status'])->toBeFalse();
    expect($out['message'])->toContain('يجب إلغاء السند أولاً');
    expect(DB::table('shop_rentpay')->where('rentpay_id', $ids[2])->exists())->toBeTrue();
});

it('allows del_rentpay once the سند has been voided', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(10, 1);
    (new ShopController())->rentpayVoid(Request::create('/x', 'POST', [
        'id' => $ids[2], 'reason' => 'تصحيح',
    ]));

    ob_start();
    (new ShopController())->del_rentpay(Request::create('/x', 'POST', ['id' => $ids[2]]));
    $out = json_decode(ob_get_clean(), true);

    expect($out['status'])->toBeTrue();
    expect(DB::table('shop_rentpay')->where('rentpay_id', $ids[2])->exists())->toBeFalse();
});

it('leaves del_rentpay untouched for a payment that never had a سند', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();

    vActAs(20, 0);
    vGrantPerm(20);

    ob_start();
    (new ShopController())->del_rentpay(Request::create('/x', 'POST', ['id' => $ids[0]]));
    $out = json_decode(ob_get_clean(), true);

    expect($out['status'])->toBeTrue();
    expect(DB::table('shop_rentpay')->where('rentpay_id', $ids[0])->exists())->toBeFalse();
});

it('blocks updrentpay by a non-issuer and does not change the amount', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(20, 0);
    vGrantPerm(20);

    $response = (new ShopController())->updrentpay(Request::create('/x', 'POST', [
        'rentpay_id' => $ids[2], 'shop_id' => 1, 'rentpay_price' => 999.0, 'rentpay_dt' => '2026-07-01',
    ]));

    $data = json_decode($response->getContent(), true);
    expect($data['status'])->toBeFalse();
    expect($data['message_out'])->toContain('لا يمكن تعديل حالة هذه الدفعة');
    expect((float) vRentpay($ids[2])->rentpay_price)->toBe(2000.0);
});

it('blocks an amount edit even for the issuer, so the سند keeps certifying the real figure', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(10, 1); // the issuer, and a system admin
    $response = (new ShopController())->updrentpay(Request::create('/x', 'POST', [
        'rentpay_id' => $ids[2], 'shop_id' => 1, 'rentpay_price' => 2500.0, 'rentpay_dt' => '2026-07-01',
    ]));

    $data = json_decode($response->getContent(), true);
    expect($data['status'])->toBeFalse();
    expect($data['message_out'])->toContain('يجب إلغاء السند أولاً');

    // The obligation, the سند and the ledger must all still agree on 2000.
    expect((float) vRentpay($ids[2])->rentpay_price)->toBe(2000.0);
    expect((float) CashReceipt::first()->amount)->toBe(2000.0);
    expect((float) DB::table('cashbox_ledger')->value('amount'))->toBe(2000.0);
});

it('allows a note-only edit on a receipted payment and audits the before/after', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();
    vIssueReceiptFor($ids[2]);

    vActAs(10, 1);
    $response = (new ShopController())->updrentpay(Request::create('/x', 'POST', [
        'rentpay_id' => $ids[2], 'shop_id' => 1,
        'rentpay_price' => 2000.0, 'rentpay_dt' => '2026-07-01',
        'rentpay_note' => 'استُلم نقداً بالمكتب',
    ]));

    expect(json_decode($response->getContent(), true)['status'])->toBeTruthy();
    expect(vRentpay($ids[2])->rentpay_note)->toBe('استُلم نقداً بالمكتب');

    $edit = DB::table('ai_audit_log')->where('action', 'edit')->first();
    expect($edit)->not->toBeNull();
    expect($edit->field)->toBe('rentpay_note');
    expect($edit->new_value)->toBe('استُلم نقداً بالمكتب');
    expect((int) $edit->change_user)->toBe(10);
});

it('audits the before/after of an edit on a payment with no سند', function () {
    vCreateAuditLog();
    $ids = vSeedQuarterlySchedule();

    vActAs(10, 1);
    (new ShopController())->updrentpay(Request::create('/x', 'POST', [
        'rentpay_id' => $ids[0], 'shop_id' => 1,
        'rentpay_price' => 3000.0, 'rentpay_dt' => '2026-01-01',
    ]));

    $edit = DB::table('ai_audit_log')->where('action', 'edit')->where('field', 'rentpay_price')->first();
    expect($edit)->not->toBeNull();
    expect((float) $edit->old_value)->toBe(2000.0);
    expect((float) $edit->new_value)->toBe(3000.0);
    expect((float) vRentpay($ids[0])->rentpay_price)->toBe(3000.0);
});

// ---------------------------------------------------------------------------
// Audit trail — device + IP, and the pre-migration safety net
// ---------------------------------------------------------------------------

it('records the IP and device on the audit row when the columns exist', function () {
    vCreateAuditLog(true);
    $ids = vSeedQuarterlySchedule();

    app()->instance('request', Request::create('/x', 'POST', [], [], [], [
        'REMOTE_ADDR' => '203.0.113.44',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/125.0',
    ]));

    vIssueReceiptFor($ids[0]);

    $audit = DB::table('ai_audit_log')->where('action', 'paid')->first();
    expect($audit)->not->toBeNull();
    expect($audit->ip)->toBe('203.0.113.44');
    expect($audit->user_agent)->toContain('Chrome/125.0');
});

it('still writes audit rows on a schema WITHOUT the device columns', function () {
    // The regression this guards: AuditLogger swallows Throwable, so including
    // ip/user_agent unconditionally on a pre-migration schema would silently
    // black out the entire audit trail instead of just dropping the device.
    vCreateAuditLog(false);
    $ids = vSeedQuarterlySchedule();

    vIssueReceiptFor($ids[0]);

    $audit = DB::table('ai_audit_log')->where('action', 'paid')->first();
    expect($audit)->not->toBeNull();
    expect((int) $audit->document_id)->toBe($ids[0]);
});

it('shortens a raw User-Agent into a readable Arabic device label', function () {
    expect(\App\Support\DeviceLabel::short('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/125.0'))
        ->toBe('ويندوز · Chrome');
    expect(\App\Support\DeviceLabel::short('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari/605.1'))
        ->toBe('آيفون · Safari');
    expect(\App\Support\DeviceLabel::short(null))->toBe('—');
});
