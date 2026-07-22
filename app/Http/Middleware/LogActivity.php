<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Spec 008 bundle 3 (activity-log) — system-wide write-capture net. This is
 * the SINGLE net for create/update/delete activity in this codebase: writes
 * happen heavily via `DB::table(...)->insert/update/delete` (query builder),
 * NOT Eloquent models, so Eloquent model observers would MISS about half of
 * all writes. Do NOT add Eloquent observers on top of this middleware — every
 * Eloquent write would be double-logged.
 *
 * Terminable: handle() only snapshots the actor/request shape and calls
 * $next(); the actual decide+insert happens in terminate(), which Laravel
 * runs AFTER the response is sent — zero added latency.
 *
 * IMPORTANT: Laravel resolves a *fresh* middleware instance for terminate()
 * unless the middleware is bound as a container singleton (it isn't here —
 * it's a plain entry in the 'web' group). Instance properties set in handle()
 * would therefore be gone by the time terminate() runs. The snapshot is
 * stashed on $request->attributes instead, because $request is guaranteed to
 * be the SAME object instance across handle() and terminate().
 *
 * Decision is keyed on ROUTE NAME verb tokens, not HTTP method, because this
 * app's DataTables list endpoints (ajax_search_*, tbl, tbl_*, sel_*_list,
 * show_job_cat, ...) are POST *reads* fired on every page load and keystroke.
 * Logging them would bloat the table exactly as the spec feared.
 */
class LogActivity
{
    /** Route-name/URI tokens that mark a request as a READ/poll — never logged. */
    private const READ_TOKENS = [
        'ajax_search', 'tbl', 'search', 'status', 'print', 'export', 'views',
        'show', 'index', 'datatable', 'dtbl', 'list',
        // Notification/count polls fire on a timer on every page — pure noise.
        'notify', 'count', 'poll', 'ping', 'heartbeat', 'unseen', 'unread',
    ];

    /** Action → Arabic verb for the human-readable summary. */
    private const ACTION_AR = [
        'create' => 'إنشاء', 'update' => 'تعديل', 'delete' => 'حذف', 'write' => 'إجراء على',
    ];

    /** Entity token (first route segment) → Arabic noun. */
    private const ENTITY_AR = [
        'invoices' => 'الفواتير', 'purchase' => 'المشتريات', 'shop' => 'المحلات',
        'workers' => 'الموظفين', 'worker' => 'الموظفين', 'expense' => 'المصاريف',
        'financial' => 'المالية', 'accountings' => 'الحسابات', 'vacation' => 'الإجازات',
        'moraslat' => 'المراسلات', 'cashbox' => 'الصندوق', 'lease' => 'الإيجارات',
        'task' => 'المهام', 'vehicle' => 'المركبات', 'service' => 'الخدمات',
        'settings' => 'الإعدادات', 'constant' => 'الثوابت', 'permission' => 'الصلاحيات',
    ];

    /** Specific routes that deserve a precise Arabic phrase over the generic one. */
    private const ROUTE_LABELS = [
        'dashboard.invoices.bulk-push' => 'ترحيل جماعي للفواتير إلى المشتريات',
        'dashboard.invoices.push' => 'ترحيل فواتير دفعة إلى المشتريات',
        'dashboard.invoices.correct' => 'تعديل بيانات فاتورة',
        'dashboard.invoices.manual-entry' => 'إدخال بيانات فاتورة يدوياً',
        'dashboard.invoices.destroy' => 'حذف دفعة فواتير',
        'dashboard.invoices.store' => 'رفع دفعة فواتير للاستخراج',
        'dashboard.shop.rentpay.receipt' => 'تسجيل سند قبض إيجار',
        'dashboard.shop.rentpay.void' => 'إلغاء سند قبض إيجار',
    ];

    /** Route-name tokens that map to a delete action. */
    private const DELETE_TOKENS = ['del', 'delete', 'destroy', 'cancal', 'remove'];

    /** Route-name tokens that map to a create action. */
    private const CREATE_TOKENS = ['store', 'add', 'create', 'new', 'insert', 'save'];

    /** Route-name tokens that map to an update action. */
    private const UPDATE_TOKENS = ['upd', 'update', 'edit', 'change', 'toggle'];

    public function handle($request, Closure $next)
    {
        $route = $request->route();

        $request->attributes->set('_activity_snapshot', [
            'user_id' => Auth::check() ? Auth::id() : null,
            'ip' => $request->ip(),
            'route' => $route?->getName(),
            'method' => $request->method(),
            'entity_id' => $this->guessEntityId($request, $route),
        ]);

        return $next($request);
    }

    public function terminate($request, $response): void
    {
        $snap = $request->attributes->get('_activity_snapshot');
        if (! $snap) {
            return; // handle() never ran for this request (e.g. terminable but bypassed)
        }

        $method = strtoupper($snap['method'] ?? $request->method());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $routeName = $snap['route'];
        if (! $routeName) {
            return; // unnamed route — nothing reliable to classify or attribute
        }

        // Auth listener owns login/logout — avoid double-capture.
        if (in_array($routeName, ['login', 'logout'], true)) {
            return;
        }

        // Never log the activity-log screen itself.
        if (str_starts_with($routeName, 'dashboard.activity_log')) {
            return;
        }

        $haystack = strtolower($routeName . ' ' . $request->path());
        foreach (self::READ_TOKENS as $token) {
            if (str_contains($haystack, $token)) {
                return;
            }
        }

        $action = $this->resolveAction($haystack);
        $entityType = $this->resolveEntityType($routeName, $request);

        ActivityLogger::log(
            $action,
            $entityType,
            $snap['entity_id'],
            $this->arabicSummary($action, $entityType, $routeName, $snap['entity_id']),
            [
                'user' => $snap['user_id'],
                'route' => $routeName,
                'method' => $method,
                'ip' => $snap['ip'],
            ]
        );
    }

    private function resolveAction(string $haystack): string
    {
        foreach (self::DELETE_TOKENS as $token) {
            if (str_contains($haystack, $token)) {
                return ActivityLogger::DELETE;
            }
        }
        foreach (self::CREATE_TOKENS as $token) {
            if (str_contains($haystack, $token)) {
                return ActivityLogger::CREATE;
            }
        }
        foreach (self::UPDATE_TOKENS as $token) {
            if (str_contains($haystack, $token)) {
                return ActivityLogger::UPDATE;
            }
        }

        // Fail-open: an unmatched non-GET route is rare and cheap insurance
        // against an audit gap — known high-frequency reads are denylisted above.
        return ActivityLogger::WRITE;
    }

    /**
     * Build a human-readable ARABIC summary instead of the raw "write X via route".
     * Specific routes get a precise phrase; everything else gets "{verb} {الكيان}".
     */
    private function arabicSummary(string $action, string $entityType, string $routeName, ?int $entityId): string
    {
        if (isset(self::ROUTE_LABELS[$routeName])) {
            $label = self::ROUTE_LABELS[$routeName];

            return $entityId ? $label.' #'.$entityId : $label;
        }

        $verb = self::ACTION_AR[$action] ?? 'إجراء على';
        $noun = self::ENTITY_AR[$entityType] ?? $entityType;
        $summary = $verb.' '.$noun;

        return $entityId ? $summary.' #'.$entityId : $summary;
    }

    private function resolveEntityType(string $routeName, $request): string
    {
        if (str_starts_with($routeName, 'dashboard.')) {
            $rest = substr($routeName, strlen('dashboard.'));
            $segment = explode('.', $rest)[0] ?? null;
            if ($segment) {
                return $segment;
            }
        }

        $segments = array_values(array_filter(explode('/', $request->path())));

        return $segments[0] ?? 'unknown';
    }

    private function guessEntityId($request, $route): ?int
    {
        if ($route) {
            // Defense-in-depth: Route::parameter() throws LogicException on an
            // unbound route. Laravel binds the route before any middleware runs,
            // so this should never fire in production — but a throw here would
            // break every request, so never let it escape.
            try {
                foreach (['id', 'worker', 'shop', 'expense', 'purchase', 'batch', 'invoiceId', 'batchId'] as $param) {
                    $val = $route->parameter($param);
                    if (is_numeric($val)) {
                        return (int) $val;
                    }
                }
            } catch (\Throwable $e) {
                // fall through to request-input lookup below
            }
        }

        foreach (['id', 'worker_id', 'shop_id', 'expense_id', 'purchase_id', 'receipt_id'] as $field) {
            $val = $request->input($field);
            if (is_numeric($val)) {
                return (int) $val;
            }
        }

        return null;
    }
}
