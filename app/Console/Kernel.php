<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
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
     $schedule->command('testing:cron')->lastDayOfMonth('15:00')->runInBackground();
     $schedule->command('worker:cron')->lastDayOfMonth('15:00')->runInBackground();


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
