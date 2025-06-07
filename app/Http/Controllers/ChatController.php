<?php

namespace App\Http\Controllers;

use App\Events\NewChat;
use App\Models\Chat;
use App\Models\Message;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $chats = Chat::with(['user', 'trainer', 'lastMessage'])
            ->forUser($userId)
            ->accepted()
            ->get()
            ->map(function ($chat) use ($userId) {
                $chat->unread = $chat->messages()
                    ->where('sender_id', '!=', $userId)
                    ->where('read', false)
                    ->count();
                return $chat;
            });

        return response()->json($chats);
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

        $chat = Chat::findOrFail($chatId);
        $recipientId = Auth::id() == $chat->user_id ? $chat->trainer_id : $chat->user_id;

        broadcast(new NewChat($message, $recipientId))->toOthers();

        return response()->json($message);
    }

    public function acceptChat(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->update(['status' => 'accepted']);

        return response()->json($chat);
    }
}
