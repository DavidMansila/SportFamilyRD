<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;

class ScrapCalendarController extends Controller
{
    // Guarda eventos del scrapping en la tabla calendars
    public function store(Request $request)
    {
        $events = $request->input('events', []);
        $created = [];
        foreach ($events as $event) {
            $created[] = Calendar::updateOrCreate(
                [
                    'date' => $event['fecha'] ?? date('Y-m-d'),
                    'time' => $event['startTime'] ?? '00:00:00',
                    'place' => $event['place'] ?? 'Sin lugar',
                ],
                [
                    'price' => isset($event['price']) ? preg_replace('/[^\d.]/', '', $event['price']) : 0,
                    'image' => $event['image'] ?? null,
                    'quantity' => 100
                ]
            );
        }
        return response()->json(['created' => $created]);
    }
}
