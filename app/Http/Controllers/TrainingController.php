<?php

namespace App\Http\Controllers;
use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudRechazadaMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaSolicitudEntrenadorMail;

use App\Models\Training;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;
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

        // La lista trae nombre/email/telefono de cada solicitante: solo el
        // propio entrenador (o un admin) puede consultar sus solicitudes.
        if ($request->has('trainer_id')) {
            $isOwnerTrainer = Trainer::where('id', $request->trainer_id)
                ->where('user_id', $request->user()->id)
                ->exists();
            if (!$isOwnerTrainer && $request->user()->user_type !== 'admin') {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        } elseif ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $query = Training::with('user');

        if ($request->has('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        return $query->get();
    }

    public function create()
    {
        // For API, this can be left empty or return a view if needed
    }


    public function store(Request $request)
    {
        // El solicitante siempre es el usuario autenticado, nunca un user_id que
        // mande el cliente (si no, cualquiera podria solicitar entrenamiento
        // suplantando a otro usuario).
        $user = $request->user();

        $validated = $request->validate([
            'trainer_id' => 'required|exists:trainer,id',
            'sport_level' => 'required|in:Principiante,Intermedio,Avanzado,Profesional',
            'description' => 'required|string|max:500',
        ]);
        // status siempre arranca en 'pending': solo el entrenador (via update())
        // puede aceptar o rechazar, nunca el propio solicitante.
        $validated['status'] = 'pending';

        // Verificar si ya existe una solicitud pendiente o recientemente expirada
        $existingRequest = Training::where('user_id', $user->id)
            ->where('trainer_id', $validated['trainer_id'])
            ->whereIn('status', ['pending', 'expired'])
            ->first();

        if ($existingRequest) {
            // Si la solicitud está expirada, permitir nueva solicitud eliminando la anterior
            if ($existingRequest->status === 'expired') {
                $existingRequest->delete();
            }
            // Si está pendiente, verificar si ha expirado
            else {
                $expirationDate = Carbon::parse($existingRequest->created_at)
                    ->addWeekdays(3)
                    ->endOfDay();

                if (now()->gt($expirationDate)) {
                    // Marcar como expirada y permitir nueva solicitud
                    $existingRequest->update(['status' => 'expired']);
                    $existingRequest->delete();
                } else {
                    return response()->json([
                        'message' => 'Ya tienes una solicitud pendiente con este entrenador'
                    ], 422);
                }
            }
        }

        // Crear nueva solicitud
        $training = Training::create([
            'user_id' => $user->id,
            'trainer_id' => $validated['trainer_id'],
            'sport_level' => $validated['sport_level'],
            'description' => $validated['description'],
            'status' => $validated['status']
        ]);

        // Obtener el entrenador (User)
        $entrenador = User::whereHas('trainer', function($q) use ($validated) {
            $q->where('id', $validated['trainer_id']);
        })->first();

        if ($entrenador) {
            Mail::to($entrenador->email)->send(new NuevaSolicitudEntrenadorMail($entrenador, $user));
        }

        return response()->json($training, 201);
    }



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

        // Solo el entrenador dueño de la solicitud (o un admin) puede aceptarla o
        // rechazarla. Antes no habia ningun chequeo: cualquier usuario autenticado
        // podia aceptar/rechazar (o reasignar a otro user_id) cualquier solicitud.
        $isOwnerTrainer = Trainer::where('id', $training->trainer_id)
            ->where('user_id', $request->user()->id)
            ->exists();
        if (!$isOwnerTrainer && $request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'sport_level' => 'sometimes',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,accepted,rejected,expired',
        ]);

        $training->update($validated);

         // Asegúrate de tener la relación user cargada
            $training->load('user');

            // Enviar correo solo si se actualizó el status
            if (isset($validated['status'])) {
                if ($validated['status'] === 'accepted') {
                    Mail::to($training->user->email)->send(new SolicitudAprobadaMail($training->user));
                }
                if ($validated['status'] === 'rejected') {
                    Mail::to($training->user->email)->send(new SolicitudRechazadaMail($training->user));
                }
            }

        return response()->json($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        $isOwnerTrainer = Trainer::where('id', $training->trainer_id)
            ->where('user_id', $request->user()->id)
            ->exists();
        if (
            $training->user_id != $request->user()->id
            && !$isOwnerTrainer
            && $request->user()->user_type !== 'admin'
        ) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

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

        // Solo el propio entrenador (o un admin) puede ver quien le ha pedido
        // entrenamiento: la lista incluye nombre/email/telefono de cada
        // solicitante. Antes cualquier usuario autenticado podia ver esos datos
        // de cualquier entrenador con solo cambiar el trainer_id.
        $isOwnerTrainer = Trainer::where('id', $trainerId)
            ->where('user_id', $request->user()->id)
            ->exists();
        if (!$isOwnerTrainer && $request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $trainings = Training::with(['user' => function ($query) {
            $query->select('id', 'name', 'email', 'phone', 'image');
        }])
            ->where('trainer_id', $trainerId)
            ->get();

        return $trainings->map(function ($training) {
            $imageUrl = $training->user && $training->user->image
                ? public_storage_url('users/' . $training->user->id . '/' . $training->user->image)
                : asset('defaults/Perfil-Icon.png');

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
            'trainer_id' => 'required|integer',
        ]);

        $lastWeek = now()->subWeek();

        $existingRequests = Training::where('user_id', $request->user()->id)
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
