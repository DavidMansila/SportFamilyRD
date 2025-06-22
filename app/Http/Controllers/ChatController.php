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
            'trainer.user:id,name,image',
            'lastMessage'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('trainer', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
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
                    'trainer' => [
                        'id' => $chat->trainer->id,
                        'user' => $chat->trainer->user
                    ]
                ];
            });

        return response()->json($chats);
    }


    public function storeMessage(Request $request, $chatId)
    {
        $request->validate(['message' => 'required|string']);

        $user = Auth::user();
        $senderType = $user->user_type === 'user' ? 'user' : 'trainer';

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message' => $request->message
        ]);

        // Disparar evento de mensaje enviado
        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }

    public function markAsRead($chatId)
    {
        $userId = Auth::id();

        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        // Disparar evento de mensaje leído
        broadcast(new MessageRead($chatId))->toOthers();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'trainer_id' => 'required|exists:trainer,id',
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
        $chat = Chat::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'user', 'trainer'])
            ->findOrFail($id);

        Message::where('chat_id', $id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['read' => true]);

        return response()->json([
            'messages' => $chat->messages,
            'chat' => $chat
        ]);
    }



    public function acceptChat(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);
        $chat->update(['status' => 'accepted']);

        return response()->json($chat);
    }
}
