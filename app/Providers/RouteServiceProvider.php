<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Limitadores de peticiones de la API.
     *
     * El limitador 'api' lo consume $middleware->throttleApi() en
     * bootstrap/app.php, que lo antepone al grupo de middleware 'api'. Antes
     * este metodo estaba vacio y throttleApi() no se llamaba: el grupo 'api' de
     * Laravel 11 NO trae throttle por defecto (a diferencia de Laravel 10), asi
     * que las 60 rutas con auth:sanctum corrian sin ningun limite de tasa.
     *
     * Lo que despistaba era app/Http/Kernel.php, que si listaba 'throttle:api'
     * en el grupo 'api'... pero es un kernel con el formato de Laravel 10 y esta
     * version del framework no lo carga. Ese archivo ya se elimino.
     *
     * 120/minuto por usuario es deliberadamente holgado: el SPA dispara varias
     * peticiones por pantalla (el Home solo ya hace media docena) y el objetivo
     * de esta fase es cortar el abuso automatizado sin molestar a nadie. Se
     * aprieta despues, con trafico real medido.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Rutas que MANDAN CORREO. Aqui el limite no protege la base de datos
        // sino la reputacion del dominio: cada peticion dispara un email real a
        // un tercero, asi que el abuso convierte el servidor en un emisor de
        // spam y quema la cuota del proveedor SMTP.
        RateLimiter::for('correo', function (Request $request) {
            return Limit::perHour(5)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Registro de cuentas: por IP, porque todavia no hay usuario.
        RateLimiter::for('registro', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });
    }
}
