<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:users,id',
            'training_id' => 'required|exists:trainings,id',
            'status' => 'required|in:pending,accepted,rejected'
        ]);

        // Verificar que no exista ya un chat para este training
        $existingChat = Chat::where('training_id', $request->training_id)->first();
        if ($existingChat) {
            return response()->json(['message' => 'Ya existe un chat para este entrenamiento'], 409);
        }

        $chat = Chat::create($request->all());

        return response()->json($chat, 201);
    }


    public function createChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:users,id',
            'training_id' => 'required|exists:training_requests,id', // Cambiado a training_requests
            'status' => 'required|in:pending,accepted,rejected'
        ]);

        // Verificar que no exista ya un chat para este training
        $existingChat = Chat::where('training_id', $request->training_id)->first();
        if ($existingChat) {
            return response()->json(['message' => 'Ya existe un chat para este entrenamiento'], 409);
        }

        $chat = Chat::create([
            'user_id' => $request->user_id,
            'trainer_id' => $request->trainer_id,
            'training_id' => $request->training_id,
            'status' => $request->status
        ]);

        return response()->json($chat, 201);
    }

    // Obtener chats del usuario
    public function getUserChats()
    {
        $user = Auth::user();

        $chats = Chat::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('trainer_id', $user->id);
        })
            ->where('status', 'accepted')
            ->with(['user', 'trainer'])
            ->get();

        return response()->json($chats);
    }

    // Obtener mensajes de un chat
    public function getMessages($chatId)
    {
        $chat = Chat::findOrFail($chatId);

        // Verificar que el usuario pertenece al chat
        if (Auth::id() !== $chat->user_id && Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // Enviar mensaje
    public function sendMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $chat = Chat::findOrFail($chatId);

        // Verificar que el usuario pertenece al chat
        if (Auth::id() !== $chat->user_id && Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json($message);
    }

    // Aceptar solicitud de chat
    public function acceptRequest($chatId)
    {
        $chat = Chat::findOrFail($chatId);

        // Solo el entrenador puede aceptar
        if (Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->update(['status' => 'accepted']);

        return response()->json($chat);
    }


    // Marcar mensajes como leídos
    public function markAsRead($chatId)
    {
        $chat = Chat::findOrFail($chatId);

        // Verificar que el usuario pertenece al chat
        if (Auth::id() !== $chat->user_id && Auth::id() !== $chat->trainer_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', Auth::id())
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }
}
