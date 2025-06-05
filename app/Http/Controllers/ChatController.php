<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // 1. Crear o recuperar un chat entre usuario y entrenador
    public function store(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:users,id'
        ]);

        $userId = Auth::id();

        $chat = Chat::firstOrCreate(
            ['user_id' => $userId, 'trainer_id' => $request->trainer_id],
            ['status' => 'pending']
        );

        return response()->json($chat, 201);
    }

    // 2. Entrenador acepta el chat
    public function accept($chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $chat->status = 'accepted';
        $chat->save();

        return response()->json(['message' => 'Chat aceptado']);
    }

    // 3. Entrenador rechaza el chat
    public function reject($chatId)
    {
        $chat = Chat::findOrFail($chatId);

        if (Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $chat->status = 'rejected';
        $chat->save();

        return response()->json(['message' => 'Chat rechazado']);
    }

    // 4. Enviar mensaje
    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string'
        ]);

        $chat = Chat::findOrFail($request->chat_id);
        $userId = Auth::id();

        // Solo participantes pueden enviar
        if (!in_array($userId, [$chat->user_id, $chat->trainer_id]) || $chat->status !== 'accepted') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $userId,
            'message' => $request->message,
            'read' => false
        ]);

        return response()->json($message);
    }

    // 5. Obtener mensajes de un chat
    public function getMessages($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $userId = Auth::id();

        if (!in_array($userId, [$chat->user_id, $chat->trainer_id])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $messages = $chat->messages()->with('sender')->orderBy('created_at')->get();

        return response()->json($messages);
    }

    // 6. Ver todos los chats del usuario autenticado
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['error' => 'Falta el parámetro user_id'], 400);
        }

        $chats = Chat::where('user_id', $userId)
            ->orWhere('trainer_id', $userId)
            ->with(['client', 'trainer'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($chats);
    }
}
