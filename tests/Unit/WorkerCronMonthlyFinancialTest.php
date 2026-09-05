<?php

// Client report 2026-09-05 «سابقاً كان النظام تلقائياً بيصدر فواتير في مصاريف
// العمال، حالاً ما يصدر شيء». Three stacked faults: (1) the host disables
// proc_open so Laravel's scheduler could never spawn `worker:cron`; (2) even
// launched, WrokerCron null-derefed on a month with no payments_month row and on
// Auth::user() (no user in a CLI); (3) the «الشهر الجديد» button was hidden when
// payments_month was empty, so صباح النور had no manual path either.
// These pin (2) and the Kernel side of (1); (3) is a blade-level assertion.
uses(Tests\TestCase::class);

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['financial', 'workers', 'payments_month'] as $t) {
        Schema::dropIfExists($t);
    }
    Schema::create('workers', function ($table) {
        $table->increments('worker_id');
        $table->string('worker_name')->nullable();
    });
    Schema::create('payments_month', function ($table) {
        $table->increments('payments_month_id');
        $table->integer('payments_month_m')->nullable();
        $table->integer('payments_month_y')->nullable();
        $table->integer('payments_month_val')->nullable();
    });
    Schema::create('financial', function ($table) {
        $table->increments('financial_id');
        $table->unsignedBigInteger('worker_id')->nullable();
        $table->string('financial_month_desc', 10)->nullable();
        $table->integer('financial_month_y')->nullable();
        $table->integer('financial_month_m')->nullable();
        $table->integer('financial_month_val')->nullable();
        $table->string('note', 5000)->nullable();
        $table->unsignedBigInteger('create_user')->nullable();
        $table->dateTime('created_at')->nullable();
    });
    DB::table('workers')->insert([
        ['worker_name' => 'A'],
        ['worker_name' => 'B'],
        ['worker_name' => 'C'],
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates one row per worker from the CLI with no payments_month row and no auth user', function () {
    // صباح النور shape: payments_month empty, run from cron (Auth::user() === null).
    Carbon::setTestNow('2026-09-05 10:00:00');

    $exit = Artisan::call('worker:cron');

    expect($exit)->toBe(0);
    $rows = DB::table('financial')->where('financial_month_desc', '09-2026')->get();
    expect($rows)->toHaveCount(3);
    expect($rows->pluck('financial_month_val')->unique()->all())->toBe([500]);
    expect($rows->pluck('create_user')->unique()->all())->toBe([null]);
});

it('uses the payments_month amount when the month has one', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');
    DB::table('payments_month')->insert(['payments_month_m' => 9, 'payments_month_y' => 2026, 'payments_month_val' => 750]);

    Artisan::call('worker:cron');

    expect(DB::table('financial')->pluck('financial_month_val')->unique()->all())->toBe([750]);
});

it('is idempotent — a second run in the same month adds nothing', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Artisan::call('worker:cron');
    Artisan::call('worker:cron');

    expect(DB::table('financial')->count())->toBe(3);
});

it('schedules the monthly generators as in-process calls, not proc_open subprocesses', function () {
    // Hostinger disables proc_open; Event::execute() (used by ->command()) needs
    // it, while CallbackEvent runs in-process. Anything still registered via
    // ->command() is dead on that host.
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
    (fn () => $this->schedule($schedule))->call($kernel);

    $events = $schedule->events();
    expect($events)->not->toBeEmpty();
    foreach ($events as $event) {
        expect($event)->toBeInstanceOf(\Illuminate\Console\Scheduling\CallbackEvent::class);
    }
    $descriptions = array_map(fn ($e) => (string) $e->description, $events);
    expect(implode(' ', $descriptions))
        ->toContain('worker:cron')
        ->toContain('testing:cron')
        ->toContain('leases:scan-alerts')
        ->toContain('ai:recover-stale-jobs');
});

it('always renders the «الشهر الجديد» button, even with an empty payments_month', function () {
    $blade = file_get_contents(resource_path('views/dashboard/financial/view.blade.php'));
    $pos = mb_strpos($blade, 'financial.cronadd');
    expect($pos)->not->toBeFalse();
    $before = mb_substr($blade, max(0, $pos - 400), 400);
    expect($before)->not->toContain("payments_month')->get()) > 0");
});
