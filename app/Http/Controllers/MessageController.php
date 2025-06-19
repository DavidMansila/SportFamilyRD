<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_type' => Auth::user()->user_type === 'user' ? 'user' : 'trainer',
            'message' => $request->message
        ]);

        // Disparar evento
        event(new NewMessage($message));

        return response()->json($message, 201);
    }
}
