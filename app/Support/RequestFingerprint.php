<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — "سجل تدقيق يتضمن اسم المستخدم، وتاريخ ووقت العملية،
 * والجهاز أو عنوان الـ IP".
 *
 * Single helper both audit trails (AuditLogger -> ai_audit_log, ActivityLogger
 * -> employee_activity_log) use to attach the caller's IP + device to a log row.
 *
 * Two properties this helper MUST keep, because both loggers swallow \Throwable
 * and a mistake here turns a partial audit gap into a total audit blackout:
 *
 *  1. SCHEMA-GUARDED — a key is only returned when the target table actually has
 *     that column, so an environment that has not run the migration keeps
 *     logging exactly as before instead of failing every insert on "column not
 *     found". The probe result is cached per (table, column) for the process,
 *     so this costs one schema query per column per request at most.
 *  2. CONTEXT-SAFE — AuditLogger is called from controllers, but also from
 *     services that may run outside an HTTP request (console/queue). Reading the
 *     request is wrapped so a missing/argv-built request yields nulls rather
 *     than throwing.
 */
class RequestFingerprint
{
    /** @var array<string,bool> "table.column" => exists */
    private static array $columnCache = [];

    /**
     * @return array<string,string|null> subset of ['ip' => ..., 'user_agent' => ...]
     *                                   limited to columns the table really has
     */
    public static function forTable(string $table): array
    {
        $out = [];

        if (self::hasColumn($table, 'ip')) {
            $out['ip'] = self::ip();
        }
        if (self::hasColumn($table, 'user_agent')) {
            $out['user_agent'] = self::userAgent();
        }

        return $out;
    }

    public static function ip(): ?string
    {
        return self::fromRequest(fn ($request) => $request->ip(), 45);
    }

    /** الجهاز — raw User-Agent, truncated to the column width. */
    public static function userAgent(): ?string
    {
        return self::fromRequest(fn ($request) => $request->userAgent(), 255);
    }

    private static function fromRequest(callable $read, int $maxLength): ?string
    {
        try {
            $request = request();
            if (! $request) {
                return null;
            }
            $value = $read($request);
            if ($value === null || $value === '') {
                return null;
            }

            return mb_substr((string) $value, 0, $maxLength);
        } catch (\Throwable $e) {
            // Never let fingerprinting break (or silently kill) an audit write.
            return null;
        }
    }

    private static function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }

        try {
            $exists = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            $exists = false;
        }

        return self::$columnCache[$key] = $exists;
    }

    /** Test seam — drops the memoised schema probes. */
    public static function flushColumnCache(): void
    {
        self::$columnCache = [];
    }
}
