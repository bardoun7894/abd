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

/** @return string|null the Arabic outcome shown to the operator */
function callMaybeGenerateRentPayments(Request $request, $shop_id): ?string
{
    $controller = new ShopController();
    $method = new ReflectionMethod($controller, 'maybeGenerateRentPayments');

    return $method->invoke($controller, $request, $shop_id);
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

/*
 * The client's report was "لم تُرحل، لا أدري لماذا" — the generation had five
 * silent bail-outs, so a save that produced nothing looked exactly like one that
 * worked. Every outcome must now come back as words the operator can act on.
 */
it('reports why nothing was generated when the start date is missing', function () {
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
    ]);

    $outcome = callMaybeGenerateRentPayments($request, 103);

    expect($outcome)->toBeString()
        ->and($outcome)->toContain('تاريخ بداية العقد');
});

it('reports why nothing was generated when the shop already has دفعات', function () {
    DB::table('shop_rentpay')->insert([
        'shop_id' => 104,
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

    $outcome = callMaybeGenerateRentPayments($request, 104);

    expect($outcome)->toContain('دفعات مسجّلة بالفعل');
});

it('reports the count when generation succeeds', function () {
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2020-01-01',
        'rent_edt' => '2025-01-01',
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
        'rent_sched_value' => 20000.0,
        'rent_sched_freq' => 'semi-annual',
    ]);

    $outcome = callMaybeGenerateRentPayments($request, 105);

    expect($outcome)->toContain('10')
        ->and($outcome)->toContain('دفعة إيجار');
});

it('stays silent on an ordinary save that carries no lease schedule', function () {
    // A shop save unrelated to a lease must not be nagged with a rent-payment
    // message — that is why the "no schedule inputs" case returns null, not text.
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2020-01-01',
    ]);

    expect(callMaybeGenerateRentPayments($request, 106))->toBeNull();
});

it('leaves rentpay_note NULL so generated دفعات look like employee entries', function () {
    // Client: "اجعلها حالها من حال مدخلات الموظف". Every pre-existing row on both
    // live instances has a NULL note, so anything else marks the row as AI output.
    $request = Request::create('/dashboard/shop/upd-file', 'POST', [
        'rent_sdt' => '2020-01-01',
        'rent_edt' => '2025-01-01',
        'rent_sched_num' => 10,
        'rent_sched_rentval' => 40000.0,
        'rent_sched_value' => 20000.0,
        'rent_sched_freq' => 'semi-annual',
    ]);

    callMaybeGenerateRentPayments($request, 107);

    $notes = DB::table('shop_rentpay')->where('shop_id', 107)->pluck('rentpay_note');
    expect($notes)->toHaveCount(10);
    foreach ($notes as $note) {
        expect($note)->toBeNull();
    }
});

/*
 * Spec: attaching a contract to «تحميل صورة العقد» must itself generate the
 * دفعات. Before this, only the separate AI widget's hidden rent_sched_* fields
 * could do it, so the client's actual flow ("من نفس المحل لما يرفع العقد")
 * silently produced nothing.
 */

/** @return string|null */
function callRentPaymentsFromContractFile($shop_id, string $absPath, string $start = '', string $end = ''): ?string
{
    $controller = new ShopController();
    $method = new ReflectionMethod($controller, 'rentPaymentsFromContractFile');

    return $method->invoke($controller, $shop_id, $absPath, $start, $end);
}

/** Bind a fake extractor returning $payload, and a throwaway file to "read". */
function fakeContractFile(array $payload, ?Throwable $throw = null): string
{
    $path = tempnam(sys_get_temp_dir(), 'lease') . '.pdf';
    file_put_contents($path, '%PDF-1.5 fake');

    app()->bind(App\Services\ShopAiExtractor::class, function () use ($payload, $throw) {
        return new class($payload, $throw) extends App\Services\ShopAiExtractor {
            public function __construct(private array $payload, private ?Throwable $throw) {}
            public function extract(string $filePath, ?string $model = null): array
            {
                if ($this->throw) { throw $this->throw; }
                return $this->payload;
            }
        };
    });

    return $path;
}

it('generates دفعات from a lease contract that was just attached', function () {
    $path = fakeContractFile([
        'document_type' => 'lease',
        'issue_date' => '2020-01-01',
        'expiry_date' => '2025-01-01',
        'num_payments' => 10,
        'payment_value' => 20000.0,
        'rent_amount' => 40000.0,
        'payment_frequency' => 'semi-annual',
    ]);

    $outcome = callRentPaymentsFromContractFile(77, $path);

    expect(DB::table('shop_rentpay')->where('shop_id', 77)->count())->toBe(10)
        ->and($outcome)->toContain('تم إنشاء');
    @unlink($path);
});

it('does not generate دفعات when the attached document is not a lease', function () {
    $path = fakeContractFile([
        'document_type' => 'commercial_registration',
        'num_payments' => 4, 'payment_value' => 100.0, 'rent_amount' => 400.0,
    ]);

    $outcome = callRentPaymentsFromContractFile(78, $path);

    expect(DB::table('shop_rentpay')->where('shop_id', 78)->count())->toBe(0)
        ->and($outcome)->toBeNull();
    @unlink($path);
});

it('explains itself when the contract carries no readable schedule', function () {
    $path = fakeContractFile([
        'document_type' => 'lease',
        'issue_date' => '2020-01-01',
        'num_payments' => 0, 'payment_value' => null, 'rent_amount' => 0,
    ]);

    $outcome = callRentPaymentsFromContractFile(79, $path);

    expect(DB::table('shop_rentpay')->where('shop_id', 79)->count())->toBe(0)
        ->and($outcome)->toContain('لم يُعثر');
    @unlink($path);
});

it('survives an extraction failure without losing the shop save', function () {
    $path = fakeContractFile([], new RuntimeException('gemini timeout'));

    $outcome = callRentPaymentsFromContractFile(80, $path);

    expect(DB::table('shop_rentpay')->where('shop_id', 80)->count())->toBe(0)
        ->and($outcome)->toContain('تعذّرت قراءته');
    @unlink($path);
});

it('still refuses to duplicate when the shop already has دفعات', function () {
    DB::table('shop_rentpay')->insert([
        'shop_id' => 81, 'rentpay_dt' => '2024-01-01', 'rentpay_price' => 500,
        'rentpay_status' => 'unpaid',
    ]);
    $path = fakeContractFile([
        'document_type' => 'lease', 'issue_date' => '2020-01-01', 'expiry_date' => '2025-01-01',
        'num_payments' => 10, 'payment_value' => 20000.0, 'rent_amount' => 40000.0,
    ]);

    $outcome = callRentPaymentsFromContractFile(81, $path);

    expect(DB::table('shop_rentpay')->where('shop_id', 81)->count())->toBe(1)
        ->and($outcome)->toContain('لديه دفعات مسجّلة بالفعل');
    @unlink($path);
});
