<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Support\Facades\Cache;

class SportController extends Controller
{
    /**
     * Catalogo de deportes del directorio.
     *
     * Son ~36 KB y tardaba ~2,5 s contra Supabase. El catalogo es de SOLO
     * LECTURA: no existe ninguna ruta que cree, edite o borre deportes (solo
     * cambia por seeder o tocando la base a mano), asi que cachearlo no puede
     * dejar datos rancios en el uso normal. 10 minutos.
     */
    public function index()
    {
        try {
            $sports = Cache::remember(
                'sports-index',
                600,
                fn() => Sport::orderBy('sort_order')->get()
            );
            return response()->json([
                'message' => 'Deportes obtenidos con éxito',
                'sports' => $sports
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los deportes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
