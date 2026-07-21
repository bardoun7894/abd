<?php

// Spec 008 bundle 2 (ai-permissions). Boots the real Laravel app (facades +
// container + routes) but swaps the default connection to an isolated SQLite
// :memory: DB — this app's legacy permission tables (`permission`,
// `per_function`, `per_controller`) aren't migration-managed on the real
// MySQL DB reachable in dev/CI, so we mirror the existing test convention
// (see tests/Unit/LeaseControllerTest.php, tests/Unit/SettingsServiceTest.php):
// create just the tables each test needs, mock Auth (no `users` table
// round-trip needed since Perm only ever touches Auth::id()/Auth()->user()).
uses(Tests\TestCase::class);

use App\Helpers\Perm;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\LeaseController;
use App\Http\Controllers\Dashboard\PurchaseController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    // Real columns per EmpsController::updrole()'s writes to `permission`
    // (emp_id, role_id, function_id, is_role) — Perm::get_function_access()
    // reads emp_id + function_id.
    Schema::create('permission', function ($t) {
        $t->id();
        $t->unsignedBigInteger('emp_id');
        $t->unsignedBigInteger('role_id')->nullable();
        $t->unsignedBigInteger('function_id');
        $t->boolean('is_role')->nullable();
        $t->timestamps();
    });

    Schema::create('app_settings', function ($t) {
        $t->id();
        $t->string('skey')->unique();
        $t->text('svalue')->nullable();
        $t->timestamps();
    });

    Cache::flush();
    Settings::forgetCache();
});

function actingAsEmp(int $id, int $empJob): void
{
    // emp_name is unused by Perm's logic but is dereferenced unconditionally
    // (Perm::get_function_access / get_controll_access), so the stub object
    // needs the property or PHP raises a notice-turned-exception under this
    // app's error-reporting config.
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => $empJob, 'emp_name' => 'Test User']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

function grantFunction(int $empId, int $functionId): void
{
    DB::table('permission')->insert([
        'emp_id' => $empId,
        'function_id' => $functionId,
        'is_role' => 1,
    ]);
}

// ---------------------------------------------------------------------------
// Perm::ai_access() — the single source of truth every guard below builds on.
// ---------------------------------------------------------------------------

it('ai_access: admin (emp_job==1) bypasses every AI feature with zero granted functions', function () {
    actingAsEmp(1, 1);

    expect(Perm::ai_access(Perm::AI_LEASE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_PURCHASE_INVOICE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_SETTINGS))->toBeTruthy();
});

it('ai_access: non-admin with no granted functions is denied every AI feature', function () {
    actingAsEmp(2, 0);

    expect(Perm::ai_access(Perm::AI_LEASE))->toBeFalsy();
    expect(Perm::ai_access(Perm::AI_PURCHASE_INVOICE))->toBeFalsy();
    expect(Perm::ai_access(Perm::AI_SETTINGS))->toBeFalsy();
});

it('ai_access: master function 210 alone unlocks all four features (all-AI grant)', function () {
    actingAsEmp(3, 0);
    grantFunction(3, Perm::AI_MASTER);

    expect(Perm::ai_access(Perm::AI_LEASE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_PURCHASE_INVOICE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_SETTINGS))->toBeTruthy();
});

it('ai_access: a specific function (211) grants only that feature — proves the parent-100 leak is closed', function () {
    actingAsEmp(4, 0);
    grantFunction(4, Perm::AI_LEASE);

    expect(Perm::ai_access(Perm::AI_LEASE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_PURCHASE_INVOICE))->toBeFalsy();
    expect(Perm::ai_access(Perm::AI_SETTINGS))->toBeFalsy();
});

it('ai_access: function 212 grants only purchase-invoice reading', function () {
    actingAsEmp(5, 0);
    grantFunction(5, Perm::AI_PURCHASE_INVOICE);

    expect(Perm::ai_access(Perm::AI_PURCHASE_INVOICE))->toBeTruthy();
    expect(Perm::ai_access(Perm::AI_LEASE))->toBeFalsy();
    expect(Perm::ai_access(Perm::AI_SETTINGS))->toBeFalsy();
});

// ---------------------------------------------------------------------------
// LeaseController / InvoiceController — per-feature guard replacing the old
// ishaveaccess:100 group gate. Invokes the exact middleware closures the
// router would run for a given action (via ControllerDispatcher::getMiddleware,
// the same resolution Laravel itself uses) rather than re-implementing the
// only/except split by hand.
// ---------------------------------------------------------------------------

function runControllerMiddleware($controller, string $method, Request $request)
{
    $dispatcher = new ControllerDispatcher(app());
    $stack = $dispatcher->getMiddleware($controller, $method);

    expect($stack)->not->toBeEmpty("no ai-permissions middleware resolved for {$method}() — guard missing?");

    $next = fn ($req) => response()->json(['status' => true, 'reached_action' => true]);
    foreach (array_reverse($stack) as $middleware) {
        $next = fn ($req) => $middleware($req, $next);
    }

    return $next($request);
}

it('LeaseController: full-page GET (index) redirects to show_not_allow when function 211 is missing', function () {
    actingAsEmp(10, 0);
    $response = runControllerMiddleware(new LeaseController(), 'index', Request::create('/dashboard/leases'));

    expect($response->isRedirect(route('show_not_allow')))->toBeTrue();
});

it('LeaseController: full-page GET (index) passes through once function 211 is granted', function () {
    actingAsEmp(11, 0);
    grantFunction(11, Perm::AI_LEASE);
    $response = runControllerMiddleware(new LeaseController(), 'index', Request::create('/dashboard/leases'));

    expect($response->getData(true)['reached_action'] ?? null)->toBeTrue();
});

it('LeaseController: JSON action (store) returns a 403 JSON body — never a redirect — when denied', function () {
    actingAsEmp(12, 0);
    $response = runControllerMiddleware(new LeaseController(), 'store', Request::create('/dashboard/leases', 'POST'));

    expect($response->status())->toBe(403);
    $payload = $response->getData(true);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toBeString()->not->toBeEmpty();
});

it('LeaseController: purchase-invoice-only grant (212) does NOT unlock leases — proves the leak stays closed', function () {
    actingAsEmp(13, 0);
    grantFunction(13, Perm::AI_PURCHASE_INVOICE);
    $response = runControllerMiddleware(new LeaseController(), 'index', Request::create('/dashboard/leases'));

    expect($response->isRedirect(route('show_not_allow')))->toBeTrue();
});

it('InvoiceController: full-page GET (index) redirects to show_not_allow when function 212 is missing', function () {
    actingAsEmp(20, 0);
    $response = runControllerMiddleware(new InvoiceController(), 'index', Request::create('/dashboard/invoices'));

    expect($response->isRedirect(route('show_not_allow')))->toBeTrue();
});

it('InvoiceController: JSON action (pushToPurchase) returns a 403 JSON body when denied', function () {
    actingAsEmp(21, 0);
    $response = runControllerMiddleware(new InvoiceController(), 'pushToPurchase', Request::create('/dashboard/invoices/1/push', 'POST'));

    expect($response->status())->toBe(403);
    expect($response->getData(true)['status'])->toBeFalse();
});

it('InvoiceController: lease-only grant (211) does NOT unlock invoices — proves the leak stays closed', function () {
    actingAsEmp(22, 0);
    grantFunction(22, Perm::AI_LEASE);
    $response = runControllerMiddleware(new InvoiceController(), 'index', Request::create('/dashboard/invoices'));

    expect($response->isRedirect(route('show_not_allow')))->toBeTrue();
});

it('LeaseController + InvoiceController: master grant (210) unlocks both', function () {
    actingAsEmp(23, 0);
    grantFunction(23, Perm::AI_MASTER);

    $leaseResp = runControllerMiddleware(new LeaseController(), 'index', Request::create('/dashboard/leases'));
    $invoiceResp = runControllerMiddleware(new InvoiceController(), 'index', Request::create('/dashboard/invoices'));

    expect($leaseResp->getData(true)['reached_action'] ?? null)->toBeTrue();
    expect($invoiceResp->getData(true)['reached_action'] ?? null)->toBeTrue();
});

it('LeaseController + InvoiceController: admin bypasses both with zero grants', function () {
    actingAsEmp(24, 1);

    $leaseResp = runControllerMiddleware(new LeaseController(), 'index', Request::create('/dashboard/leases'));
    $invoiceResp = runControllerMiddleware(new InvoiceController(), 'index', Request::create('/dashboard/invoices'));

    expect($leaseResp->getData(true)['reached_action'] ?? null)->toBeTrue();
    expect($invoiceResp->getData(true)['reached_action'] ?? null)->toBeTrue();
});

// ---------------------------------------------------------------------------
// PurchaseController::aiExtract — inline guard (whole controller stays under
// ishaveaccess:9; only this one AI action needs 212/master).
// ---------------------------------------------------------------------------

it('PurchaseController::aiExtract returns a 403 JSON body when function 212 is missing', function () {
    actingAsEmp(30, 0);

    $response = (new PurchaseController())->aiExtract(Request::create('/dashboard/purchase/ai-extract', 'POST'));

    expect($response->status())->toBe(403);
    $payload = $response->getData(true);
    expect($payload['status'])->toBeFalse();
    expect($payload['message_out'])->toContain('صلاحية');
});

it('PurchaseController::aiExtract passes the permission guard once function 212 is granted (fails later on missing file, not on 403)', function () {
    actingAsEmp(31, 0);
    grantFunction(31, Perm::AI_PURCHASE_INVOICE);

    // Past the permission guard, the very next line is $request->validate(['invoice' => 'required|...']).
    // An empty request throwing a ValidationException (not a 403) proves the guard let it through.
    expect(fn () => (new PurchaseController())->aiExtract(Request::create('/dashboard/purchase/ai-extract', 'POST')))
        ->toThrow(ValidationException::class);
});

// ---------------------------------------------------------------------------
// SettingsController — guard() relaxed to admit function 213 (or master 210),
// but ONLY for the Gemini group: index() filters the registry, update()
// enforces a server-side gemini_*-only allowlist (load-bearing — UI filtering
// alone would leak SMS/ZATCA secrets to a delegate who crafts the POST body).
// ---------------------------------------------------------------------------

it('SettingsController::guard denies a non-admin with no AI-settings function', function () {
    actingAsEmp(40, 0);

    expect(fn () => (new SettingsController())->index())->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

it('SettingsController::index renders every group for a full admin', function () {
    actingAsEmp(41, 1);

    $view = (new SettingsController())->index();
    $registry = $view->getData()['registry'];

    expect(array_keys($registry))->toContain('الرسائل النصية (SMS)', 'الفوترة الإلكترونية (ZATCA)', 'الذكاء الاصطناعي (Gemini)');
});

it('SettingsController::index filters SMS/ZATCA groups and custom keys away from a 213-only delegate', function () {
    Settings::set('some_custom_leak', 'should-not-be-visible');

    actingAsEmp(42, 0);
    grantFunction(42, Perm::AI_SETTINGS);

    $view = (new SettingsController())->index();
    $data = $view->getData();
    $registry = $data['registry'];

    expect(array_keys($registry))->toBe(['الذكاء الاصطناعي (Gemini)']);
    expect($data['custom'])->toBe([]);
});

it('SettingsController::update — admin can still write an SMS secret (no regression)', function () {
    actingAsEmp(43, 1);

    (new SettingsController())->update(Request::create('/dashboard/settings/update', 'POST', [
        'setting_sms_api_key' => 'ADMIN_SMS_SECRET',
        'setting_gemini_model' => 'gemini-flash-lite-latest',
    ]));

    expect(Settings::get('sms_api_key'))->toBe('ADMIN_SMS_SECRET');
    expect(Settings::get('gemini_model'))->toBe('gemini-flash-lite-latest');
});

it('SettingsController::update — a 213-only delegate updating gemini_model is accepted while a crafted sms_api_key field is silently ignored', function () {
    actingAsEmp(44, 0);
    grantFunction(44, Perm::AI_SETTINGS);

    (new SettingsController())->update(Request::create('/dashboard/settings/update', 'POST', [
        // Field the delegate's filtered form would never render, but a crafted
        // POST body could still send — this is exactly what update() must reject.
        'setting_sms_api_key' => 'LEAKED_SMS_SECRET',
        'setting_gemini_model' => 'gemini-flash-lite-latest',
    ]));

    expect(Settings::get('gemini_model'))->toBe('gemini-flash-lite-latest');
    expect(Settings::get('sms_api_key'))->toBeNull();
});

it('SettingsController::update — a 213-only delegate cannot smuggle a secret via the custom_key/custom_value rows', function () {
    actingAsEmp(45, 0);
    grantFunction(45, Perm::AI_SETTINGS);

    (new SettingsController())->update(Request::create('/dashboard/settings/update', 'POST', [
        'custom_key' => ['zatca_backdoor'],
        'custom_value' => ['leaked'],
    ]));

    expect(Settings::get('zatca_backdoor'))->toBeNull();
});
