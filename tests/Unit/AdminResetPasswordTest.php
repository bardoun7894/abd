<?php

// Boots the app but touches no database: phpunit.xml deliberately points tests at
// an in-memory sqlite that has no `users` table, so anything asserting on real
// user rows has to be verified against a live instance instead (it was — see
// specs/_session/2026-07-29). What is pinned here is the contract that cannot
// regress silently: the route exists, it is POST-only, and it is gated.
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Route;

/**
 * WHY THIS ROUTE MATTERS
 * ----------------------
 * updstore() lists 'password' among its attribute labels but never validates or
 * saves it — only user CREATION ever set a password. The sole recovery path was
 * «نسيت كلمة المرور», which needs SMTP, and no working mail credentials exist on
 * either instance. Losing this route puts users back to a permanent lockout with
 * no in-app remedy (client منصور, 2026-07-28, on both systems).
 */
test('the admin password-reset route is registered as POST', function () {
    $route = Route::getRoutes()->getByName('dashboard.emps.reset_password');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('POST')
        ->and($route->methods())->not->toContain('GET')
        ->and($route->uri())->toBe('dashboard/emps/reset_password');
});

test('it is behind the auth middleware', function () {
    $route = Route::getRoutes()->getByName('dashboard.emps.reset_password');

    expect($route->gatherMiddleware())->toContain('auth');
});

test('it points at the controller action that hashes the password', function () {
    $route = Route::getRoutes()->getByName('dashboard.emps.reset_password');

    expect($route->getActionName())
        ->toBe('App\Http\Controllers\Dashboard\EmpsController@reset_password');

    // The action must exist — Route::resource taught us that a registered route
    // pointing at a missing method is a 500, not a 404.
    expect(method_exists(\App\Http\Controllers\Dashboard\EmpsController::class, 'reset_password'))
        ->toBeTrue();
});
