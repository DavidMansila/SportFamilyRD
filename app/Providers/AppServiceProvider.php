<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;

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

        $this->registrarTransporteDeCorreo();
    }

    /**
     * Transporte de correo por la API HTTP de Brevo.
     *
     * Laravel trae drivers para SES, Postmark, Mailgun y Resend, pero no para
     * Brevo, asi que se registra aqui el puente oficial de Symfony.
     *
     * Por que por API y no por SMTP: desde el 26 de septiembre de 2025, los
     * servicios gratuitos de Render bloquean el trafico saliente a los puertos
     * 25, 465 y 587. Cualquier envio por SMTP -Gmail, el propio SMTP de Brevo,
     * el que sea- se queda esperando hasta que caduca la conexion y el correo
     * no sale. La API de Brevo va por HTTPS al 443, que no esta bloqueado.
     */
    private function registrarTransporteDeCorreo(): void
    {
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport($config['key'] ?? '');
        });
    }
}
