<?php

namespace App\Http\Controllers;

use App\Events\NewChat;
use App\Events\NewMessage;
use App\Events\MessageRead;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function index(Request $request)
    {
        $userId = Auth::id();

        $chats = Chat::with([
            'user:id,name,image',
            'trainer:id,name,image',
            'lastMessage'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('trainer_id', $userId);
            })
            ->accepted()
            ->get()
            ->map(function ($chat) use ($userId) {
                return [
                    'id' => $chat->id,
                    'user_id' => $chat->user_id,
                    'trainer_id' => $chat->trainer_id,
                    'status' => $chat->status,
                    'unread_count' => $chat->messages()
                        ->where('sender_id', '!=', $userId)
                        ->where('read', false)
                        ->count(),
                    'last_message' => $chat->last_message,
                    'user' => $chat->user,
                    'trainer' => $chat->trainer
                ];
            });

        return response()->json($chats);
    }



    public function storeMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => Auth::id(),
            'message' => $request->message
        ]);

        $message->load('sender');

        $chat = Chat::findOrFail($chatId);

        // Determinar el receptor correctamente
        $receiverId = (Auth::id() == $chat->user_id)
            ? $chat->trainer_id
            : $chat->user_id;

        broadcast(new NewMessage($message, $receiverId));

        return response()->json($message);
    }



    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:users,id',
        ]);

        // Verificar si ya existe un chat
        $existingChat = Chat::where('user_id', $request->user_id)
            ->where('trainer_id', $request->trainer_id)
            ->first();

        if ($existingChat) {
            return response()->json([
                'message' => 'Ya existe un chat entre estos usuarios',
                'chat' => $existingChat
            ], 200);
        }

        $chat = Chat::create([
            'user_id' => $request->user_id,
            'trainer_id' => $request->trainer_id,
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'Chat creado exitosamente',
            'chat' => $chat
        ], 201);
    }



    public function show($id)
    {
        $chat = Chat::with(['messages.sender', 'user', 'trainer'])
            ->findOrFail($id);

        // Marcar mensajes como leídos
        Message::where('chat_id', $id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['read' => true]);

        return response()->json($chat);
    }



    public function acceptChat(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->update(['status' => 'accepted']);

        return response()->json($chat);
    }



    public function markAsRead($chatId)
    {
        $userId = Auth::id();

        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        // Enviar evento de actualización
        broadcast(new MessageRead($chatId));

        return response()->json(['success' => true]);
    }
}
