<?php

use Illuminate\Support\Facades\Storage;

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

        $user->image = $user->image
            ? public_storage_url('users/' . $user->id . '/' . $user->image)
            : null;
    }
}
