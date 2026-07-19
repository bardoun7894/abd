<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
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

    /** Registry keys explicitly marked as secrets. */
    private static function registrySecretKeys(): array
    {
        return ['gemini_api_key', 'sms_api_key'];
    }

    /**
     * Decide whether a setting key should be encrypted at rest.
     * - Known registry secrets (Gemini/SMS API keys).
     * - Custom keys whose name clearly indicates a secret (api_key, secret, token, password, private_key).
     */
    public static function isSecretKey(string $key): bool
    {
        if (in_array($key, self::registrySecretKeys(), true)) {
            return true;
        }

        $lower = strtolower($key);
        $secretTokens = ['api_key', 'secret', 'token', 'password', 'private_key'];
        foreach ($secretTokens as $token) {
            if (str_contains($lower, $token)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string,?string> all settings as key => value */
    public static function all(): array
    {
        // Prefer the cache, but never let a cache miss/unwritable-cache or a
        // missing table 500 the page — fall back to a direct DB read, then [].
        try {
            $values = Cache::remember(self::CACHE_KEY, 300, fn () => self::readFromDb());
        } catch (\Throwable $e) {
            $values = self::readFromDb();
        }

        foreach ($values as $k => $v) {
            $values[$k] = self::decryptValue($k, $v);
        }

        return $values;
    }

    private static function readFromDb(): array
    {
        try {
            return DB::table('app_settings')->pluck('svalue', 'skey')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Decrypt a stored value when the key is secret.
     * Transparent backward compat: if decryption fails, treat the value as a
     * legacy plaintext entry and return it as-is.
     */
    private static function decryptValue(string $key, ?string $value): ?string
    {
        if ($value === null || $value === '' || ! self::isSecretKey($key)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
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
        $stored = $value;
        if ($stored !== null && $stored !== '' && self::isSecretKey($key)) {
            $stored = Crypt::encryptString($stored);
        }

        try {
            DB::table('app_settings')->updateOrInsert(
                ['skey' => $key],
                ['svalue' => $stored, 'updated_at' => now(), 'created_at' => now()]
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
