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
        // Ejecutar cada dia
        $schedule->command('news:import')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/web.php');
    }
}
