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

    // protected $middlewareGroups = [
    //     'web' => [
    //         // \App\Http\Middleware\EncryptCookies::class,
    //         \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    //         \Illuminate\Session\Middleware\StartSession::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //         // \App\Http\Middleware\VerifyCsrfToken::class, // Asegúrate que está presente
    //         \Illuminate\Routing\Middleware\SubstituteBindings::class,
    //     ],
    // ];

    protected $routeMiddleware = [
        'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
    ];

    protected $middleware = [
    // ...
    \Illuminate\Http\Middleware\HandleCors::class,
];
}
