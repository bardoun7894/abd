<?php

/**
 * Spec 024 F2 — سند قبض voucher enrichment rendering. cash_receipt already
 * carries contract_no/payment_no/period_from/period_to for lease/rentpay
 * receipts (populated by ShopController::rentpayReceipt(), covered by
 * ShopRentpayHardLockAndEnrichmentTest.php). This is the FRONTEND half: the
 * PDF blade must render those fields when present and stay unchanged for
 * receipts that lack them (any other source_type).
 *
 * Follows the house pattern for this exact view (tests/Unit/CashboxControllerTest.php):
 * view(...)->render() + PDF::Output('t.pdf','S') to get real PDF bytes, since
 * the file is raw PHP (no HTML echoed to the Blade output buffer) driving a
 * TCPDF singleton. A source-level check backs the "@if-guarded" requirement,
 * since decoding TCPDF's internal text encoding to grep rendered strings is
 * not a reliable test surface.
 */
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $table->string('contract_no', 100)->nullable();
        $table->string('payment_no', 50)->nullable();
        $table->date('period_from')->nullable();
        $table->date('period_to')->nullable();
        $table->string('shop_name', 255)->nullable();
        $table->string('payment_total', 20)->nullable();
    });

    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function receiptPdfBladeSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/cashbox/receipt_pdf.blade.php'));
}

function makeLeaseReceiptRow(): \App\Models\CashReceipt
{
    $id = DB::table('cash_receipt')->insertGetId([
        'receipt_no' => 'R-1',
        'source_type' => 'shop_rentpay',
        'source_id' => 1,
        'direction' => 'in',
        'amount' => 2000.00,
        'receipt_date' => '2026-07-20',
        'payer_name' => 'فوال نور الصباح',
        'created_at' => now(),
        'contract_no' => 'RENT-2026-05',
        'payment_no' => '3',
        'payment_total' => '4',
        'shop_name' => 'محل الأندلس',
        'period_from' => '2026-07-01',
        'period_to' => '2026-09-30',
    ]);

    return \App\Models\CashReceipt::where('receipt_id', $id)->first();
}

function makeNonLeaseReceiptRow(): \App\Models\CashReceipt
{
    $id = DB::table('cash_receipt')->insertGetId([
        'receipt_no' => 'R-2',
        'source_type' => 'expense',
        'source_id' => 5,
        'direction' => 'in',
        'amount' => 500.00,
        'receipt_date' => '2026-07-20',
        'payer_name' => 'دافع آخر',
        'created_at' => now(),
    ]);

    return \App\Models\CashReceipt::where('receipt_id', $id)->first();
}

it('references the lease voucher fields guarded by @if in the blade source', function () {
    $src = receiptPdfBladeSource();

    expect($src)->toContain('$receipt->contract_no');
    expect($src)->toContain('$receipt->payment_no');
    expect($src)->toContain('$receipt->period_from');
    expect($src)->toContain('$receipt->period_to');
    expect($src)->toContain('رقم العقد');
    expect($src)->toContain('رقم الدفعة');
    expect($src)->toContain('الفترة المستحقة');

    // Spec 024 F2 follow-up — اسم المحل was missing from the voucher entirely,
    // and رقم الدفعة is now composed as "n من m" at print time from the bare
    // ordinal + payment_total (kept separate so the column stays sortable).
    expect($src)->toContain('$receipt->shop_name');
    expect($src)->toContain('اسم المحل');
    expect($src)->toContain('$receipt->payment_total');
    expect($src)->toContain(' من ');
});

it('renders a valid PDF for a lease receipt carrying contract/payment/period fields', function () {
    $receipt = makeLeaseReceiptRow();

    view('dashboard.cashbox.receipt_pdf', ['receipt' => $receipt, 'receivedByName' => 'موظف تجريبي'])->render();
    $out = PDF::Output('t.pdf', 'S');

    expect(substr($out, 0, 4))->toBe('%PDF');
    expect(strlen($out))->toBeGreaterThan(500);
});

it('renders a valid PDF unchanged for a non-lease receipt lacking those fields', function () {
    $receipt = makeNonLeaseReceiptRow();

    view('dashboard.cashbox.receipt_pdf', ['receipt' => $receipt, 'receivedByName' => 'موظف تجريبي'])->render();
    $out = PDF::Output('t.pdf', 'S');

    expect(substr($out, 0, 4))->toBe('%PDF');
    expect(strlen($out))->toBeGreaterThan(500);
});
