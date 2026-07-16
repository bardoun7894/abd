<?php

use App\Models\AppNotification;
use App\Services\AlertDispatcher;
use App\Services\SmsClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * These tests are DB-light: instead of touching the real (Docker/MySQL) app
 * database, they spin up an isolated in-memory SQLite connection just for
 * app_notifications, matching the real migration's schema. No network calls
 * are made (Mail::fake() + SmsClient with unset env creds).
 */
beforeEach(function () {
    $this->originalDefaultConnection = Config::get('database.default');

    Config::set('database.connections.testing_sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    Config::set('database.default', 'testing_sqlite');
    DB::purge('testing_sqlite');

    Schema::connection('testing_sqlite')->create('app_notifications', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('type', 40)->nullable();
        $table->string('title')->nullable();
        $table->text('body')->nullable();
        $table->string('ref_type', 30)->nullable();
        $table->unsignedBigInteger('ref_id')->nullable();
        $table->boolean('is_read')->default(false);
        $table->boolean('sent_email')->default(false);
        $table->boolean('sent_sms')->default(false);
        $table->string('dedup_key')->nullable();
        $table->timestamps();
    });

    Mail::fake();

    // Make sure no real SMS credentials leak in from the shell environment.
    putenv('SMS_PROVIDER');
    putenv('SMS_API_KEY');
    putenv('SMS_SENDER');
    putenv('SMS_BASE_URL');
});

afterEach(function () {
    Schema::connection('testing_sqlite')->dropIfExists('app_notifications');
    DB::purge('testing_sqlite');
    Config::set('database.default', $this->originalDefaultConnection);
});

test('SmsClient no-ops and returns false when no provider credentials are configured', function () {
    $client = new SmsClient();

    $result = $client->send('+966500000000', 'test message');

    expect($result)->toBeFalse();
});

test('AlertDispatcher::send is idempotent for a repeated dedup_key', function () {
    AlertDispatcher::send(1, 'lease_due', 'تنبيه', 'العقد مستحق', [
        'dedup_key' => 'lease-1-2026-07',
    ]);

    AlertDispatcher::send(1, 'lease_due', 'تنبيه', 'العقد مستحق', [
        'dedup_key' => 'lease-1-2026-07',
    ]);

    expect(AppNotification::count())->toBe(1);
});

test('AlertDispatcher::send inserts an in-app notification row when no dedup_key clashes', function () {
    AlertDispatcher::send(2, 'invoice_review', 'فاتورة', 'يرجى المراجعة', [
        'ref_type' => 'invoice',
        'ref_id' => 55,
    ]);

    $row = AppNotification::first();

    expect($row)->not->toBeNull()
        ->and($row->user_id)->toBe(2)
        ->and($row->type)->toBe('invoice_review')
        ->and($row->ref_type)->toBe('invoice')
        ->and($row->ref_id)->toBe(55);
});
