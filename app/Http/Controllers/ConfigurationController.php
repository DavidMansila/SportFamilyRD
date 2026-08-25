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
                    'value' => $userConfig ? $userConfig->status : 'inactivo',
                ];
            });

            return response()->json([
                'config'=>$result,
                'message' => 'Configuraciones recibidas exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las configuraciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateValue(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $configId = $request->configuration_id;
            $status = $request->status;

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
            return response()->json([
                'message' => 'Error al actualizar la configuración',
                'error' => $e->getMessage(),
            ], 500);
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

            // Actualizar la contraseña
            if (Hash::check($currentPassword, $user->password)) {
                
                $user->password = Hash::make($newPassword);
                $user->save();
            }else{
            
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                ], 400);
            }

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la contraseña',
                'error' => $e->getMessage(),
            ], 500);
        }
     
    }

    
    public function show($id)
    {
        $configuration = Configuration::findOrFail($id);
        return response()->json($configuration);
    }

    public function store(Request $request)
    {
        $configuration = Configuration::create($request->validate([
            'configuration' => 'nullable|string|max:255',
        ]));
        return response()->json($configuration, 201);
    }

    public function update(Request $request, $id)
    {
        $configuration = Configuration::findOrFail($id);
        $configuration->update($request->validate([
            'configuration' => 'nullable|string|max:255',
        ]));
        return response()->json($configuration);
    }

    public function destroy($id)
    {
        $configuration = Configuration::findOrFail($id);
        $configuration->delete();
        return response()->json(null, 204);
    }
}
