<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responde primero, ejecuta el comando despues.
 *
 * Los scrapers rondan los 28s y cron-job.org corta a los 30: el job se marcaba
 * como fallido aunque el servidor terminara bien. Con terminate() la respuesta
 * sale del proceso ANTES de empezar el trabajo, asi que el cron recibe su 202
 * en menos de un segundo y da el job por bueno, mientras el scraping sigue en
 * el mismo proceso PHP. Sin cola, sin worker, sin servicio extra.
 *
 * Uso: ->middleware('cron.background:news:import')
 *
 * A TENER EN CUENTA:
 *
 * - El cron ya no puede decirte si el scraping salio bien: cuando se envia el
 *   202 todavia no ha empezado. El resultado queda en los logs (canal por
 *   defecto) y, sobre todo, en las filas nuevas de la base de datos.
 *
 * - Produccion corre con "artisan serve" (ver Dockerfile), que es el servidor
 *   embebido de PHP y atiende una peticion a la vez: mientras el scraping
 *   corre aqui, el sitio no responde. Ya pasaba antes -la peticion sincrona
 *   bloqueaba los mismos 28s-, asi que esto no lo empeora, pero es el motivo
 *   de que los dos scrapers sigan en jobs separados y semanales de madrugada.
 */
class RunCommandAfterResponse
{
    public function handle(Request $request, Closure $next, string $comando): Response
    {
        // El comando viaja en el request y no en una propiedad de la clase: el
        // kernel construye una instancia NUEVA del middleware para llamar a
        // terminate() ($app->make()), y ademas sin los parametros de la ruta.
        // Cualquier estado guardado en $this durante handle() se pierde.
        $request->attributes->set('comando_diferido', $comando);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $comando = $request->attributes->get('comando_diferido');

        // terminate() corre pase lo que pase, tambien sobre las respuestas de
        // error. Sin este guardia, una peticion con token invalido devolveria
        // 403 y acto seguido dispararia el scraping igual.
        if (! $comando || $response->getStatusCode() !== 202) {
            return;
        }

        // El limite de PHP no tiene nada que ver con el de cron-job.org: la
        // respuesta ya salio, pero el proceso sigue vivo y max_execution_time
        // mataria el scraping a la mitad.
        @set_time_limit(300);

        // Que la desconexion del cliente (el cron ya colgo) no aborte el
        // proceso en cuanto PHP intente escribir algo.
        ignore_user_abort(true);

        // UN cron a la vez. Sin esto, cada peticion valida lanzaba su propia
        // importacion sin mirar si ya habia otra en marcha: el throttle de la
        // ruta permite 10 por minuto y por IP, y cada ciclo de scraping ocupa
        // el UNICO proceso PHP del contenedor hasta cinco minutos. Quien
        // tuviera el token -que ademas viaja en la URL y queda escrito en los
        // logs de acceso- podia dejar el sitio sin responder encadenando
        // importaciones, y de paso martillear los seis sitios que se scrapean.
        //
        // El bloqueo caduca solo a los 10 minutos: si el proceso muere a mitad
        // (se queda sin memoria, Render reinicia el contenedor), no deja el
        // cron bloqueado para siempre.
        $cerrojo = Cache::lock('cron-en-curso:' . $comando, 600);

        if (! $cerrojo->get()) {
            Log::warning("Cron en segundo plano: {$comando} ya estaba en curso, se ignora esta llamada");

            return;
        }

        $empezo = microtime(true);

        try {
            Artisan::call($comando);

            Log::info("Cron en segundo plano: {$comando} termino", [
                'segundos' => round(microtime(true) - $empezo, 1),
                'salida' => Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            // Nadie va a ver esta excepcion: la respuesta ya se envio y el cron
            // ya registro un exito. El log es el unico sitio donde queda.
            Log::error("Cron en segundo plano: {$comando} fallo", [
                'segundos' => round(microtime(true) - $empezo, 1),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            // Se suelta pase lo que pase: si el comando revienta, el siguiente
            // cron tiene que poder entrar sin esperar a que caduque el bloqueo.
            $cerrojo->release();
        }
    }
}
