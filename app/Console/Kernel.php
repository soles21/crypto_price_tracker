<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $interval = config('crypto.fetch_interval', 5);
        $schedule->command('crypto:fetch-prices')
                 ->cron("*/{$interval} * * * *")
                 ->withoutOverlapping()
                 ->onFailure(function () {
                     \Log::error('Failed to run crypto:fetch-prices command');
                 });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}