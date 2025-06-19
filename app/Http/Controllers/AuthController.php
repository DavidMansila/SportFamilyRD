<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->image = $user->image
            ? url('storage/users/' . $user->id . '/' . $user->image)
            : url('storage/users/Perfil-Icon.png');

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function currentUser(Request $request)
    {
        $user = $request->user();
        $user->image = $user->image
            ? url('storage/users/' . $user->id . '/' . $user->image)
            : url('storage/users/Perfil-Icon.png');

        return response()->json($user);
    }
}
