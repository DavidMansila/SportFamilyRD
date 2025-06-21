<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calendars = Calendar::all();
        return response()->json($calendars);
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
            'date' => 'required|date',
            'time' => 'required',
            'place' => 'required|string',
            'price' => 'required|numeric',
        ]));
        return response()->json($calendar, 201);
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

   
}
