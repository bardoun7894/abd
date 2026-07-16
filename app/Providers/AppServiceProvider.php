<?php

namespace App\Providers;

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
