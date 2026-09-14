<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
|
| Estas tres tareas vivian en app/Console/Kernel.php, que es el formato de
| Laravel 10. Esta aplicacion arranca con Application::configure() en
| bootstrap/app.php (Laravel 11), que NO carga esa clase: el planificador
| estaba vacio y ninguna de las tres se ejecuto nunca.
|
|   $ php artisan schedule:list
|     INFO  No scheduled tasks have been defined.
|
| Consecuencia: las noticias deportivas y los eventos del calendario no se
| actualizaban solos, y las solicitudes de entrenamiento no expiraban. El cron
| externo que llama /api/internal/schedule-run cada minuto estaba disparando un
| planificador sin tareas.
|
| withoutOverlapping() importa aqui porque el cron externo llama cada minuto:
| si una importacion tarda mas que eso, no queremos dos corriendo a la vez
| contra el mismo sitio scrapeado y la misma tabla.
*/

Schedule::command('news:import')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onFailure(fn() => \Illuminate\Support\Facades\Log::error('Fallo news:import'));

Schedule::command('calendar:import')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onFailure(fn() => \Illuminate\Support\Facades\Log::error('Fallo calendar:import'));

Schedule::command('training:expire')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// Sanctum marca los tokens como caducados pero no borra las filas. Con
// 'expiration' ya configurado (config/sanctum.php), sin esta limpieza la tabla
// personal_access_tokens crece indefinidamente con tokens muertos.
Schedule::command('sanctum:prune-expired --hours=24')
    ->daily()
    ->withoutOverlapping();
