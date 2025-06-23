<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $user->image = $user->image
            ? url('storage/users/' . $user->id . '/' . $user->image)
            : url('storage/users/Perfil-Icon.png');

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Sesión cerrada con éxito']);
        }

        return response()->json(['message' => 'No hay sesión activa'], 401);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function csrfCookie()
    {
        return response()->json(['message' => 'CSRF cookie set']);
    }
}
