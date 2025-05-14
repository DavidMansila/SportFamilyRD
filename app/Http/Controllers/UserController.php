<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            : url('public/imagenes/no_image.png');
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
        // dd($request->all());
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            //todo ponerle confirmed cuando david mande el front
        ]);

        try{
           $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type'=> 'user',
            ]);

            //iniciar sesion automaticamente al crear un usuario
            Auth::login($user);

            return response()->json([
                'message' => 'Usuario creado con éxito',
                 'user' => $user,
            ], 200);

        }catch(\Exception $e){
            // \Log::error('Error al crear usuario: '.$e->getMessage());
            return response()->json([
                'message' => 'Error: '.$e->getMessage()
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
        User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::where('id', $id)->delete();
    }

    
}