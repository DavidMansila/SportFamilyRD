<?php

namespace App\Http\Controllers;

use App\Models\Sport;

class SportController extends Controller
{
    public function index()
    {
        try {
            $sports = Sport::orderBy('sort_order')->get();
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
