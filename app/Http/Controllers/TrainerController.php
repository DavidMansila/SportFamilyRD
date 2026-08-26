<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\NuevaSolicitudAdminMail;
use App\Mail\SolicitudAprobadaEntrenador;
use App\Mail\SolicitudRechazadaEntrenador;

use Illuminate\Support\Facades\Mail;

class TrainerController extends Controller
{

    public function index(Request $request)
    {
        // Lista completa de solicitudes de entrenador (incluye pendientes/rechazadas,
        // con telefono/email/ciudad): solo un admin puede verla. Antes esta ruta
        // era publica y exponia esos datos de TODAS las solicitudes sin autenticacion.
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $status = $request->query('status', 'all');

        $query = Trainer::query()->with(['achievements', 'specialties']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $trainer = $query->get()->map(function ($trainer) {
            return [
                'id' => $trainer->id,
                'name' => $trainer->name,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'sport_category' => $trainer->sport_category,
                'experience' => $trainer->experience,
                'city_country' => $trainer->city_country,
                'cost' => $trainer->cost,
                'level_of_certification' => $trainer->level_of_certification,
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
            'trainer' => $trainer
        ], 200);
    }

    public function getAprovedTrainers()
    {
        // 'user' va en el eager-load: sin esto, optional($trainer->user) mas abajo
        // dispara una consulta SEPARADA por cada entrenador (N+1) para leer su
        // imagen, cada una un viaje de red aparte a Supabase.
        $approvedTrainer = Trainer::with(['achievements', 'specialties', 'user'])
            ->where('status', 'approved')
            ->get();

        $approvedTrainer->transform(function ($trainer) {
            $trainer->image = optional($trainer->user)->image;
            $trainer->image = $trainer->image
                ? public_storage_url('users/' . $trainer->user_id . '/' . $trainer->image)
                : public_storage_url('users/Perfil-Icon.png');
            return $trainer;
        });

        return response()->json([
            'message' => 'Entrenadores aprobados obtenidos exitosamente',
            'trainer' => $approvedTrainer
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

        // user_id siempre el del usuario autenticado (nunca uno que mande el
        // cliente), y status siempre arranca en 'pending': solo updateStatus()
        // (solo-admin) puede aprobar o rechazar una solicitud.
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';
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
        $admin = User::where('user_type', 'admin')->first();
        if ($admin) {
            Mail::to($admin->email)->send(new NuevaSolicitudAdminMail($admin, $trainer->user));
        }

        return response()->json([
            'message' => 'solicitud de entrenador creada exitosamente (con achievements y specialties)',
            'product' => $trainer->load(['achievements', 'specialties'])
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        // Aprobar/rechazar una solicitud (y con eso, convertir al usuario en
        // entrenador) es una accion solo de admin.
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected'
        ]);

        $trainer = Trainer::findOrFail($id);
        $trainer->status = $request->input('status');

        $user = User::findOrFail($trainer->user_id);

        if ($trainer->status === 'approved') {
            $user->user_type = 'entrenador';
            $user->category = $trainer->sport_category;
            $user->save();
            Mail::to($user->email)->send(new SolicitudAprobadaEntrenador($user));
        } elseif ($trainer->status === 'rejected') {
            $user->user_type = 'user';
            $user->save();
            Mail::to($user->email)->send(new SolicitudRechazadaEntrenador($user));
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
        $trainer = Trainer::findOrFail($id);

        // Solo el dueño de la solicitud o un admin puede editarla.
        if ($trainer->user_id != $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->all();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);
        $specialties = $data['specialties'] ?? [];
        unset($data['specialties']);

        if (isset($data['cost']) && ($data['cost'] === 'null' || $data['cost'] === '')) {
            $data['cost'] = null;
        }

        // status solo lo cambia updateStatus() (solo-admin): nunca por esta via,
        // o cualquiera podria auto-aprobarse como entrenador.
        unset($data['user_id'], $data['status']);
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


    public function getAllTrainerRequests(Request $request)
    {
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $requests = Trainer::all();
        return response()->json([
            'success' => true,
            'requests' => $requests
        ]);
    }
}
