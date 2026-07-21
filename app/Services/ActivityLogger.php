<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Spec 008 bundle 3 (activity-log) — best-effort, append-only insert helper.
 * Single choke point for all activity writes: the LogActivity middleware
 * (system-wide write net) and RecordAuthActivity listener (login/logout) both
 * funnel through here. Mirrors app/Services/AuditLogger.php exactly: wrapped
 * in try/catch(\Throwable) that SWALLOWS — auditing must never abort the
 * action that triggered it.
 */
class ActivityLogger
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const WRITE = 'write'; // fail-open bucket for unmatched non-GET routes

    /**
     * @param  array{user?:int|null,entity_id?:int|null,route?:string|null,method?:string|null,ip?:string|null}  $opts
     */
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $summary = null, array $opts = []): void
    {
        try {
            DB::table('employee_activity_log')->insert([
                'user_id' => $opts['user'] ?? null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId ?? ($opts['entity_id'] ?? null),
                'summary' => $summary,
                'route' => $opts['route'] ?? null,
                'method' => $opts['method'] ?? null,
                'ip' => $opts['ip'] ?? null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Swallow — the activity log is best-effort and must not abort the action.
        }
    }
}
