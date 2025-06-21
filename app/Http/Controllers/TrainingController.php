<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'trainer_id' => 'nullable|integer',
            'page' => 'sometimes|integer'
        ]);

        $query = Training::with('user');

        if ($request->has('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        return $query->get();
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
            'trainer_id' => 'required|exists:users,id',
            'sport_level' => 'required|in:Principiante,Intermedio,Avanzado,Profesional',
            'description' => 'required|string|max:500',
            'status' => 'required|in:pending,accepted,rejected'
        ]);

        $lastWeek = now()->subWeek();
        $existing = Training::where('user_id', $request->user_id)
            ->where('trainer_id', $request->trainer_id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('created_at', '>=', $lastWeek)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Ya tienes una solicitud activa con este entrenador'
            ], 422);
        }

        $training = Training::create($validated);
        return response()->json($training, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $training = Training::find($id);

        if (!$training) {
            return response()->json(['message' => 'Training not found'], 404);
        }

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

    /**
     * Get all training requests made by a user.
     */

    public function getReceivedTrainings(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|integer'
        ]);

        $trainerId = $request->input('trainer_id');

        $trainings = Training::with(['user' => function ($query) {
            $query->select('id', 'name', 'email', 'phone', 'image');
        }])
            ->where('trainer_id', $trainerId)
            ->get();

        return $trainings->map(function ($training) {
            $imageUrl = $training->user && $training->user->image
                ? asset('storage/users/' . $training->user->id . '/' . $training->user->image)
                : asset('storage/users/Perfil-Icon.png');

            return [
                'id' => $training->id,
                'user_id' => $training->user_id,
                'sport_level' => $training->sport_level,
                'description' => $training->description,
                'status' => $training->status,
                'created_at' => $training->created_at,
                'user' => $training->user ? [
                    'name' => $training->user->name,
                    'email' => $training->user->email,
                    'phone' => $training->user->phone,
                    'image_url' => $imageUrl
                ] : null
            ];
        });
    }

    public function checkExisting(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'trainer_id' => 'required|integer',
        ]);

        $lastWeek = now()->subWeek();

        $existingRequests = Training::where('user_id', $request->user_id)
            ->where('trainer_id', $request->trainer_id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('created_at', '>=', $lastWeek)
            ->get();

        return response()->json([
            'exists' => $existingRequests->isNotEmpty(),
            'count' => $existingRequests->count(),
            'last_week' => $lastWeek->toDateTimeString(),
            'requests' => $existingRequests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'created_at' => $req->created_at->toDateTimeString(),
                    'status' => $req->status
                ];
            })
        ]);
    }
}
