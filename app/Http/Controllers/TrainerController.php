<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Trainer::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $trainers = $query->get();

        return response()->json([
            'message' => 'Solicitudes obtenidas exitosamente',
            'trainers' => $trainers
        ], 200);
    }

    public function getAprovedTrainers()
    {
        $approvedTrainers = Trainer::with(['achievements', 'specialties'])
            ->where('status', 'approved')
            ->get();

        $approvedTrainers->transform(function ($trainer) {
            $trainer->image = optional($trainer->user)->image;
            $trainer->image = $trainer->image
                ? url('storage/users/' . $trainer->user_id . '/' . $trainer->image)
                : url('storage/users/Perfil-Icon.png');
            return $trainer;
        });

        return response()->json([
            'message' => 'Entrenadores aprobados obtenidos exitosamente',
            'trainers' => $approvedTrainers
        ], 200);
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
        $data = $request->all();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);
        $specialties = $data['specialties'] ?? [];
        unset($data['specialties']);

        if (isset($data['cost']) && ($data['cost'] === 'null' || $data['cost'] === '')) {
            $data['cost'] = null;
        }

        $trainer = Trainer::create($data);

        if (is_string($achievements)) {
            $achievements = json_decode($achievements, true) ?? [];
        }

        $achievements = array_map(function ($item) {
            if (is_string($item)) {
                $decoded = json_decode($item, true);
                return is_array($decoded) ? $decoded : [];
            }
            return $item;
        }, $achievements);
        if (!empty($achievements)) {
            $trainer->achievements()->createMany($achievements);
        }


        if (is_string($specialties)) {
            $specialties = json_decode($specialties, true) ?? [];
        }
        $specialties = array_map(function ($item) {
            if (is_string($item)) {
                $decoded = json_decode($item, true);
                return is_array($decoded) ? $decoded : [];
            }
            return $item;
        }, $specialties);
        if (!empty($specialties)) {
            $trainer->specialties()->createMany($specialties);
        }

        return response()->json([
            'message' => 'solicitud de entrenador creada exitosamente (con achievements y specialties)',
            'product' => $trainer->load(['achievements', 'specialties'])
        ], 200);
    }

    /**
     * Store a newly created resource in storage (con manejo de achievements relacional).
     */


    /**
     * Update the status of the specified trainer.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $trainer = Trainer::findOrFail($id);
        $trainer->status = $request->input('status');

        $user = User::findOrFail($trainer->user_id);

        if ($trainer->status === 'approved') {
            $user->user_type = 'entrenador';
            $user->category = $trainer->sport_category;
            $user->save();
        } elseif ($trainer->status === 'rejected') {
            $user->user_type = 'user';
            $user->save();
        }
        $trainer->save();

        return response()->json([
            'message' => 'Estado del entrenador actualizado exitosamente',
            'user' => $user,
            'trainer' => $trainer
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $trainer = Trainer::findOrFail($id);

        $trainer->sport_category = $request->input('sport_category');

        // Sincronizar especialidades
        $trainer->specialties()->delete();
        foreach ($request->input('specialties') as $specialty) {
            $trainer->specialties()->create(['description' => $specialty['description']]);
        }

        // Sincronizar logros
        $trainer->achievements()->delete();
        foreach ($request->input('achievements') as $achievement) {
            $trainer->achievements()->create([
                'title' => $achievement['title'],
                'description' => $achievement['description'],
                'date' => $achievement['date'] ?? null
            ]);
        }

        $trainer->save();

        return response()->json(['trainer' => $trainer]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get approved trainers with their achievements and specialties.
     */
}
