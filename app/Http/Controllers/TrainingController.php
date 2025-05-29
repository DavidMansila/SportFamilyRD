<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trainings = Training::all();
        return response()->json($trainings);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // For API, this can be left empty or return a view if needed
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'age' => 'required|integer',
            'sport_level' => 'required',
            'description' => 'nullable|string',
            'status' => 'required',
        ]);

        
        $training = Training::create($validated);
        return response()->json($training, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $training = Training::findOrFail($id);
        return response()->json($training);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // For API, this can be left empty or return a view if needed
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'age' => 'sometimes|integer',
            'sport_level' => 'sometimes',
            'description' => 'nullable|string',
            'status' => 'sometimes',
        ]);
        $training->update($validated);
        return response()->json($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $training->delete();
        return response()->json(null, 204);
    }
}
