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
                        // La columna es 'achievement_date' (ver la migracion de
                        // achievements): pedir '->date' devolvia siempre null y
                        // el panel de admin mostraba la fecha del logro vacia.
                        'date' => $achievement->achievement_date,
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
        //
        // COLUMNAS EXPLICITAS, por lo mismo que en PostController::index(): esta
        // ruta es PUBLICA y cargar la relacion entera serializaba el registro
        // completo de la cuenta del entrenador -correo personal, telefono,
        // fecha de nacimiento, ubicacion y biografia- a cualquiera que pidiera
        // /api/trainer/approved sin identificarse.
        //
        // Ojo con la distincion: los campos 'email' y 'phone' del propio
        // Trainer son los datos de contacto PROFESIONALES que la persona puso
        // en su solicitud para aparecer en el directorio, y esos si son
        // publicos a proposito. Lo que no debia salir es la cuenta de usuario
        // detras. El frontend solo lee user.name.
        $approvedTrainer = Trainer::with([
            'achievements',
            'specialties',
            'user:id,name,image',
        ])
            ->where('status', 'approved')
            ->get();

        $approvedTrainer->transform(function ($trainer) {
            // El nombre de archivo crudo se lee ANTES de resolver la relacion,
            // porque resolve_user_image() reescribe $trainer->user->image en el
            // sitio y despues ya no serviria para armar esta ruta.
            $archivo = optional($trainer->user)->image;

            // La relacion 'user' tambien se resuelve: el modal de detalle del
            // entrenador pinta user.image directamente, y sin esto recibia el
            // nombre de archivo suelto ("avatar.jpg"), que el navegador resuelve
            // contra la URL actual y da una imagen rota. La tarjeta del listado
            // no lo notaba porque usa $trainer->image, que si estaba resuelta.
            resolve_user_image($trainer->user);

            $trainer->image = $archivo
                ? public_storage_url('users/' . $trainer->user_id . '/' . $archivo)
                : asset('defaults/Perfil-Icon.png');
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

    /**
     * Reglas de una solicitud de entrenador.
     *
     * store() y update() metian $request->all() directo en create()/update()
     * SIN UNA SOLA REGLA de validacion: campos de longitud ilimitada, tipos
     * arbitrarios y correo sin formato, que despues se muestran en el
     * directorio publico de entrenadores.
     *
     * Los enum replican los de la migracion de 'trainer': con un valor fuera de
     * la lista, Postgres rechazaba el INSERT con un error de tipo que llegaba al
     * cliente como un 500.
     *
     * user_id y status NO estan aqui a proposito: los fija el controlador
     * (user_id = usuario autenticado, status = 'pending'), nunca el cliente.
     *
     * @param  bool  $parcial  update() permite mandar solo algunos campos.
     */
    private function reglas(bool $parcial = false): array
    {
        $req = $parcial ? 'sometimes' : 'required';

        return [
            'name' => "$req|string|max:255",
            'email' => "$req|email|max:255",
            'phone' => "$req|string|max:30",
            'city_country' => "$req|string|max:255",
            'sport_category' => "$req|in:Fútbol,Baloncesto,Tenis,Natación,Ciclismo,Atletismo,Artes Marciales",
            'experience' => "$req|string|max:255",
            'level_of_certification' => "$req|in:ninguna,basica,intermedia,avanzada,nacional,internacional",
            'certificates_linked' => 'nullable|string|max:2048',
            'description' => 'nullable|string|max:2000',
            'schedule' => 'nullable|string|max:1000',
            'cost' => 'nullable|numeric|min:0|max:1000000',
        ];
    }

    /**
     * achievements y specialties llegan como array o como JSON en una cadena
     * (segun como los serialice el frontend), y a veces con cada elemento a su
     * vez como cadena JSON. Esta normalizacion ya estaba duplicada literalmente
     * cuatro veces entre store() y update().
     */
    private function normalizarLista($valor): array
    {
        if (is_string($valor)) {
            $valor = json_decode($valor, true) ?? [];
        }

        if (! is_array($valor)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_string($item)) {
                $decoded = json_decode($item, true);
                return is_array($decoded) ? $decoded : null;
            }

            return is_array($item) ? $item : null;
        }, $valor)));
    }

    /** Solo las claves que el modelo acepta, con longitud acotada. */
    private function saneaAchievements(array $items): array
    {
        return array_map(fn($a) => [
            'title' => mb_substr((string) ($a['title'] ?? ''), 0, 255),
            'description' => mb_substr((string) ($a['description'] ?? ''), 0, 1000),
            'achievement_date' => $a['achievement_date'] ?? ($a['date'] ?? null),
        ], array_slice($items, 0, 50));
    }

    private function saneaSpecialties(array $items): array
    {
        return array_map(fn($s) => [
            'description' => mb_substr((string) ($s['description'] ?? ''), 0, 500),
        ], array_slice($items, 0, 50));
    }

    /**
     * El formulario manda la tarifa como FormData y, cuando el campo esta vacio,
     * JS serializa null/undefined como la CADENA "null". Hay que normalizarlo
     * ANTES de validar: la regla 'numeric' rechazaria "null" con un 422 y el
     * formulario de solicitud dejaria de funcionar para quien no ponga tarifa.
     *
     * Antes esta conversion se hacia despues, justo antes del create(), porque
     * no habia validacion ninguna.
     */
    private function normalizarCost(Request $request): void
    {
        if ($request->has('cost')) {
            $cost = $request->input('cost');
            if ($cost === 'null' || $cost === 'undefined' || $cost === '' || $cost === null) {
                $request->merge(['cost' => null]);
            }
        }
    }

    public function store(Request $request)
    {
        $this->normalizarCost($request);
        $request->validate($this->reglas());

        $data = $request->all();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);
        $specialties = $data['specialties'] ?? [];
        unset($data['specialties']);

        // user_id siempre el del usuario autenticado (nunca uno que mande el
        // cliente), y status siempre arranca en 'pending': solo updateStatus()
        // (solo-admin) puede aprobar o rechazar una solicitud.
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';
        $trainer = Trainer::create($data);

        $achievements = $this->saneaAchievements($this->normalizarLista($achievements));
        if (!empty($achievements)) {
            $trainer->achievements()->createMany($achievements);
        }

        $specialties = $this->saneaSpecialties($this->normalizarLista($specialties));
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

        $this->normalizarCost($request);
        $request->validate($this->reglas(parcial: true));

        $data = $request->all();
        $achievements = $data['achievements'] ?? [];
        unset($data['achievements']);
        $specialties = $data['specialties'] ?? [];
        unset($data['specialties']);

        // status solo lo cambia updateStatus() (solo-admin): nunca por esta via,
        // o cualquiera podria auto-aprobarse como entrenador.
        unset($data['user_id'], $data['status']);
        $trainer->update($data);

        $trainer->achievements()->delete();
        $achievements = $this->saneaAchievements($this->normalizarLista($achievements));
        if (!empty($achievements)) {
            $trainer->achievements()->createMany($achievements);
        }

        $trainer->specialties()->delete();
        $specialties = $this->saneaSpecialties($this->normalizarLista($specialties));
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

        // optional(): la relacion 'user' puede no resolver si la cuenta se
        // borro y quedo la fila de entrenador. Sin esta guarda, una ruta
        // PUBLICA respondia 500 (y con APP_DEBUG a true, un volcado del
        // entorno completo).
        return response()->json([
            'id' => $trainer->id,
            'name' => optional($trainer->user)->name,
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
