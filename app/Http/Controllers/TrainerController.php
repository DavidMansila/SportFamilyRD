<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Trainer::query()->with(['achievements', 'specialties']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $trainers = $query->get()->map(function ($trainer) {
            return [
                'id' => $trainer->id,
                'name' => $trainer->name,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'sport_category' => $trainer->sport_category,
                'experience' => $trainer->experience,
                'city_country' => $trainer->city_country,
                'cost' => $trainer->cost,
                'certificates_linked' => $trainer->certificates_linked,
                'status' => $trainer->status,
                'created_at' => $trainer->created_at,
                'achievements' => $trainer->achievements->map(function ($achievement) {
                    return [
                        'title' => $achievement->title,
                        'description' => $achievement->description,
                        'date' => $achievement->date
                    ];
                })->toArray(),
                'specialties' => $trainer->specialties->map(function ($specialty) {
                    return [
                        'description' => $specialty->description
                    ];
                })->toArray()
            ];
        });

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

    public function create()
    {
        //
    }

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
        $data = $request->all();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);
        $specialties = $data['specialties'] ?? [];
        unset($data['specialties']);

        if (isset($data['cost']) && ($data['cost'] === 'null' || $data['cost'] === '')) {
            $data['cost'] = null;
        }

        $trainer = Trainer::findOrFail($id);
        $trainer->update($data);

        $trainer->achievements()->delete();
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

        $trainer->specialties()->delete();
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
            'message' => 'solicitud de entrenador actualizada exitosamente (con achievements y specialties)',
            'product' => $trainer->load(['achievements', 'specialties'])
        ], 200);
    }


    public function destroy(string $id)
    {
        //
    }

    /**
     * Get approved trainers with their achievements and specialties.
     */


    public function getTrainerByUserId($userId)
    {
        $trainer = Trainer::where('user_id', $userId)->first();

        if (!$trainer) {
            return response()->json(['message' => 'Trainer not found'], 404);
        }

        return response()->json([
            'id' => $trainer->id,
            'name' => $trainer->user->name,
        ]);
    }


    public function getAllTrainerRequests()
    {
        $requests = Trainer::all();
        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
}
