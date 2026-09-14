<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;

class ScrapCalendarController extends Controller
{
    /**
     * Guarda en el calendario los eventos obtenidos por scraping.
     *
     * Esta ruta era PUBLICA y sin validacion: cualquiera podia insertar filas
     * ilimitadas en la tabla de eventos, que es la que alimenta el calendario y
     * los eventos destacados del Home. El throttle de la ruta limitaba las
     * peticiones, no los registros -una sola podia traer 100.000 eventos-, y el
     * campo 'price' entraba tal cual al carrito.
     *
     * Ahora exige sesion de admin (ver la ruta) y valida cada evento. El tope de
     * 200 por peticion es holgado para el scraping real (el sitio de origen
     * publica decenas, no miles) y acota el peor caso.
     */
    public function store(Request $request)
    {
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Los nombres de campo son los que manda el frontend en
        // CalendarioView.vue (Title/date/Description), no los que leia antes
        // este controlador ('fecha', 'description'): con las claves viejas cada
        // evento se guardaba con la fecha de hoy y "Sin descripcion".
        $datos = $request->validate([
            'events' => 'required|array|max:200',
            'events.*.Title' => 'required|string|max:255',
            'events.*.date' => 'required|date',
            'events.*.time' => 'nullable|date_format:H:i,H:i:s',
            'events.*.place' => 'nullable|string|max:255',
            'events.*.Description' => 'nullable|string|max:2000',
            'events.*.price' => 'nullable|numeric|min:0|max:1000000',
            'events.*.image' => 'nullable|url|max:2048',
            'events.*.quantity' => 'nullable|integer|min:1|max:100000',
        ]);

        $creados = [];

        foreach ($datos['events'] as $evento) {
            $creados[] = Calendar::create([
                'Title' => $evento['Title'],
                'date' => $evento['date'],
                'time' => $evento['time'] ?? '00:00:00',
                'place' => $evento['place'] ?? 'Sin lugar',
                'Description' => $evento['Description'] ?? '',
                'price' => $evento['price'] ?? 0,
                'image' => $evento['image'] ?? null,
                'quantity' => $evento['quantity'] ?? 100,
            ]);
        }

        return response()->json([
            'message' => count($creados) . ' eventos guardados',
            'events' => $creados,
        ], 201);
    }
}
