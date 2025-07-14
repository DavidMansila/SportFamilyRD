<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;

class ScrapCalendarController extends Controller
{
    // Guarda eventos del scrapping en la tabla calendars
    public function store(Request $request)
    {
        
        // dd($request->all());
        $events = $request->input('events', []);
        $created = [];

        // foreach ($events as $event) {
        //     $created[] = Calendar::create(
        //         [
        //             'Title' => $event['Title'] ?? 'Evento sin título',
        //             'date' => $event['fecha'] ?? date('Y-m-d'),
        //             'time' => $event['startTime'] ?? '00:00:00',
        //             'place' => $event['place'] ?? 'Sin lugar',
        //             'description' => $event['description'] ?? 'Sin descripción',
        //         ],
        //         [
        //             'price' => isset($event['price']) ? preg_replace('/[^\d.]/', '', $event['price']) : 0,
        //             'image' => $event['image'] ?? null,
        //             'quantity' => 100
        //         ]
        //     );
        // }

        foreach ($events as $event) {
            $created[] = Calendar::create([
                'Title' => $event['Title'] ?? 'Evento sin título',
                'date' => $event['fecha'] ?? date('Y-m-d'),
                'time' => $event['startTime'] ?? '00:00:00',
                'place' => $event['place'] ?? 'Sin lugar',
                'Description' => $event['description'] ?? 'Sin descripción',
                'price' => isset($event['price']) ? preg_replace('/[^\d.]/', '', $event['price']) : 0,
                'image' => $event['image'] ?? null,
                'quantity' => 100
            ]);
        }
        return response()->json(['created exitosamente' => $created]);
    }
}
