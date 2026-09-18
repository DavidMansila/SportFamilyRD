<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('error_json')) {
    /**
     * Respuesta de error para el cliente, con el detalle SOLO en el log.
     *
     * Los catch de los controladores devolvian 'error' => $e->getMessage() en el
     * JSON. Un fallo de base de datos publicaba asi nombres de tablas y columnas,
     * fragmentos de SQL y rutas absolutas del contenedor -reconocimiento gratis
     * para un atacante-, y lo hacia SIEMPRE, con APP_DEBUG a false incluido.
     *
     * A cambio se devuelve un identificador corto que tambien queda escrito en el
     * log: quien reporta el fallo puede pasar ese codigo y se encuentra la traza
     * completa sin haber expuesto nada.
     */
    function error_json(\Throwable $e, string $mensaje, int $status = 500)
    {
        $incidencia = (string) Str::uuid();

        Log::error($mensaje, [
            'incidencia' => $incidencia,
            'excepcion' => $e::class,
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile() . ':' . $e->getLine(),
        ]);

        return response()->json([
            'message' => $mensaje,
            'incidencia' => $incidencia,
        ], $status);
    }
}

if (! function_exists('public_storage_url')) {
    // Construye la URL publica de un archivo del disco "public" sin importar
    // si ese disco es local (dev) o Supabase Storage/S3 (produccion), para no
    // tener url('storage/...') hardcodeado a la ruta del disco local regado
    // por los controladores.
    function public_storage_url(string $path): string
    {
        return Storage::disk('public')->url(ltrim($path, '/'));
    }
}

if (! function_exists('resolve_user_image')) {
    // Convierte el campo "image" (solo el nombre de archivo, ej. "avatar.jpg")
    // de un modelo User cargado como relacion (post->user, comment->user,
    // chat->user, training->user, etc.) en la URL publica completa, en el
    // mismo objeto. Sin esto, cualquier endpoint que devuelva un User anidado
    // "tal cual" manda solo el nombre de archivo, y el frontend terminaba
    // reconstruyendo el path el mismo asumiendo un disco local que ya no
    // existe en produccion (Supabase Storage). null si no tiene foto: el
    // frontend ya sabe mostrar el icono por defecto en ese caso.
    function resolve_user_image(?\App\Models\User $user): void
    {
        if (!$user) {
            return;
        }

        if (!$user->image) {
            $user->image = null;

            return;
        }

        // Ya resuelta: no se vuelve a prefijar.
        //
        // Esta funcion MUTA el modelo, y a la misma instancia de User se la
        // puede llamar mas de una vez en la misma peticion: en el foro, quien
        // escribe el post puede ser ademas autor de un comentario o de una
        // respuesta de ese mismo post, y el recorrido pasa por los tres. El
        // resultado era una URL con el prefijo repetido
        // (".../users/61/https://.../users/61/avatar.jpg"), que devuelve 404 y
        // dejaba el avatar en blanco en el foro.
        if (Str::startsWith($user->image, ['http://', 'https://', '/'])) {
            return;
        }

        $user->image = public_storage_url('users/' . $user->id . '/' . $user->image);
    }
}
