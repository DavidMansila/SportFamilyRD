<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Deshabilitar completamente broadcasting en desarrollo
        // if (app()->environment('local')) {
        //     return;
        // }

        Broadcast::routes([
            'prefix' => 'api',
            'as' => 'api.broadcasting.',
            'middleware' => ['api', 'auth:sanctum','broadcast.auth'],
            'methods' => ['GET', 'POST']
        ]);

        // El require de channels.php se ejecuta en CADA peticion, y la primera
        // llamada a Broadcast::channel() instancia el driver de broadcasting.
        // Si las credenciales de Pusher faltan o no se pueden leer, el
        // constructor de Pusher lanza una excepcion... durante el arranque de
        // los service providers, o sea ANTES de llegar a ninguna ruta: el sitio
        // entero responde 500, no solo el chat.
        //
        // Verificado en local: con el cache de configuracion vacio, 4 de cada 30
        // peticiones concurrentes a /api/saved-news devolvian 500 por esto (la
        // lectura concurrente del .env devolvia PUSHER_APP_KEY nulo). En
        // produccion el Dockerfile hace config:cache y no pasa, pero que una
        // credencial de un servicio opcional pueda tumbar el sitio completo no
        // es un riesgo que valga la pena correr.
        //
        // Falla CERRADO: si los canales no llegan a registrarse, Broadcast::auth
        // deniega la suscripcion. Se pierde el tiempo real, no la autorizacion.
        try {
            require base_path('routes/channels.php');
        } catch (\Throwable $e) {
            Log::error('No se pudieron registrar los canales de broadcasting; el chat en tiempo real queda deshabilitado en esta peticion.', [
                'excepcion' => $e::class,
                'mensaje' => $e->getMessage(),
            ]);
        }
    }

    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
}
