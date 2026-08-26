<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if ($this->app->environment('local')) {
            URL::forceScheme('http');
        }

        // Render (y la mayoria de PaaS) terminan el HTTPS en su proxy y le
        // reenvian la peticion al contenedor por HTTP interno. Sin esto,
        // Laravel genera URLs de assets/rutas en http:// (el navegador las
        // bloquea como "mixed content" en una pagina servida por https://).
        // trustProxies() en bootstrap/app.php ya deja pasar la cabecera
        // X-Forwarded-Proto; esto ademas fuerza https explicitamente.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
