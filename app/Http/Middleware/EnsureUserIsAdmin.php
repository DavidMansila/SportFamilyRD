<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a las cuentas de administracion.
 *
 * Por que un middleware y no la comprobacion dentro de cada metodo: hasta ahora
 * el patron
 *
 *     if ($request->user()->user_type !== 'admin') { return 403; }
 *
 * estaba copiado 29 veces en 10 controladores. Basta olvidarlo UNA vez para
 * abrir un agujero, y es exactamente lo que ocurrio dos veces en este proyecto:
 * el catalogo global de ajustes y la consulta de solicitudes de entrenamiento
 * se quedaron sin esa linea y cualquier cuenta podia usarlos.
 *
 * Ademas, escrito en el metodo, quien lee routes/api.php no tiene forma de
 * saber quien puede llamar a cada ruta sin abrir el controlador. Aqui la regla
 * se lee junto a la ruta que protege.
 *
 * Va SIEMPRE detras de 'auth:sanctum': sin sesion, quien responde es ese otro
 * middleware con un 401, que es la respuesta correcta para "no te has
 * identificado" -distinta de "te has identificado y no te toca"-.
 *
 * OJO con lo que NO cubre: las comprobaciones del tipo "el dueño del recurso o
 * un admin" (editar tu propio post, ver tus entrenamientos, cambiar tus datos)
 * NO son esto. Dependen del recurso concreto que se esta tocando, asi que
 * siguen viviendo dentro de su metodo y no se han movido.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
