<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calendars = Calendar::all();
        
        if ($calendars->isEmpty()) {
            return response()->json(['events' => []]);
        }

        return response()->json([
            'events' => $calendars,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $calendar = Calendar::create($request->validate([
            'Title'=> 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'place' => 'required|string',
            'price' => 'required|numeric',
            'Description' => 'string',
            'image'=> 'string|max:255',
            'quantity' => 'required|integer|min:1',
        ]));

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $path = "calendars/{$calendar->id}";

            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            $imageName = time() . '.' . $image->extension();
            $image->storeAs($path, $imageName, 'public');
           
            $calendar->image = $imageName;
            $calendar->save();
        }

        return response()->json(
            [
                'message' => 'Evento creado correctamente',
                'event' => $calendar,
            ],200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendar $calendar)
    {
        return response()->json($calendar);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Calendar $calendar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Calendar $calendar)
    {
        $calendar->update($request->validate([
            'date' => 'sometimes|date',
            'time' => 'sometimes',
            'place' => 'sometimes|string',
            'price' => 'sometimes|numeric',
        ]));
        return response()->json($calendar);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendar $calendar)
    {
        $calendar->delete();
        return response()->json(null, 204);
    }

    /**
     * Devuelve los 3 próximos eventos para la home
     */
    public function featuredEvents()
    {
        $events = Calendar::whereDate('date', '>=', now())
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->take(3)
            ->get()
            ->map(function($event) {
                return [
                    'event_id' => 'EVT-' . $event->id,
                    'title' => $event->title ?? 'Evento Deportivo',
                    'date' => date('d/M', strtotime($event->date)),
                    'time' => $event->time,
                    'location' => $event->place,
                    'description' => $event->description ?? '',
                    'image' => $event->image ?? null,
                ];
            });
        return response()->json(['events' => $events]);
    }
}
