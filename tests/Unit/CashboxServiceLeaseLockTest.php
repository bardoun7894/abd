<?php

uses(Tests\TestCase::class);

use App\Models\CashReceipt;
use App\Services\CashboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 — permanent hard-lock on the lease/rentpay سند: only the issuer
 * (cash_receipt.received_by) or a system admin (emp_job==1) may revert a
 * shop_rentpay/lease_payment receipt. Every other source_type (e.g. 'purchase')
 * must stay completely unaffected. Mirrors the isolated sqlite :memory: pattern
 * in tests/Unit/CashboxServiceTest.php, extended with a `users` table for the
 * issuer-name lookup baked into the exact block message.
 */
beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

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
        // Spec 024 F2 enrichment columns (additive).
        $table->string('contract_no', 100)->nullable();
        $table->string('payment_no', 50)->nullable();
        $table->date('period_from')->nullable();
        $table->date('period_to')->nullable();
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

    DB::table('users')->insert([
        ['id' => 10, 'name' => 'أحمد الحلو'],
        ['id' => 99, 'name' => 'موظف آخر'],
    ]);

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

/** Seed a receipt directly (never via the controller) so the "issuer" identity
 * is fixed independently of whichever Auth mock a test sets up. */
function seedLeaseReceipt(array $overrides = []): CashReceipt
{
    $data = array_merge([
        'source_type' => 'shop_rentpay',
        'source_id' => 55,
        'direction' => 'in',
        'amount' => 500.0,
        'receipt_date' => '2026-07-20',
        'received_by' => 10, // userA / the issuer
        'is_void' => 0,
        'create_user' => 10,
        'created_at' => now(),
    ], $overrides);

    $id = DB::table('cash_receipt')->insertGetId($data);
    DB::table('cash_receipt')->where('receipt_id', $id)->update(['receipt_no' => 'R-' . $id]);

    DB::table('cashbox_ledger')->insert([
        'receipt_id' => $id,
        'source_type' => $data['source_type'],
        'source_id' => $data['source_id'],
        'direction' => 'in',
        'amount' => $data['amount'],
        'balance_after' => $data['amount'],
        'change_user' => $data['received_by'],
        'change_at' => now(),
    ]);

    return CashReceipt::find($id);
}

it('rejects a non-issuer non-admin void of a shop_rentpay receipt with the exact Arabic block message', function () {
    $receipt = seedLeaseReceipt(); // received_by = 10 (أحمد الحلو)

    $act = fn () => (new CashboxService())->voidReceipt($receipt->receipt_id, 'محاولة إلغاء', 99, false);

    expect($act)->toThrow(RuntimeException::class,
        'لا يمكن تعديل حالة هذه الدفعة لأنها مرتبطة بسند سداد تم تحريره بواسطة الموظف (أحمد الحلو). '
        . 'في حال الحاجة إلى التعديل، يجب أن يتم من خلال الموظف الذي حرر السند أو مستخدم يمتلك صلاحية مدير النظام.');

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(0); // rolled back, untouched
});

it('allows the issuer to void their own shop_rentpay receipt', function () {
    $receipt = seedLeaseReceipt(); // received_by = 10

    (new CashboxService())->voidReceipt($receipt->receipt_id, 'رجوع عن الدفع', 10, false);

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(1);
});

it('allows a system admin to void a shop_rentpay receipt they did not issue', function () {
    $receipt = seedLeaseReceipt(); // received_by = 10

    (new CashboxService())->voidReceipt($receipt->receipt_id, 'تصحيح إداري', 99, true);

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(1);
});

it('does NOT lock a purchase receipt — non-issuer non-admin void still succeeds', function () {
    $receipt = seedLeaseReceipt(['source_type' => 'purchase', 'direction' => 'out']);

    (new CashboxService())->voidReceipt($receipt->receipt_id, 'عكس ترحيل مشترى', 99, false);

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(1);
});

it('preserves legacy 3-arg voidReceipt calls (no lock enforcement when isAdmin is omitted)', function () {
    $receipt = seedLeaseReceipt(); // received_by = 10

    // Old call signature — no 4th arg — must behave exactly as before this feature.
    (new CashboxService())->voidReceipt($receipt->receipt_id, 'استدعاء قديم', 99);

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(1);
});

it('persists contract_no/payment_no/period_from/period_to on recordReceipt when provided', function () {
    $receipt = (new CashboxService())->recordReceipt([
        'source_type' => 'shop_rentpay',
        'source_id' => 77,
        'amount' => 1200.0,
        'receipt_date' => '2026-07-20',
        'received_by' => 10,
        'create_user' => 10,
        'contract_no' => 'RENT-2026-05',
        'payment_no' => '3',
        'period_from' => '2026-07-01',
        'period_to' => '2026-09-30',
    ]);

    $receipt->refresh();
    expect($receipt->contract_no)->toBe('RENT-2026-05');
    expect($receipt->payment_no)->toBe('3');
    expect($receipt->period_from)->toBe('2026-07-01');
    expect($receipt->period_to)->toBe('2026-09-30');
});

it('does not break recordReceipt when enrichment keys are omitted (backward compatible)', function () {
    $receipt = (new CashboxService())->recordReceipt([
        'source_type' => 'purchase',
        'source_id' => 9,
        'amount' => 300.0,
        'receipt_date' => '2026-07-20',
        'direction' => 'out',
    ]);

    expect($receipt->contract_no)->toBeNull();
    expect($receipt->payment_no)->toBeNull();
});
