<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->generateAuthToken();

            $user->image = $user->image
                ? url('storage/users/' . $user->id . '/' . $user->image)
                : url('storage/users/Perfil-Icon.png');

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->clearAuthToken();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function currentUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->image = $user->image
            ? url('storage/users/' . $user->id . '/' . $user->image)
            : url('storage/users/Perfil-Icon.png');

        return response()->json($user);
    }
}
