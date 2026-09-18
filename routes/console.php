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

// news:import y calendar:import ya no se programan aqui.
//
// Estuvieron puestos como tareas diarias (08:00 y 09:00) y nunca llegaron a
// importar nada: el planificador solo se despierta si algo llama a
// "schedule:run" cada minuto, y ese disparador -el cron externo contra
// /api/internal/schedule-run- no estaba operativo. La prueba estaba en la base
// de datos: las 105 filas de NewsScrapping y las 13 de calendars tenian todas
// la misma fecha de creacion, la de la ultima ejecucion a mano.
//
// Ahora cada scraper tiene su propio job en cron-job.org, que llama su URL
// directamente y no depende de este planificador ni de que la peticion de ese
// minuto exacto llegue:
//
//   GET /api/internal/cron/news      -> news:import
//   GET /api/internal/cron/calendar  -> calendar:import
//
// Las dos rutas estan en routes/api.php. Si algun dia se arregla el disparador
// de schedule:run, NO volver a anadirlos aqui: correrian dos veces.
//
// Abajo quedan solo las tareas que no dependen de scraping externo.

Schedule::command('training:expire')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// Sanctum marca los tokens como caducados pero no borra las filas. Con
// 'expiration' ya configurado (config/sanctum.php), sin esta limpieza la tabla
// personal_access_tokens crece indefinidamente con tokens muertos.
Schedule::command('sanctum:prune-expired --hours=24')
    ->daily()
    ->withoutOverlapping();
