<?php

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 1 (cashbox) — CashboxController-level checks the service/
 * controller unit tests don't cover:
 *   1. The receipt voucher PDF actually renders bytes (not just "the blade
 *      compiles") — both a normal and a voided (VOID watermark) receipt.
 *   2. Perm::get_function_access(220/221) really gates index()/voidReceipt()
 *      for a non-admin user with no matching permission row — every other
 *      test in this bundle mocks emp_job=1, which bypasses Perm entirely.
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

    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name')->nullable();
    });

    Schema::create('permission', function ($table) {
        $table->id();
        $table->unsignedBigInteger('emp_id');
        $table->unsignedBigInteger('function_id');
    });

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function makeCashReceiptRow(bool $void = false): object
{
    $id = DB::table('cash_receipt')->insertGetId([
        'source_type' => 'shop_rentpay',
        'source_id' => 1,
        'direction' => 'in',
        'amount' => 1000.00,
        'receipt_date' => '2026-07-20',
        'payer_name' => 'مستأجر تجريبي',
        'is_void' => $void ? 1 : 0,
        'void_reason' => $void ? 'سبب تجريبي للإلغاء' : null,
        'created_at' => now(),
    ]);

    $row = DB::table('cash_receipt')->where('receipt_id', $id)->first();
    $row->receipt_no = 'R-' . $id;
    DB::table('cash_receipt')->where('receipt_id', $id)->update(['receipt_no' => $row->receipt_no]);

    return \App\Models\CashReceipt::where('receipt_id', $id)->first();
}

it('renders the receipt voucher PDF to real PDF bytes for a normal receipt', function () {
    $receipt = makeCashReceiptRow(false);

    view('dashboard.cashbox.receipt_pdf', ['receipt' => $receipt, 'receivedByName' => 'موظف تجريبي'])->render();
    $out = PDF::Output('t.pdf', 'S');

    expect(substr($out, 0, 4))->toBe('%PDF');
    expect(strlen($out))->toBeGreaterThan(500);
});

it('renders the receipt voucher PDF to real PDF bytes for a voided receipt (VOID watermark path)', function () {
    $receipt = makeCashReceiptRow(true);

    view('dashboard.cashbox.receipt_pdf', ['receipt' => $receipt, 'receivedByName' => 'موظف تجريبي'])->render();
    $out = PDF::Output('t.pdf', 'S');

    expect(substr($out, 0, 4))->toBe('%PDF');
    expect(strlen($out))->toBeGreaterThan(500);
});

it('rejects a non-admin user with no function-220 permission row from the cashbox index', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 42, 'emp_job' => 0, 'emp_name' => 'Non Admin']);
    Auth::shouldReceive('id')->andReturn(42);
    Auth::shouldReceive('check')->andReturn(true);

    $response = (new App\Http\Controllers\Dashboard\CashboxController())->index(\Illuminate\Http\Request::create('/dashboard/cashbox', 'GET'));

    // No matching permission row exists for emp_id=42 -> Perm::get_function_access(220)
    // is false -> index() redirects to show_not_allow instead of rendering the ledger.
    expect($response->getTargetUrl() ?? null)->not->toBeNull();
});

it('rejects a non-admin user with no function-221 permission row from voidReceipt (403)', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 42, 'emp_job' => 0, 'emp_name' => 'Non Admin']);
    Auth::shouldReceive('id')->andReturn(42);
    Auth::shouldReceive('check')->andReturn(true);

    $receipt = makeCashReceiptRow(false);
    $request = \Illuminate\Http\Request::create('/x', 'POST', [
        'receipt_id' => $receipt->receipt_id,
        'reason' => 'محاولة إلغاء بدون صلاحية',
    ]);

    $response = (new App\Http\Controllers\Dashboard\CashboxController())->voidReceipt($request);

    expect($response->getStatusCode())->toBe(403);
    expect(\App\Models\CashReceipt::find($receipt->receipt_id)->is_void)->toBe(0);
});

it('allows a user WITH function-221 permission to void', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 42, 'emp_job' => 0, 'emp_name' => 'Delegate']);
    Auth::shouldReceive('id')->andReturn(42);
    Auth::shouldReceive('check')->andReturn(true);

    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => 221]);

    $receipt = makeCashReceiptRow(false);
    $request = \Illuminate\Http\Request::create('/x', 'POST', [
        'receipt_id' => $receipt->receipt_id,
        'reason' => 'سبب صالح للإلغاء',
    ]);

    $response = (new App\Http\Controllers\Dashboard\CashboxController())->voidReceipt($request);

    expect($response->getStatusCode())->toBe(200);
    expect((int) \App\Models\CashReceipt::find($receipt->receipt_id)->is_void)->toBe(1);
});
