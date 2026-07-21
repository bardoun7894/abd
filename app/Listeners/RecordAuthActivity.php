<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Spec 008 bundle 3 (activity-log) — captures login + logout, the two events
 * LogActivity deliberately skips (it only nets create/update/delete writes).
 *
 * Verified upstream for this codebase:
 *  - app/Http/Requests/Auth/LoginRequest::authenticate() calls Auth::attempt()
 *    -> fires Illuminate\Auth\Events\Login.
 *  - app/Http/Controllers/Auth/AuthenticatedSessionController::destroy() calls
 *    Auth::guard('web')->logout() -> fires Illuminate\Auth\Events\Logout.
 * No controller edits were needed to wire these up.
 */
class RecordAuthActivity
{
    public function handleLogin(Login $event): void
    {
        $userId = $event->user->id ?? null;

        ActivityLogger::log(
            ActivityLogger::LOGIN,
            'auth',
            $userId,
            'تسجيل دخول',
            [
                'user' => $userId,
                'route' => 'login',
                'method' => 'POST',
                'ip' => request()->ip(),
            ]
        );
    }

    public function handleLogout(Logout $event): void
    {
        $userId = $event->user->id ?? null;

        ActivityLogger::log(
            ActivityLogger::LOGOUT,
            'auth',
            $userId,
            'تسجيل خروج',
            [
                'user' => $userId,
                'route' => 'logout',
                'method' => 'POST',
                'ip' => request()->ip(),
            ]
        );
    }
}
