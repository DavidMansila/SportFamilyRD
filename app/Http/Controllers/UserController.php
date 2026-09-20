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
use Illuminate\Support\Facades\Log;

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // min:8 - antes era min:4, que admite '1234'. El cambio de
            // contraseña (ConfigurationController) ya exigia 8: eran dos
            // politicas distintas para la misma contraseña.
            'password' => 'required|string|min:8|confirmed',
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

            // El correo de verificacion va en su propio try: si falla el envio
            // (proveedor caido, credenciales mal, puerto SMTP bloqueado...) la
            // cuenta YA esta creada, y antes esa excepcion caia en el catch de
            // abajo y devolvia 500 "No se pudo crear el usuario". El usuario
            // veia un error, se registraba de nuevo y le saltaba "el correo ya
            // esta en uso". Ahora el registro se completa igual y el frontend
            // sabe, por 'verification_email_sent', si tiene que avisar de que
            // el correo no salio.
            $correoEnviado = true;
            try {
                event(new Registered($user));
            } catch (\Throwable $e) {
                $correoEnviado = false;
                Log::error('No se pudo enviar el correo de verificacion al registrar', [
                    'user_id' => $user->id,
                    'mailer' => config('mail.default'),
                    'error' => $e->getMessage(),
                ]);
            }

            // Generar token para el usuario
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario creado con éxito',
                'user' => $user,
                'token' => $token,
                'verification_email_sent' => $correoEnviado,
            ], 201);
        } catch (\Exception $e) {
            return error_json($e, 'No se pudo crear el usuario', 500);
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

            // addImages devuelve null cuando el fichero no se llego a escribir.
            // Guardar el nombre igualmente dejaria el perfil apuntando a una
            // imagen inexistente, que es peor que no cambiar nada.
            if ($imageName === null) {
                return response()->json([
                    'message' => 'No se pudo guardar la imagen. Inténtalo de nuevo.',
                ], 503);
            }

            $user->update(['image' => $imageName]);
            $user->image = public_storage_url('users/' . $user->id . '/' . $user->image);

            return response()->json([
                'message' => 'Avatar actualizado con éxito',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return error_json($e, 'Error al procesar la solicitud', 500);
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

                // Si la imagen no se pudo guardar se conserva la anterior: el
                // resto del perfil (nombre, telefono...) ya se actualizo y esa
                // parte si es valida, asi que no se tira la peticion entera.
                if ($imageName !== null) {
                    $user->update(['image' => $imageName]);
                }
            }

            $user->image = $user->image
                ? public_storage_url('users/' . $user->id . '/' . $user->image)
                : asset('defaults/Perfil-Icon.png');

            return response()->json([
                'message' => 'Usuario actualizado con éxito',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return error_json($e, 'Error al procesar la solicitud', 500);
        }
    }

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
            return error_json($e, 'Error al procesar la solicitud', 500);
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
            return error_json($e, 'Error al procesar la solicitud', 500);
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
            return error_json($e, 'Hubo un error', 404);
        }
    }
}
