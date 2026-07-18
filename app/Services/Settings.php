<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Spec 005 — DB-backed application settings (admin-editable API keys and
 * config). Values live in the `app_settings` table and are edited from the
 * admin Settings screen. Services keep reading `config()`/`env()` as before —
 * applyToConfig() (called from AppServiceProvider::boot) transparently
 * overrides the relevant config values with any DB-set value, so the whole
 * app picks up a changed key with zero service-code changes.
 *
 * Safe by design: every DB read is wrapped so a missing table (fresh install,
 * mid-migration) falls back silently to env/config defaults.
 */
class Settings
{
    private const CACHE_KEY = 'app_settings.all';

    /** @return array<string,?string> all settings as key => value */
    public static function all(): array
    {
        // Prefer the cache, but never let a cache miss/unwritable-cache or a
        // missing table 500 the page — fall back to a direct DB read, then [].
        try {
            return Cache::remember(self::CACHE_KEY, 300, fn () => self::readFromDb());
        } catch (\Throwable $e) {
            return self::readFromDb();
        }
    }

    private static function readFromDb(): array
    {
        try {
            return DB::table('app_settings')->pluck('svalue', 'skey')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();
        $v = $all[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    public static function set(string $key, ?string $value): void
    {
        try {
            DB::table('app_settings')->updateOrInsert(
                ['skey' => $key],
                ['svalue' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        } finally {
            Cache::forget(self::CACHE_KEY);
        }
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Push any DB-set values over the matching config keys. Called once per
     * request from AppServiceProvider::boot(). Only overrides when a non-empty
     * value exists, so env/config defaults stand otherwise.
     */
    public static function applyToConfig(): void
    {
        $map = [
            'gemini_api_key' => 'services.gemini.key',
            'gemini_model' => 'services.gemini.default_model',
            'gemini_rescan_model' => 'services.gemini.rescan_model',
            'gemini_thinking' => 'services.gemini.thinking_level',
            'gemini_thinking_hard' => 'services.gemini.thinking_level_hard',
            'gemini_timeout' => 'services.gemini.timeout',
            'gemini_page_timeout' => 'services.gemini.page_timeout',
            'gemini_retries' => 'services.gemini.retries',
            'zatca_seller_name' => 'zatca.seller_name',
            'zatca_vat_number' => 'zatca.vat_number',
        ];
        $all = self::all();
        foreach ($map as $settingKey => $configKey) {
            $v = $all[$settingKey] ?? null;
            if ($v !== null && $v !== '') {
                config([$configKey => $v]);
            }
        }
    }
}
