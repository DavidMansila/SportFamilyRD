<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache de los endpoints publicos de solo lectura.
 *
 * POR QUE EXISTE
 * --------------
 * La base de datos vive en Supabase (us-east-2) y este servidor esta en RD.
 * Medido: abrir la conexion (TLS + autenticacion) cuesta ~500 ms, ANTES de la
 * primera consulta. Como Laravel conecta de forma perezosa, una peticion que se
 * resuelve entera desde el cache NO abre conexion y no paga esos 500 ms.
 *
 * No es teoria: /sports (cacheado) responde en ~0,23 s y /products (sin cachear)
 * en ~0,98 s, haciendo ambos una sola consulta. Esa diferencia es la conexion.
 *
 * INVALIDACION
 * ------------
 * Las claves se limpian desde eventos de modelo (ver el metodo booted() de
 * Product, Calendar, News y Post), no desde los controladores. Asi da igual por
 * donde entre la escritura -panel de admin, seeder, scraper o tinker-: el cache
 * se entera igual y no hay forma de olvidarse de un sitio.
 *
 * El TTL queda como red de seguridad por si algo escribe saltandose Eloquent
 * (una consulta cruda, o alguien tocando la tabla desde el panel de Supabase).
 */
class CacheDeContenido
{
    /** Claves que dependen de la tabla de productos. */
    public const PRODUCTOS = ['products-index', 'recent-products'];

    /** Claves que dependen de la tabla de eventos. */
    public const CALENDARIO = ['calendar-index', 'featured-events', 'home-stats'];

    /** Claves que dependen de la tabla de noticias. */
    public const NOTICIAS = ['news-index', 'recent-news'];

    /** Claves que dependen de los posts del foro (y de sus likes/comentarios). */
    public const FORO = ['popular-posts', 'home-stats'];

    /** Claves que dependen de la tabla de usuarios. */
    public const USUARIOS = ['home-stats'];

    /**
     * Cuanto vive cada cosa. Corto a proposito: con la invalidacion por eventos
     * el TTL casi nunca llega a vencer, y si vence no se pierde nada.
     */
    public const MINUTOS_CONTENIDO = 300;   // productos, noticias, calendario
    public const MINUTOS_RANKING = 60;      // posts populares: cambia con cada like

    public static function olvidar(array $claves): void
    {
        foreach ($claves as $clave) {
            Cache::forget($clave);
        }
    }
}
