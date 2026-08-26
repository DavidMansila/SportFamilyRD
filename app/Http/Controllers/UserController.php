<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->map(function ($user) {
            $user->image = $user->image
                ? public_storage_url('users/' . $user->id . '/' . $user->image)
                : asset('defaults/Perfil-Icon.png');
            return $user;
        });

        return response()->json([
            'message' => 'Usuarios obtenidos con éxito',
            'users' => $users
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        dd($request->all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'user',
            ]);

            //iniciar sesion automaticamente al crear un usuario y mandar correo
            Auth::login($user);
            event(new Registered($user));

            // Generar token para el usuario
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario creado con éxito',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Contraseña debe contener al menos 4 caracteres: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $user->image = $user->image
            ? public_storage_url('users/' . $user->id . '/' . $user->image)
            : asset('defaults/Perfil-Icon.png');
        // Forzar que email_verified_at siempre esté presente en la respuesta
        $arr = $user->toArray();
        if (!array_key_exists('email_verified_at', $arr)) {
            $arr['email_verified_at'] = $user->email_verified_at;
        }
        return response()->json($arr);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Reemplaza solo el avatar del usuario (el frontend actual no usa esta
     * ruta: sube el avatar junto con el resto del perfil via update(), pero
     * la dejamos funcional por si algun cliente la llega a usar).
     */
    public function updateAvatar(Request $request, string $user)
    {
        try {
            $user = User::findOrFail($user);

            if ($request->user()->id != $user->id && $request->user()->user_type !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para editar este usuario'
                ], 403);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $imageName = Post::addImages($request->file('image'), $user->id, 'users');
            $user->update(['image' => $imageName]);
            $user->image = public_storage_url('users/' . $user->id . '/' . $user->image);

            return response()->json([
                'message' => 'Avatar actualizado con éxito',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Solo el dueño de la cuenta o un admin puede editarla. Antes esta ruta
            // era publica y sin este chequeo, asi que cualquiera podia editar (o,
            // via $request->all(), incluso cambiar el password o el user_type de
            // cualquier cuenta).
            if ($request->user()->id != $user->id && $request->user()->user_type !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para editar este usuario'
                ], 403);
            }

            // Lista blanca explicita: email, password, user_type y email_verified_at
            // nunca se aceptan por esta via generica (el email/password tienen sus
            // propios flujos con verificacion; user_type solo lo cambia el sistema
            // al aprobar una solicitud de entrenador).
            $user->update($request->only([
                'name', 'phone', 'location', 'birthdate', 'bio',
            ]));

            if ($request->hasFile('image')) {
                $imageName = Post::addImages($request->file('image'), $user->id, 'users');
                $user->update(['image' => $imageName]);
            }

            $user->image = $user->image
                ? public_storage_url('users/' . $user->id . '/' . $user->image)
                : asset('defaults/Perfil-Icon.png');

            return response()->json([
                'message' => 'Usuario actualizado con éxito',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function showUserRequests(Request $request)
    // {
    //     try{
    //         $user = Auth::user();
    //         $requests = Training::where('status', 'pending')->get();

    //         return response()->json([
    //             'message' => 'Solicitudes obtenidas con éxito',
    //             'requests' => $requests,
    //         ], 200);

    //     }catch(\Exception $e){
    //         return response()->json([
    //             'message' => 'Error: '.$e->getMessage()
    //         ], 500);
    //     }
    // }

    public function showUserRequests(Request $request)
    {
        try{
            $user = Auth::user();
            $requests = Training::where('status', 'pending')->get();

            return response()->json([
                'message' => 'Solicitudes obtenidas con éxito',
                'requests' => $requests,
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error: '.$e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($request->user()->id != $user->id && $request->user()->user_type !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para eliminar este usuario'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'message' => 'Usuario eliminado con éxito',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

   public function getUserByID($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $image = $user->image
                ? public_storage_url('users/' . $user->id . '/' . $user->image)
                : asset('defaults/Perfil-Icon.png');

            // Endpoint publico (se usa justo despues de verificar el correo, antes
            // de que exista un token de sesion): solo se devuelven campos no
            // sensibles. Antes devolvia el registro completo (email, telefono,
            // fecha de nacimiento, bio) de CUALQUIER usuario sin autenticacion.
            return response()->json([
                'message' => 'Usuario obtenido con éxito',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $image,
                    'user_type' => $user->user_type,
                    'email_verified_at' => $user->email_verified_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hubo un error',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
