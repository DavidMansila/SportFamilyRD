<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Obtener el usuario autenticado
            $user = Auth::user();

            // Devolver el rol del usuario junto con el mensaje de éxito
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
            'credenciales' => $credentials,
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    // En AuthController.php
    public function currentUser(Request $request)
    {
        return response()->json($request->user());
    }


}
