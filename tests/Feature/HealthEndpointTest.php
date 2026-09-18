<?php

namespace Tests\Feature;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * GET /health: el ping que usa el monitor externo para que Render no duerma la
 * instancia.
 *
 * Lo unico que tiene que hacer es responder 200 lo mas barato posible. Estas
 * pruebas fijan ese "barato", que es facil de perder sin darse cuenta: basta
 * con mover la ruta a routes/web.php -donde entraria en el grupo 'web'- para
 * que cada ping arranque la sesion y, con SESSION_DRIVER=database, escriba en
 * la tabla 'sessions' cada pocos minutos, dia y noche.
 */
class HealthEndpointTest extends TestCase
{
    // Sin RefreshDatabase a proposito: si este endpoint necesitara base de
    // datos para responder, la prueba deberia fallar, no montarsela.

    public function test_responde_200_con_estado_ok(): void
    {
        $respuesta = $this->get('/health');

        $respuesta->assertOk();
        $respuesta->assertExactJson(['status' => 'ok']);
    }

    public function test_pide_que_no_se_cachee(): void
    {
        // Un 200 servido desde la cache de un proxy diria que la instancia
        // esta viva aunque estuviera caida.
        $this->get('/health')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_no_arranca_la_sesion(): void
    {
        $ruta = Route::getRoutes()->getByName('health');

        $this->assertNotNull($ruta, 'La ruta /health perdio su nombre o desaparecio.');
        $this->assertNotContains(
            StartSession::class,
            $ruta->gatherMiddleware(),
            'La ruta /health arrancaria sesion: con SESSION_DRIVER=database eso es '
            . 'una escritura en base de datos en cada ping. Debe quedarse fuera del '
            . 'grupo de middleware "web" (ver bootstrap/app.php).'
        );
    }

    public function test_no_hace_ninguna_consulta_a_la_base_de_datos(): void
    {
        $consultas = [];
        DB::listen(function ($consulta) use (&$consultas) {
            $consultas[] = $consulta->sql;
        });

        $this->get('/health')->assertOk();

        $this->assertSame(
            [],
            $consultas,
            'El ping de salud consulto la base de datos: ' . implode(' | ', $consultas)
        );
    }

    public function test_el_catch_all_del_spa_no_se_lo_come(): void
    {
        // El /up que trae Laravel devolvia el HTML de la aplicacion con un 200
        // porque la ruta comodin de routes/web.php se registra antes. Aqui se
        // comprueba que /health responde JSON de verdad.
        $this->get('/health')
            ->assertHeader('Content-Type', 'application/json');
    }

    /**
     * La exclusion del comodin del SPA es ".../health$" DENTRO de un lookahead
     * que ya empieza anclado: "^(?!...|health$).*". Es decir, esta anclada por
     * los dos extremos -el "^" del patron fija la posicion 0 y el "$" exige que
     * ahi se acabe la ruta-, asi que solo se libra la ruta exacta /health.
     *
     * Como eso no se lee de un vistazo, aqui queda fijado con casos en vez de
     * con una lectura del regex: cualquier ruta vecina tiene que seguir cayendo
     * en el SPA. Si alguien cambia la exclusion por un "health" suelto, estas
     * comprobaciones se caen.
     */
    #[DataProvider('rutasVecinas')]
    public function test_solo_se_libra_la_ruta_exacta(string $ruta): void
    {
        $this->get($ruta)
            ->assertOk()
            ->assertSee('<!DOCTYPE html>', false);
    }

    public static function rutasVecinas(): array
    {
        return [
            'sufijo' => ['/healthy'],
            'prefijo en otro segmento' => ['/foo/health'],
            'con segmento extra detras' => ['/health/extra'],
            'bajo api' => ['/api/health'],
        ];
    }

    /**
     * TEMPORAL (ver commit 5e942d1). Esta prueba existe solo mientras el
     * "Health Check Path" del servicio en el dashboard de Render siga
     * apuntando a /up. En cuanto se cambie ahi a /health, BORRAR esta prueba
     * -o darle la vuelta a la asercion-, porque deja de haber motivo para
     * sostener el comportamiento que fija.
     *
     * Y conviene borrarla, porque lo que fija no es deseable: /up responde 200
     * con el HTML COMPLETO del SPA y, al ir por el grupo de middleware 'web',
     * arranca sesion y escribe en la tabla 'sessions' en cada comprobacion.
     * Justo lo que /health existe para evitar. No es un healthcheck: es la
     * pagina de la aplicacion devolviendo 200 por casualidad, porque el comodin
     * de routes/web.php se registra antes que la ruta de salud de Laravel y se
     * la come. Nunca funciono de otra forma.
     *
     * Se sostiene igualmente hasta entonces porque el probe de Render decide si
     * un deploy esta sano antes de mandarle trafico: si /up dejara de dar 200,
     * los despliegues se quedarian colgados esperandolo.
     */
    public function test_up_sigue_devolviendo_200_para_el_probe_de_render(): void
    {
        $this->get('/up')->assertOk();
    }
}
