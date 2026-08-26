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
