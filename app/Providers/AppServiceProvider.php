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
    }
}
