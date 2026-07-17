<?php

namespace App\Support;

/**
 * Central Arabic labels + badge colors for the enum values that surface in the UI
 * (invoice/lease batch statuses and AI audit-log actions). One place so the السجل
 * page and the AI audit-log viewer render the same wording and colors.
 *
 * The DB stores raw English enums (see App\Services\InvoicePipeline / AuditLogger);
 * these were previously printed raw into an Arabic UI.
 */
class AuditLabels
{
    /** batch status => [arabic label, metronic badge color] */
    private const STATUS = [
        'pending'    => ['معلّقة',       'warning'],
        'processing' => ['قيد المعالجة', 'primary'],
        'done'       => ['مكتملة',       'success'],
        'failed'     => ['فشلت',         'danger'],
        'rejected'   => ['مرفوضة',       'danger'],
        'draft'      => ['مسودة',        'secondary'],
    ];

    /** audit action => [arabic label, metronic badge color] */
    private const ACTION = [
        'extract'      => ['استخراج',        'primary'],
        'edit'         => ['تعديل',          'info'],
        'approve'      => ['اعتماد',         'success'],
        'reject'       => ['رفض',            'danger'],
        'reprocess'    => ['إعادة معالجة',   'warning'],
        'dup_override' => ['تجاوز تكرار',    'warning'],
        'read'         => ['قراءة',          'secondary'],
    ];

    public static function statusLabel(?string $status): string
    {
        return self::STATUS[$status][0] ?? ($status ?: '—');
    }

    public static function statusColor(?string $status): string
    {
        return self::STATUS[$status][1] ?? 'secondary';
    }

    /** all statuses as value => label, for filter dropdowns */
    public static function statuses(): array
    {
        return array_map(fn ($v) => $v[0], self::STATUS);
    }

    public static function actionLabel(?string $action): string
    {
        return self::ACTION[$action][0] ?? ($action ?: '—');
    }

    public static function actionColor(?string $action): string
    {
        return self::ACTION[$action][1] ?? 'secondary';
    }

    public static function actions(): array
    {
        return array_map(fn ($v) => $v[0], self::ACTION);
    }
}
