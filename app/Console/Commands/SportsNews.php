<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class SportsNews extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ImportSportsNews::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('news:import')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
