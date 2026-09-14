<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Aborta si la configuracion esta cacheada.
     *
     * Un bootstrap/cache/config.php presente PISA las variables de entorno de
     * phpunit.xml. El efecto es silencioso y peligroso: la suite deja de correr
     * sobre SQLite en memoria y con el broadcasting desactivado, y pasa a usar
     * la base de datos REAL de Supabase y la cuenta REAL de Pusher.
     *
     * No es teorico: paso en esta misma sesion. Tras un "php artisan
     * config:cache", un test de chat empezo a devolver 500 porque el evento de
     * broadcasting intentaba salir contra Pusher de verdad.
     *
     * Peor todavia seria el caso contrario: una prueba que escribe o borra
     * datos pasando en verde contra produccion sin que nadie se entere. Por eso
     * esto para la suite en seco en vez de avisar.
     */
    protected function setUp(): void
    {
        // Ruta literal y no base_path(): esto corre ANTES de parent::setUp(),
        // o sea antes de que exista el contenedor de la aplicacion, asi que
        // los helpers de Laravel todavia no estan disponibles.
        if (file_exists(__DIR__ . '/../bootstrap/cache/config.php')) {
            $this->fail(
                'La configuracion esta cacheada y eso ANULA las variables de phpunit.xml: '
                . 'las pruebas correrian contra la base de datos y los servicios REALES. '
                . 'Ejecuta "php artisan config:clear" antes de las pruebas.'
            );
        }

        parent::setUp();
    }
}
