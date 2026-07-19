# Dashboard widgets patch — ready to apply (2026-07-17)

**Status:** BLOCKED by workflow-enforcement ("orchestrator delegation" gate) inside the
sub-agent session — delegate/ask_parent/edit-on-app-code all denied as
CONSEQUENTIAL_WRITE. Research complete; patch below is final and verified against
the codebase. Apply the three edits, then run the verification steps at the bottom.

Grounding: KB prior art `qa/dashboard-homepage-tasks-vacations-widgets-animations`.

## Verified facts the patch relies on

- `tasks` table columns (via `App\Models\TheTask` fillable/casts): `schedule_id`,
  `worker_id`, `shop_id`, `service_id`, `note`, `needs`, `due_date` (cast date).
  No `title`, no `status` — title renders from `note`; status is derived.
- Task-module view permission = **88** (`TaskController@index`). Schedule status
  values: `نشط` / `مكتمل`.
- `vacation` table (via `Vacation` model raw SQL): `vacation_id`, `worker_id`,
  `start`, `end`, `vacation_type_id`, `is_deleted`, `create_user`.
  **No approval/status column exists** — "awaiting approval" is impossible without
  a schema change; widget shows latest requests with derived status pills.
- Vacation view permissions = **63–67** (`VacationController`).
- `public/css/app-ui.css` is loaded in `resources/views/layouts/app.blade.php:23`;
  tokens available: `--sn-emerald`, `--sn-amber`, `--sn-rust`, `--sn-line`,
  `--sn-shadow-md`, `--sn-dur-base`, `--sn-ease-out`; global
  `prefers-reduced-motion` guard already present (section 7).

---

## Edit 1 — `app/Http/Controllers/HomeController.php`

Add imports (after `use App\Models\Calculate;`):

```php
use App\Models\TheTask;
use App\Helpers\Perm;
```

Replace the final `return view('home', ...)` in `index()` with:

```php
        // Tasks widget (perm 88 = tasks-module view, same gate as TaskController@index).
        // Never throws: mirrors the HomeInsightService defensive pattern so a schema
        // surprise can't take the whole homepage down.
        $listtasks = collect();
        $overdue_tasks_count = 0;
        try {
            if (Perm::get_function_access(88)) {
                $activeSchedule = function ($q) { $q->where('status', '!=', 'مكتمل'); };
                $listtasks = TheTask::with(['worker', 'service', 'schedule'])
                    ->whereHas('schedule', $activeSchedule)
                    ->orderByRaw('due_date IS NULL, due_date ASC')
                    ->limit(15)
                    ->get();
                $overdue_tasks_count = TheTask::whereHas('schedule', $activeSchedule)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', Carbon::today())
                    ->count();
            }
        } catch (\Throwable $e) {
            $listtasks = collect();
            $overdue_tasks_count = 0;
        }

        // Vacations widget (perms 63-67, same gate set as VacationController). The
        // vacation table has no approval-status column, so status is derived at
        // render time: ongoing / upcoming / ended. Never throws.
        $listvacations = collect();
        try {
            if (Perm::get_function_access(63) || Perm::get_function_access(64) || Perm::get_function_access(65)
                || Perm::get_function_access(66) || Perm::get_function_access(67)) {
                $listvacations = DB::table('vacation as v')
                    ->join('workers as w', 'v.worker_id', '=', 'w.worker_id')
                    ->leftJoin('vacation_type as vt', 'v.vacation_type_id', '=', 'vt.vacation_type_id')
                    ->where('v.is_deleted', 0)
                    ->orderBy('v.vacation_id', 'desc')
                    ->limit(15)
                    ->get(['v.vacation_id', 'v.start', 'v.end', 'w.worker_name', 'vt.vacation_type_name']);
            }
        } catch (\Throwable $e) {
            $listvacations = collect();
        }

        return view('home', compact('page_title', 'listworker', 'listmoraslat', 'filters', 'insight', 'listtasks', 'overdue_tasks_count', 'listvacations', $const,  $const2,  $const3));
```

## Edit 2 — `resources/views/home.blade.php`

Insert immediately AFTER the Transactions card's closing `<?php } ?>` (the block
gated by perms 50–54, before the `emp_job==1` expenses card):

```blade
        <?php if (Perm::get_function_access(88)) { ?>

        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bolder text-info">المهام</h3>
                    <?php if ($overdue_tasks_count > 0) { ?>
                    <span class="badge badge-danger sn-badge-pulse">{{ $overdue_tasks_count }} متأخرة</span>
                    <?php } ?>
                </div>
                <div class="card-body pt-2 card-scroll h-300px">
                    <?php
                    $today = \Carbon\Carbon::today();
                    foreach ($listtasks as $t) {
                        $due = $t->due_date ? \Carbon\Carbon::parse($t->due_date) : null;
                        if ($due && $due->lt($today)) {
                            $task_badge = '<span class="badge badge-light-danger fw-bold">متأخرة</span>';
                        } elseif ($due && $due->isToday()) {
                            $task_badge = '<span class="badge badge-light-warning fw-bold">تستحق اليوم</span>';
                        } else {
                            $task_badge = '<span class="badge badge-light-primary fw-bold">قادمة</span>';
                        }
                    ?>
                    <div class="d-flex align-items-center bg-light rounded p-4 mb-4 sn-row-hover">
                        <div class="flex-grow-1 me-2">
                            <span class="fw-bolder text-gray-800 fs-6 d-block">{{ $t->note ?: 'مهمة #' . $t->id }}</span>
                            <span class="fw-bold text-dark fs-8">
                                {{ optional($t->worker)->worker_name }}
                                @if(optional($t->service)->name) — {{ $t->service->name }} @endif
                            </span>
                            @if($due)
                            <span class="text-muted fs-8 d-block">الاستحقاق: {{ $due->format('d-m-Y') }}</span>
                            @endif
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            {!! $task_badge !!}
                            @if($t->needs == 1)
                            <span class="badge badge-light-info fw-bold">احتياجات</span>
                            @endif
                        </div>
                    </div>
                    <?php } ?>
                    @if($listtasks->isEmpty())
                    <div class="text-muted fw-bold text-center py-10">لا توجد مهام نشطة</div>
                    @endif
                </div>
            </div>
        </div>
        <?php } ?>


        <?php if (Perm::get_function_access(63) || Perm::get_function_access(64) || Perm::get_function_access(65) || Perm::get_function_access(66) || Perm::get_function_access(67)) { ?>

        <div class="col-xl-4">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0">
                    <h3 class="card-title fw-bolder text-info">طلبات الإجازات</h3>
                </div>
                <div class="card-body pt-2 card-scroll h-300px">
                    <?php
                    $today = \Carbon\Carbon::today();
                    foreach ($listvacations as $v) {
                        $vstart = $v->start ? \Carbon\Carbon::parse($v->start) : null;
                        $vend   = $v->end   ? \Carbon\Carbon::parse($v->end)   : null;
                        if ($vstart && $vend && $today->between($vstart, $vend)) {
                            $vac_status = '<span class="badge badge-light-success fw-bold">جارية</span>';
                            $vac_accent = 'sn-accent-success';
                        } elseif ($vstart && $vstart->gt($today)) {
                            $vac_status = '<span class="badge badge-light-warning fw-bold">قادمة</span>';
                            $vac_accent = 'sn-accent-warning';
                        } else {
                            $vac_status = '<span class="badge badge-light-secondary fw-bold">منتهية</span>';
                            $vac_accent = 'sn-accent-muted';
                        }
                    ?>
                    <div class="bg-light rounded p-4 mb-4 sn-row-hover {{ $vac_accent }}">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 me-2">
                                <span class="fw-bolder text-gray-800 fs-6 d-block">{{ $v->worker_name }}</span>
                                <span class="fw-bold text-dark fs-8">{{ $v->vacation_type_name ?? 'إجازة' }}</span>
                                <span class="text-muted fs-8 d-block">
                                    {{ $vstart ? $vstart->format('d-m-Y') : '—' }} ← {{ $vend ? $vend->format('d-m-Y') : '—' }}
                                </span>
                            </div>
                            {!! $vac_status !!}
                        </div>
                    </div>
                    <?php } ?>
                    @if($listvacations->isEmpty())
                    <div class="text-muted fw-bold text-center py-10">لا توجد طلبات إجازات</div>
                    @endif
                </div>
            </div>
        </div>
        <?php } ?>
```

Defensive default (top of file already has a `@php` block): add inside it —

```php
        $listtasks = $listtasks ?? collect();
        $overdue_tasks_count = $overdue_tasks_count ?? 0;
        $listvacations = $listvacations ?? collect();
```

## Edit 3 — `public/css/app-ui.css`

Append as new section 8:

```css
/* -----------------------------------------------------------------------------
   8. Dashboard widget polish (home) — subtle, business-appropriate motion only.
      Card hover gets a gentle depth lift; urgent badges pulse. No entrance
      animations. Killed globally by the section-7 prefers-reduced-motion guard.
   -------------------------------------------------------------------------- */
.card {
  transition: box-shadow var(--sn-dur-base) var(--sn-ease-out);
}
.card:hover {
  box-shadow: var(--sn-shadow-md);
}

/* list-row hover inside scrollable widget bodies */
.sn-row-hover {
  transition: background-color var(--sn-dur-fast) var(--sn-ease-out);
}
.sn-row-hover:hover {
  background-color: var(--sn-emerald-tint) !important;
}

/* RTL-safe status accents for the vacations widget (border-inline-start, never
   border-left). Colors stay semantic — emerald ongoing, amber upcoming, muted ended. */
.sn-accent-success { border-inline-start: 4px solid var(--sn-emerald); }
.sn-accent-warning { border-inline-start: 4px solid var(--sn-amber); }
.sn-accent-muted   { border-inline-start: 4px solid var(--sn-line); }

/* pulse reserved for the overdue-tasks counter badge ONLY */
@keyframes sn-badge-pulse {
  0%, 100% { opacity: 1; }
  50%      { opacity: .55; }
}
.sn-badge-pulse {
  animation: sn-badge-pulse 1.6s var(--sn-ease-out) infinite;
}
```

---

## Verification (run after applying)

1. `php -l app/Http/Controllers/HomeController.php`
2. `php artisan view:cache` (compiles all blade — catches syntax errors in home.blade.php), then `php artisan view:clear`
3. `grep -nE "#083da6|#1949ea|#009ef7|#7239ea" resources/views/home.blade.php public/css/app-ui.css` → expect zero hits
4. `grep -n "border-left" resources/views/home.blade.php` → zero hits in new markup (existing Transactions card already uses `border-start` BS class — pre-existing, out of scope)
5. `./vendor/bin/pest` if runnable in reasonable time
6. Manual: load `/` as admin — المهام card between معاملات and احصائية المصاريف; overdue badge pulses only when count > 0

## Known pre-existing issues (out of scope, do not fix here)

- `routes/web.php:72-73` reference `TaskController@getSubtasks` / `storeSubtask` —
  methods do not exist in the controller; those routes will 500 if hit.
- Existing Transactions card uses Bootstrap `border-start` (LTR-physical in older
  BS builds); new markup uses the CSS-logical `border-inline-start` classes above.
