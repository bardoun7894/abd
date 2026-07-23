<?php

uses(Tests\TestCase::class);

use App\Models\CashboxLedger;
use App\Models\CashReceipt;
use App\Services\CashboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox) — unit tests for the single write choke point.
 * Mirrors the house pattern in tests/Unit/ShopRentPaymentGenerationTest.php:
 * isolated sqlite :memory:, tables created by hand (schema mirrors the real
 * migrations), wrapped in a rolled-back transaction per test.
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

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function makeCashboxReceipt(array $overrides = []): CashReceipt
{
    return (new CashboxService())->recordReceipt(array_merge([
        'source_type' => 'shop_rentpay',
        'source_id' => 1,
        'amount' => 1000.0,
        'receipt_date' => '2026-07-20',
        'payer_name' => 'مستأجر تجريبي',
        'received_by' => 1,
        'create_user' => 1,
    ], $overrides));
}

it('records a receipt with a unique receipt_no and an in ledger entry with correct balance', function () {
    $receipt = makeCashboxReceipt(['amount' => 500.0]);

    expect($receipt->receipt_no)->toBe('R-' . $receipt->receipt_id);
    expect(CashReceipt::count())->toBe(1);

    $entry = CashboxLedger::first();
    expect($entry->direction)->toBe('in');
    expect((float) $entry->amount)->toBe(500.0);
    expect((float) $entry->balance_after)->toBe(500.0);
    expect($entry->reversal_of_entry_id)->toBeNull();
});

it('computes running balance correctly across a mixed in + void-out sequence', function () {
    $r1 = makeCashboxReceipt(['amount' => 1000.0]);
    $r2 = makeCashboxReceipt(['amount' => 400.0]);

    // Balance after two receipts: 1400
    $lastEntry = CashboxLedger::orderByDesc('entry_id')->first();
    expect((float) $lastEntry->balance_after)->toBe(1400.0);

    (new CashboxService())->voidReceipt($r1->receipt_id, 'خطأ في الإدخال', 1);

    // Balance after voiding the 1000 receipt: 1400 - 1000 = 400
    $lastEntry = CashboxLedger::orderByDesc('entry_id')->first();
    expect((float) $lastEntry->balance_after)->toBe(400.0);
    expect((float) (new CashboxService())->currentBalance())->toBe(400.0);
});

it('rejects a void with an empty or blank reason', function () {
    $receipt = makeCashboxReceipt();

    expect(fn () => (new CashboxService())->voidReceipt($receipt->receipt_id, '', 1))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => (new CashboxService())->voidReceipt($receipt->receipt_id, '   ', 1))
        ->toThrow(InvalidArgumentException::class);

    expect(CashReceipt::find($receipt->receipt_id)->is_void)->toBe(0);
});

it('voids without deleting: original receipt fields and original ledger entry stay unchanged, a compensating reversal is appended', function () {
    $receipt = makeCashboxReceipt(['amount' => 750.0]);
    $originalEntry = CashboxLedger::first();

    (new CashboxService())->voidReceipt($receipt->receipt_id, 'دفعة مكررة بالخطأ', 7);

    expect(CashReceipt::count())->toBe(1); // never deleted
    expect(CashboxLedger::count())->toBe(2); // original + reversal, never deleted/rewritten

    $receipt->refresh();
    expect((int) $receipt->is_void)->toBe(1);
    expect($receipt->void_reason)->toBe('دفعة مكررة بالخطأ');
    expect((int) $receipt->void_user)->toBe(7);
    expect((float) $receipt->amount)->toBe(750.0); // untouched

    $originalEntry->refresh();
    expect($originalEntry->direction)->toBe('in');
    expect((float) $originalEntry->amount)->toBe(750.0);
    expect($originalEntry->reversal_of_entry_id)->toBeNull();

    $reversal = CashboxLedger::where('entry_id', '!=', $originalEntry->entry_id)->first();
    expect($reversal->direction)->toBe('out');
    expect((float) $reversal->amount)->toBe(750.0);
    expect((int) $reversal->reversal_of_entry_id)->toBe($originalEntry->entry_id);
});

it('never reuses a receipt_no across a create/void cycle', function () {
    $r1 = makeCashboxReceipt();
    (new CashboxService())->voidReceipt($r1->receipt_id, 'إلغاء', 1);
    $r2 = makeCashboxReceipt();

    expect($r1->receipt_no)->not->toBe($r2->receipt_no);
    expect(CashReceipt::pluck('receipt_no')->unique())->toHaveCount(2);
});

it('rejects a non-positive receipt amount', function () {
    expect(fn () => makeCashboxReceipt(['amount' => 0]))->toThrow(InvalidArgumentException::class);
    expect(fn () => makeCashboxReceipt(['amount' => -10]))->toThrow(InvalidArgumentException::class);
});

it('records an out receipt (purchase) that SUBTRACTS from the running balance', function () {
    makeCashboxReceipt(['amount' => 1000.0]);                                   // +1000 (in)
    $out = makeCashboxReceipt(['amount' => 300.0, 'source_type' => 'purchase', 'source_id' => 9, 'direction' => 'out']);

    expect($out->direction)->toBe('out');
    $last = CashboxLedger::orderByDesc('entry_id')->first();
    expect($last->direction)->toBe('out');
    expect((float) $last->amount)->toBe(300.0);
    expect((float) $last->balance_after)->toBe(700.0);       // 1000 - 300
    expect((float) (new CashboxService())->currentBalance())->toBe(700.0);
});
