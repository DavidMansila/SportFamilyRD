<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $trainers = Trainer::where('status', 'pending')->get();
        return response()->json([
            'message' => 'solicitud de entrenador creada exitosamente',
            'trainers' => $trainers
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //todo agregar validacio
        //todo agregar el add images
        //todo 
        $trainer = Trainer::create($request->all());

        return response()->json([
            'message' => 'solicitud de entrenador creada exitosamente',
            'product' => $trainer
        ], 200);
    }

    /**
     * Update the status of the specified trainer.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);
        
        $trainer = Trainer::findOrFail($id);
        $trainer->status = $request->input('status');

        $user = User::findOrFail($trainer->user_id);
        
        if ($trainer->status === 'approved') {
            $user->user_type = 'entrenador';
            $user->save();
        } elseif ($trainer->status === 'rejected') {
            $user->user_type = 'user';
            $user->save();
        }
        $trainer->save();

        return response()->json([
            'message' => 'Estado del entrenador actualizado exitosamente',
            'user' => $user,
            'trainer' => $trainer
        ], 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
