<?php

use App\Services\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Boot the Laravel app (facades + container + config) but NOT RefreshDatabase —
// this app's legacy tables aren't migration-managed, so migrate:fresh fails.
// We create just the app_settings table in beforeEach instead.
uses(Tests\TestCase::class);

/**
 * Spec 005 — Settings service: DB-backed API keys that override config,
 * with a safe fallback when the table is absent.
 */
beforeEach(function () {
    Cache::flush();
    if (! Schema::hasTable('app_settings')) {
        Schema::create('app_settings', function ($t) {
            $t->id();
            $t->string('skey')->unique();
            $t->text('svalue')->nullable();
            $t->timestamps();
        });
    }
    DB::table('app_settings')->truncate();
    Settings::forgetCache();
});

it('returns the default when a key is unset', function () {
    expect(Settings::get('nope', 'fallback'))->toBe('fallback');
});

it('stores and reads a value', function () {
    Settings::set('gemini_api_key', 'KEY_ABC');
    expect(Settings::get('gemini_api_key'))->toBe('KEY_ABC');
});

it('treats empty string as unset and returns the default', function () {
    Settings::set('sms_sender', '');
    expect(Settings::get('sms_sender', 'DEFAULT_SENDER'))->toBe('DEFAULT_SENDER');
});

it('overrides config values via applyToConfig', function () {
    config(['services.gemini.key' => 'env-key', 'zatca.vat_number' => '']);
    Settings::set('gemini_api_key', 'DB_KEY');
    Settings::set('zatca_vat_number', '300000000000003');

    Settings::applyToConfig();

    expect(config('services.gemini.key'))->toBe('DB_KEY')
        ->and(config('zatca.vat_number'))->toBe('300000000000003');
});

it('leaves config untouched when no DB value is set', function () {
    config(['services.gemini.default_model' => 'gemini-3.5-flash']);
    Settings::applyToConfig();
    expect(config('services.gemini.default_model'))->toBe('gemini-3.5-flash');
});
