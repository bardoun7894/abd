<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Append-only audit trail for AI documents (Spec 001 FR-006 + governance).
 * Records every read/extract/edit/approve/reject/reprocess/duplicate-override with
 * the actor and timestamp. Writes to the main-DB `ai_audit_log` table following the
 * house `*_history` convention. Never throws — auditing must not break the action.
 */
class AuditLogger
{
    public const EDIT = 'edit';
    public const APPROVE = 'approve';
    public const REJECT = 'reject';
    public const REPROCESS = 'reprocess';
    public const EXTRACT = 'extract';
    public const DUP_OVERRIDE = 'dup_override';
    public const READ = 'read';
    public const DELETE = 'delete';

    /**
     * @param  string  $documentType  'invoice' | 'lease'
     * @param  array{batch_id?:int,field?:string,old?:mixed,new?:mixed,note?:string,user?:int}  $opts
     */
    public static function log(string $documentType, ?int $documentId, string $action, array $opts = []): void
    {
        try {
            DB::table('ai_audit_log')->insert([
                'document_type' => $documentType,
                'document_id' => $documentId,
                'batch_id' => $opts['batch_id'] ?? null,
                'action' => $action,
                'field' => $opts['field'] ?? null,
                'old_value' => self::stringify($opts['old'] ?? null),
                'new_value' => self::stringify($opts['new'] ?? null),
                'change_user' => $opts['user'] ?? (Auth::check() ? Auth::id() : null),
                'change_at' => now(),
                'note' => $opts['note'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Swallow — the audit log is best-effort and must not abort the action.
        }
    }

    private static function stringify($v): ?string
    {
        if ($v === null) {
            return null;
        }

        return is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
    }
}
