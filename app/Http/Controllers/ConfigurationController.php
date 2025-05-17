<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\ConfigurationUser;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    // Muestra todas las configuraciones
    public function index(Request $request )
    {
        try {
            $userId = $request->user_id;
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

    // Muestra una configuración específica
    public function show($id)
    {
        $configuration = Configuration::findOrFail($id);
        return response()->json($configuration);
    }

    // Crea una nueva configuración
    public function store(Request $request)
    {
        $configuration = Configuration::create($request->validate([
            'configuration' => 'nullable|string|max:255',
        ]));
        return response()->json($configuration, 201);
    }

    // Actualiza una configuración existente
    public function update(Request $request, $id)
    {
        $configuration = Configuration::findOrFail($id);
        $configuration->update($request->validate([
            'configuration' => 'nullable|string|max:255',
        ]));
        return response()->json($configuration);
    }

    // Elimina una configuración
    public function destroy($id)
    {
        $configuration = Configuration::findOrFail($id);
        $configuration->delete();
        return response()->json(null, 204);
    }
}
