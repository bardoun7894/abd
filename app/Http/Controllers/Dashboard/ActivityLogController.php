<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Spec 008 bundle 3 (activity-log) — admin-only read screen over the
 * system-wide activity_log table. GET-query filters (user/action/entity/date/
 * search) + Laravel pagination, mirroring SettingsController::aiUsage()'s
 * GET-filter pattern.
 *
 * Deliberately STRICT emp_job==1 — unlike SettingsController::guard() (which
 * bundle 2/ai-permissions relaxed to also admit function 213), this screen
 * stays full-admin-only by spec. Do not reuse SettingsController::guard().
 */
class ActivityLogController extends Controller
{
    private const ACTION_LABELS = [
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'login' => 'دخول',
        'logout' => 'خروج',
        'write' => 'إجراء',
    ];

    private function guard(): void
    {
        if ((int) (Auth::user()->emp_job ?? 0) !== 1) {
            abort(403, 'هذه الصفحة مخصّصة لمدير النظام فقط');
        }
    }

    public function index(Request $request)
    {
        $this->guard();

        $page_title = 'سجل نشاط الموظفين';

        $query = DB::table('activity_log')
            ->leftJoin('users', 'activity_log.user_id', '=', 'users.id')
            ->select('activity_log.*', 'users.name as user_name');

        if ($request->filled('user_id')) {
            $query->where('activity_log.user_id', $request->query('user_id'));
        }
        if ($request->filled('action')) {
            $query->where('activity_log.action', $request->query('action'));
        }
        if ($request->filled('entity_type')) {
            $query->where('activity_log.entity_type', $request->query('entity_type'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('activity_log.created_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('activity_log.created_at', '<=', $request->query('date_to'));
        }
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where('activity_log.summary', 'like', "%{$q}%");
        }

        $rows = $query->orderByDesc('activity_log.created_at')
            ->paginate(50)
            ->withQueryString();

        $users = DB::table('users')->orderBy('name')->get(['id', 'name']);
        $actions = DB::table('activity_log')->distinct()->orderBy('action')->pluck('action');
        $entities = DB::table('activity_log')->whereNotNull('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type');

        $filters = $request->only(['user_id', 'action', 'entity_type', 'date_from', 'date_to', 'q']);
        $actionLabels = self::ACTION_LABELS;

        return view('dashboard.activity_log.index', compact(
            'page_title', 'rows', 'users', 'actions', 'entities', 'filters', 'actionLabels'
        ));
    }
}
