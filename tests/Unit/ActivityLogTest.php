<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\ActivityLogController;
use App\Http\Middleware\LogActivity;
use App\Listeners\RecordAuthActivity;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 008 bundle 3 (activity-log) — mirrors the house pattern established by
 * bundle 1 (see tests/Unit/CashboxControllerTest.php, CashboxServiceTest.php):
 * isolated sqlite :memory:, hand-built schema mirroring the real migration,
 * mocked Auth facade, controllers/middleware instantiated directly rather
 * than through real HTTP — real feature HTTP tests can't cleanly boot this
 * Oracle-oriented app, and RefreshDatabase/migrate-against-real-DB is
 * explicitly forbidden by phpunit.xml's safety note.
 *
 * DEVIATION from the task instruction ("tests/Feature"): tests/Pest.php has
 * `uses(TestCase::class, RefreshDatabase::class)->in('Feature')` — EVERY file
 * placed under tests/Feature/ automatically gets RefreshDatabase, which would
 * migrate:fresh against whatever DB_CONNECTION/DB_DATABASE the environment
 * resolves (phpunit.xml only overrides DB_DATABASE, not DB_CONNECTION) —
 * exactly the hazard the phpunit.xml safety comment warns about, and exactly
 * why bundle 1 (cashbox) put its tests under tests/Unit/ instead. This file
 * follows that same precedent.
 */
beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('employee_activity_log', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('action', 30);
        $table->string('entity_type', 60)->nullable();
        $table->unsignedBigInteger('entity_id')->nullable();
        $table->text('summary')->nullable();
        $table->string('route', 150)->nullable();
        $table->string('method', 10)->nullable();
        $table->string('ip', 45)->nullable();
        $table->dateTime('created_at')->nullable();
    });

    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name')->nullable();
    });

    DB::table('users')->insert(['id' => 7, 'name' => 'موظف تجريبي']);
    DB::table('users')->insert(['id' => 42, 'name' => 'مدير النظام']);
});

/** Builds a Request carrying a named, unbound route (no controller resolution needed). */
function makeActivityRequest(string $method, string $uri, ?string $routeName, array $input = []): Request
{
    $request = Request::create($uri, $method, $input);

    if ($routeName !== null) {
        $route = new RoutingRoute([$method], $uri, ['as' => $routeName]);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);
    }

    return $request;
}

it('logs a genuine write: POST dashboard.workers.del_workers creates one row with action=delete, entity_type=workers, correct user', function () {
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(7);

    $mw = new LogActivity();
    $request = makeActivityRequest('POST', '/dashboard/workers/del_workers', 'dashboard.workers.del_workers', ['id' => 55]);

    $mw->handle($request, fn ($r) => response('ok'));
    $mw->terminate($request, response('ok'));

    expect(DB::table('employee_activity_log')->count())->toBe(1);
    $row = DB::table('employee_activity_log')->first();
    expect($row->action)->toBe('delete');
    expect($row->entity_type)->toBe('workers');
    expect((int) $row->user_id)->toBe(7);
});

it('skips reads: GET requests and read-POSTs (ajax_search_*, tbl, print, export, views) create zero rows', function () {
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(7);

    $mw = new LogActivity();

    $reads = [
        ['GET', '/dashboard/workers', 'dashboard.workers.index'],
        ['POST', '/dashboard/workers/ajax_search_workers', 'dashboard.workers.ajax_search_workers'],
        ['POST', '/dashboard/workers/tbl', 'dashboard.workers.tbl'],
        ['GET', '/dashboard/report/print_violation_pdf', 'dashboard.report.print_violation_pdf'],
        ['POST', '/dashboard/report/print_vacation_xlsx', 'dashboard.report.print_vacation_xlsx'],
        ['GET', '/dashboard/workers/views', 'dashboard.workers.views'],
    ];

    foreach ($reads as [$method, $uri, $name]) {
        $request = makeActivityRequest($method, $uri, $name);
        $mw->handle($request, fn ($r) => response('ok'));
        $mw->terminate($request, response('ok'));
    }

    expect(DB::table('employee_activity_log')->count())->toBe(0);
});

it('records login/logout via auth events, and the middleware does not double-capture the login/logout routes', function () {
    $user = (object) ['id' => 42];

    (new RecordAuthActivity())->handleLogin(new Login('web', $user, false));
    (new RecordAuthActivity())->handleLogout(new Logout('web', $user));

    expect(DB::table('employee_activity_log')->where('action', 'login')->count())->toBe(1);
    expect(DB::table('employee_activity_log')->where('action', 'logout')->count())->toBe(1);

    // Middleware must skip the login/logout routes even if it saw them (auth
    // listener owns those) — no additional rows from a middleware pass.
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);

    $mw = new LogActivity();
    foreach ([['POST', '/login', 'login'], ['POST', '/logout', 'logout']] as [$method, $uri, $name]) {
        $request = makeActivityRequest($method, $uri, $name);
        $mw->handle($request, fn ($r) => response('ok'));
        $mw->terminate($request, response('ok'));
    }

    expect(DB::table('employee_activity_log')->count())->toBe(2); // still just the 2 auth-event rows
});

it('snapshots the actor in handle(): terminate() uses the value captured then, not a fresh Auth read', function () {
    // Auth::id() must be read exactly once — inside handle(). If terminate()
    // incorrectly re-reads Auth (e.g. via fresh instance properties lost
    // across the terminable boundary) it would get the second queued value.
    Auth::shouldReceive('check')->once()->andReturn(true);
    Auth::shouldReceive('id')->once()->andReturn(99);

    $mw = new LogActivity();
    $request = makeActivityRequest('POST', '/dashboard/workers/upd_workers', 'dashboard.workers.upd_workers', ['id' => 3]);

    $mw->handle($request, fn ($r) => response('ok'));
    // Simulate the request having "ended unauthenticated" by the time terminate()
    // runs — the mocks above only permit ONE call to Auth::check()/id(), already
    // consumed by handle(). If terminate() tried to read Auth again, Mockery
    // would raise an unexpected-call failure and this test would error, not just
    // fail the assertion below.
    $mw->terminate($request, response('ok'));

    $row = DB::table('employee_activity_log')->first();
    expect((int) $row->user_id)->toBe(99);
});

it('never throws from ActivityLogger even when the underlying insert fails', function () {
    Schema::drop('employee_activity_log'); // force the insert to fail

    expect(fn () => ActivityLogger::log(ActivityLogger::CREATE, 'workers', 1, 'test'))
        ->not->toThrow(Throwable::class);
});

it('returns 403 for a non-admin (emp_job != 1) and 200 for an admin (emp_job == 1) on the activity-log screen', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 5, 'emp_job' => 0]);

    expect(fn () => (new ActivityLogController())->index(Request::create('/dashboard/activity-log', 'GET')))
        ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    Auth::swap(null);
    Auth::shouldReceive('user')->andReturn((object) ['id' => 42, 'emp_job' => 1, 'dark' => 0, 'name' => 'مدير النظام', 'email' => 'admin@example.com', 'role_id' => null]);

    DB::table('employee_activity_log')->insert([
        'user_id' => 7, 'action' => 'delete', 'entity_type' => 'workers',
        'entity_id' => 55, 'summary' => 'delete workers via dashboard.workers.del_workers',
        'route' => 'dashboard.workers.del_workers', 'method' => 'POST', 'ip' => '127.0.0.1',
        'created_at' => now(),
    ]);

    $view = (new ActivityLogController())->index(Request::create('/dashboard/activity-log', 'GET'));

    // Assert the controller assembled the right view + data rather than a full
    // ->render(), which would pull in layouts.app -> page_sidebar and every
    // Perm::/Auth:: call across the whole menu tree (out of scope here — see
    // tests/Unit/CashboxControllerTest.php, which renders only leaf templates
    // for the same reason).
    expect($view)->toBeInstanceOf(\Illuminate\View\View::class);
    expect($view->name())->toBe('dashboard.activity_log.index');
    expect($view->getData()['page_title'])->toBe('سجل نشاط الموظفين');
    expect($view->getData()['rows']->total())->toBe(1);
});

it('filters by user_id, action, entity_type and date range, and paginates', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 42, 'emp_job' => 1, 'dark' => 0, 'name' => 'مدير النظام', 'email' => 'admin@example.com', 'role_id' => null]);

    DB::table('employee_activity_log')->insert([
        ['user_id' => 7, 'action' => 'delete', 'entity_type' => 'workers', 'entity_id' => 1, 'summary' => 'a', 'created_at' => '2026-07-01 10:00:00'],
        ['user_id' => 42, 'action' => 'create', 'entity_type' => 'shop', 'entity_id' => 2, 'summary' => 'b', 'created_at' => '2026-07-10 10:00:00'],
        ['user_id' => 7, 'action' => 'update', 'entity_type' => 'workers', 'entity_id' => 1, 'summary' => 'c', 'created_at' => '2026-07-15 10:00:00'],
    ]);

    $controller = new ActivityLogController();

    $byUser = $controller->index(Request::create('/dashboard/activity-log', 'GET', ['user_id' => 7]));
    expect($byUser->getData()['rows']->total())->toBe(2);

    $byAction = $controller->index(Request::create('/dashboard/activity-log', 'GET', ['action' => 'create']));
    expect($byAction->getData()['rows']->total())->toBe(1);

    $byEntity = $controller->index(Request::create('/dashboard/activity-log', 'GET', ['entity_type' => 'shop']));
    expect($byEntity->getData()['rows']->total())->toBe(1);

    $byDate = $controller->index(Request::create('/dashboard/activity-log', 'GET', [
        'date_from' => '2026-07-05', 'date_to' => '2026-07-12',
    ]));
    expect($byDate->getData()['rows']->total())->toBe(1);

    $all = $controller->index(Request::create('/dashboard/activity-log', 'GET'));
    expect($all->getData()['rows'])->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($all->getData()['rows']->total())->toBe(3);
});

it('runs the create_activity_log migration cleanly and idempotently (re-run no-ops via hasTable)', function () {
    Schema::drop('employee_activity_log');

    require_once base_path('database/migrations/2026_07_20_000020_create_activity_log_table.php');

    (new CreateActivityLogTable())->up();
    (new CreateActivityLogTable())->up(); // second run must no-op, not throw

    expect(Schema::hasTable('employee_activity_log'))->toBeTrue();

    (new CreateActivityLogTable())->down();
    expect(Schema::hasTable('employee_activity_log'))->toBeFalse();
});
