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
                ? url('storage/users/' . $user->id . '/' . $user->image)
                : url('storage/users/Perfil-Icon.png');
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
            event(new Registered($user));

            return response()->json([
                'message' => 'Usuario creado con éxito',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
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
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update($request->all());

            if (isset($request['image']) && $request['image']) {
                $imageName = Post::addImages($request['image'], $user->id, 'users');
                $user->update(['image' => $imageName]);
            }

            $user->image = $user->image
                ? url('storage/users/' . $user->id . '/' . $user->image)
                : url('storage/users/Perfil-Icon.png');

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
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
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
}
