<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // The app is Bootstrap 5 (Metronic), not Tailwind — Laravel 10 defaults the
        // paginator to Tailwind markup, which renders unstyled here. Use BS5 views.
        Paginator::useBootstrapFive();

        // تعريف ثوابت TCPDF
        if (!defined('K_PATH_IMAGES')) {
            define('K_PATH_IMAGES', public_path('assets/media/logos/'));
        }

        // Spec 005 — let admin-editable DB settings override API-key config
        // (Gemini/ZATCA). Wrapped so a missing table never breaks boot.
        try {
            \App\Services\Settings::applyToConfig();
        } catch (\Throwable $e) {
            // settings table not available yet — keep env/config defaults
        }
    }
}
