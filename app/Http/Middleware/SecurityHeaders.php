<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas.
 *
 * La aplicacion no emitia ninguna. En concreto:
 *
 *   - sin CSP, cualquier XSS que aparezca se explota sin friccion y puede
 *     exfiltrar el token de sessionStorage;
 *   - sin X-Frame-Options / frame-ancestors, la pagina es embebible en un
 *     iframe ajeno (clickjacking sobre acciones de administracion);
 *   - sin Referrer-Policy, la URL completa viaja a terceros;
 *   - sin HSTS, el primer acceso es degradable a HTTP.
 *
 * ---------------------------------------------------------------------------
 * SOBRE LA CSP: se emite en modo REPORT-ONLY por defecto.
 *
 * Una CSP mal calibrada rompe la pagina entera y en silencio. Esta app carga de
 * fuera: Font Awesome (cdnjs), fuentes de Bunny y Google, websockets de Pusher e
 * imagenes/realtime de Supabase. Report-Only deja que el navegador REPORTE las
 * violaciones sin bloquear nada, que es como hay que recorrer el SPA completo
 * antes de imponerla.
 *
 * Cuando el recorrido no reporte violaciones, poner CSP_ENFORCE=true en el
 * entorno y la misma politica pasa a bloquear de verdad.
 * ---------------------------------------------------------------------------
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=()'
        );

        // HSTS solo sobre HTTPS: mandarlo por HTTP plano no hace nada, y en
        // local (http://localhost) solo estorba.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        $csp = $this->politicaCsp();

        $response->headers->set(
            config('app.csp_enforce')
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only',
            $csp
        );

        return $response;
    }

    private function politicaCsp(): string
    {
        // 'unsafe-inline' en style-src es necesario mientras haya estilos en
        // linea: Vue los inyecta con :style y los componentes .vue con <style
        // scoped>. Quitarlo exige mover todo eso a hojas externas o a nonces.
        //
        // script-src NO lleva 'unsafe-inline': todo el JS va en los bundles que
        // compila Vite, asi que ahi si se puede ser estricto (que es justo donde
        // importa para XSS).
        // Indexadas por nombre y no por posicion: con una lista plana, agregar o
        // reordenar una directiva desplazaba los indices y la excepcion de
        // desarrollo terminaba anadida a la directiva equivocada.
        $directivas = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'"],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://cdnjs.cloudflare.com', 'https://fonts.bunny.net', 'https://fonts.googleapis.com'],
            'font-src' => ["'self'", 'data:', 'https://cdnjs.cloudflare.com', 'https://fonts.bunny.net', 'https://fonts.gstatic.com'],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
            'connect-src' => ["'self'", 'https://*.pusher.com', 'wss://*.pusher.com', 'https://*.supabase.co', 'wss://*.supabase.co'],
            'frame-ancestors' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"],
        ];

        // En desarrollo, Vite sirve TODOS los assets desde su propio puerto
        // (no solo el JS: tambien app.css y app.scss) y abre un websocket para
        // el hot-reload. Sin la excepcion en style-src, el recorrido local
        // reporta violaciones que en produccion no existen -ahi el CSS va
        // compilado dentro de public/build y se sirve desde 'self'-, y esos
        // falsos positivos hacen imposible distinguir los reportes reales.
        if (app()->environment('local')) {
            $vite = ['http://localhost:5173', 'http://127.0.0.1:5173'];

            $directivas['script-src'] = array_merge($directivas['script-src'], $vite);
            $directivas['style-src'] = array_merge($directivas['style-src'], $vite);
            $directivas['font-src'] = array_merge($directivas['font-src'], $vite);
            $directivas['connect-src'] = array_merge(
                $directivas['connect-src'],
                $vite,
                ['ws://localhost:5173', 'ws://127.0.0.1:5173']
            );
        }

        return implode('; ', array_map(
            fn($nombre, $valores) => $nombre . ' ' . implode(' ', $valores),
            array_keys($directivas),
            $directivas
        ));
    }
}
