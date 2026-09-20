<?php

namespace Tests\Feature;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Tests\TestCase;

/**
 * Ningun evento puede encolarse: no hay quien lo saque de la cola.
 *
 * El proyecto tiene QUEUE_CONNECTION=database pero NO corre ningun worker; en
 * Render hay un unico proceso que atiende peticiones HTTP y nada mas. Un evento
 * que implemente ShouldBroadcast (a secas) se guarda en la tabla 'jobs' y se
 * queda ahi indefinidamente, sin error, sin aviso y sin llegar nunca al
 * navegador.
 *
 * Paso de verdad: App\Events\EmailVerified era el unico que no usaba la version
 * inmediata, y habia dos avisos atascados en produccion -del 17 y del 20 de
 * septiembre- mientras los tres eventos del chat funcionaban con normalidad.
 *
 * Esta prueba recorre la carpeta de eventos, asi que cubre tambien los que se
 * escriban en el futuro.
 */
class EventosEnVivoTest extends TestCase
{
    public function test_todos_los_eventos_se_emiten_sin_pasar_por_la_cola(): void
    {
        $directorio = app_path('Events');
        $encolados = [];
        $revisados = 0;

        foreach (glob($directorio . DIRECTORY_SEPARATOR . '*.php') as $archivo) {
            $clase = 'App\\Events\\' . basename($archivo, '.php');

            if (! class_exists($clase)) {
                continue;
            }

            $interfaces = class_implements($clase);

            // Solo interesan los que se difunden; los eventos internos que no
            // salen al navegador no tienen este problema.
            $seDifunde = isset($interfaces['Illuminate\Contracts\Broadcasting\ShouldBroadcast'])
                || isset($interfaces[ShouldBroadcastNow::class]);

            if ($seDifunde) {
                $revisados++;
            }

            if ($seDifunde && ! isset($interfaces[ShouldBroadcastNow::class])) {
                $encolados[] = $clase;
            }
        }

        // Sin esto, la prueba pasaria igual si el glob dejara de encontrar
        // archivos: verde por no haber mirado nada.
        $this->assertGreaterThanOrEqual(
            4,
            $revisados,
            "Solo se revisaron {$revisados} eventos que se difunden; esperaba al menos 4. "
                . '¿Cambio la ruta de app/Events?'
        );

        $this->assertSame(
            [],
            $encolados,
            "Estos eventos se encolarian y no hay worker que los procese: \n  "
                . implode("\n  ", $encolados)
                . "\nUsa ShouldBroadcastNow, como el resto."
        );
    }
}
