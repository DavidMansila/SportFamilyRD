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
        $request->validate(['message' => 'required|string|max:1000']);

        $user = Auth::user();
        $senderType = $user->user_type === 'user' ? 'user' : 'trainer';

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message' => $request->message
        ]);

        // Disparar evento correctamente
        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message, 201);
    }
}
