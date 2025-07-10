<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ImportSportsNews::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('news:import')->dailyAt('08:00');
        $schedule->command('training:expire')->dailyAt('03:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/web.php');
    }
}
