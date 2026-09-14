<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\ConfigurationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ConfigurationController extends Controller
{
    public function index(Request $request )
    {
        try {
            $userId = $request->user()->id;
            // Obtener todas las configuraciones
            $allConfigs = Configuration::all();
            // Obtener las configuraciones del usuario
            $userConfigs = ConfigurationUser::where('user_id', $userId)->get()->keyBy('configuration_id');

            $result = $allConfigs->map(function ($config) use ($userConfigs) {
                $userConfig = $userConfigs->get($config->id);
                
                return [
                    'id' => $config->id,
                    'configuration' => $config->configuration,
                    // 'disabled' y no 'inactivo': el frontend compara contra
                    // 'enabled' para pintar el interruptor, asi que el valor por
                    // defecto tiene que estar en ese mismo vocabulario.
                    'value' => $userConfig ? $userConfig->status : 'disabled',
                ];
            });

            return response()->json([
                'config'=>$result,
                'message' => 'Configuraciones recibidas exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return error_json($e, 'Error al obtener las configuraciones', 500);
        }
    }

    public function updateValue(Request $request)
    {
        try {
            // Antes no se validaba nada: 'configuration_id' podia apuntar a una
            // fila inexistente y 'status' aceptaba cualquier cadena.
            $datos = $request->validate([
                // Tabla 'configuration' en singular (ver $table del modelo).
                // Los valores son los que manda AjustesView.vue en el toggle.
                'configuration_id' => 'required|integer|exists:configuration,id',
                'status' => 'required|in:enabled,disabled',
            ]);

            $userId = $request->user()->id;
            $configId = $datos['configuration_id'];
            $status = $datos['status'];

            // Verificar si la configuración ya existe para el usuario
            $userConfig = ConfigurationUser::where('user_id', $userId)
                ->where('configuration_id', $configId)
                ->first();

            if ($userConfig) {
                $userConfig->status = $status;
                $userConfig->save();
            } else {
                ConfigurationUser::create([
                    'user_id' => $userId,
                    'configuration_id' => $configId,
                    'status' => $status,
                ]);
            }

            return response()->json([
                'message' => 'Configuración actualizada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return error_json($e, 'Error al actualizar la configuración', 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
            ]);

            $currentPassword = $request->current_password;
            $newPassword = $request->new_password;

            // Siempre la contraseña del usuario autenticado, nunca un user_id que
            // mande el cliente.
            $user = $request->user();

            if (! Hash::check($currentPassword, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                ], 400);
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            // REVOCAR TODAS LAS SESIONES y emitir una nueva para este cliente.
            //
            // Cambiar la contraseña es justo lo que hace alguien que sospecha
            // que le entraron en la cuenta, y era lo unico que no funcionaba:
            // los tokens ya emitidos seguian siendo validos despues del cambio
            // (y hasta hace poco no caducaban nunca), asi que quien tuviera uno
            // robado conservaba el acceso y la victima creia haber recuperado
            // la cuenta.
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
                // El cliente tiene que guardar este token: el suyo acaba de ser
                // revocado junto con los demas.
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return error_json($e, 'Error al actualizar la contraseña', 500);
        }
     
    }

    
    /**
     * El catalogo de configuraciones es GLOBAL: una fila de esta tabla gobierna
     * una preferencia para TODOS los usuarios de la plataforma. Crearlas,
     * renombrarlas o borrarlas es una accion de admin.
     *
     * Antes store/update/destroy no comprobaban nada mas alla de estar
     * autenticado: cualquier cuenta recien registrada podia borrar el catalogo
     * entero. index() y updateValue() si son de cada usuario (tocan su propia
     * fila en configuration_user) y no llevan este chequeo.
     */
    private function soloAdmin(Request $request): ?\Illuminate\Http\JsonResponse
    {
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }

    public function show(Request $request, $id)
    {
        if ($resp = $this->soloAdmin($request)) {
            return $resp;
        }

        return response()->json(Configuration::findOrFail($id));
    }

    public function store(Request $request)
    {
        if ($resp = $this->soloAdmin($request)) {
            return $resp;
        }

        $configuration = Configuration::create($request->validate([
            'configuration' => 'required|string|max:255',
        ]));

        return response()->json($configuration, 201);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->soloAdmin($request)) {
            return $resp;
        }

        $configuration = Configuration::findOrFail($id);
        $configuration->update($request->validate([
            'configuration' => 'required|string|max:255',
        ]));

        return response()->json($configuration);
    }

    public function destroy(Request $request, $id)
    {
        if ($resp = $this->soloAdmin($request)) {
            return $resp;
        }

        Configuration::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
