<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
         protected $commands = [
     Commands\Yarab::class,


    ];
         protected function scheduleTimezone()
{
    return 'Asia/Riyadh';
}
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
           //  $schedule->command('app:yarab')->everyMinute();
               // $schedule->command('app:yarab')->hourly();
  //$schedule->command('testing:cron')->lastDayOfMonth('15:00')->runInBackground();
     //$schedule->command('testing:cron')->everyMinute()->runInBackground();
     // Every entry is ->call(Artisan::call(...)), NOT ->command(...). Hostinger
     // (noor-alsabah.com) disables proc_open, and ->command() spawns a Symfony
     // Process for each run — it threw "The Process class relies on proc_open"
     // on every tick while schedule.log still printed DONE, so nothing below ever
     // ran in production (client report 2026-09-05: worker invoices stopped).
     // ->call() executes in the schedule:run process itself, no subprocess.
     $schedule->call(fn () => Artisan::call('testing:cron'))
         ->name('testing:cron')->lastDayOfMonth('15:00')->withoutOverlapping();
     $schedule->call(fn () => Artisan::call('worker:cron'))
         ->name('worker:cron')->lastDayOfMonth('15:00')->withoutOverlapping();

     // Spec 003 FR-204 — daily lease due/expiry alert scan (in-app + email + SMS).
     $schedule->call(fn () => Artisan::call('leases:scan-alerts'))
         ->name('leases:scan-alerts')->dailyAt('06:00')->withoutOverlapping();

     // Recover AI extraction jobs left in `processing` by a crashed/killed queue worker.
     $schedule->call(fn () => Artisan::call('ai:recover-stale-jobs'))
         ->name('ai:recover-stale-jobs')->everyTenMinutes()->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
