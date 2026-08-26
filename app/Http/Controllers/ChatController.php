<?php

namespace App\Http\Controllers;

use App\Events\NewChat;
use App\Events\NewMessage;
use App\Events\MessageRead;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;

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
            ->where('status', 'accepted')
            ->get()
            ->map(function ($chat) use ($userId) {
                resolve_user_image($chat->user);
                if ($chat->trainer) {
                    resolve_user_image($chat->trainer->user);
                }

                return [
                    'id' => $chat->id,
                    'user_id' => $chat->user_id,
                    'trainer_id' => $chat->trainer_id,
                    'status' => $chat->status,
                    'unread_count' => $chat->messages()
                        ->where('sender_id', '!=', $userId)
                        ->where('read', false)
                        ->count(),
                    'last_message' => $chat->lastMessage ? [
                        'id' => $chat->lastMessage->id,
                        'message' => $chat->lastMessage->message,
                        'sender_id' => $chat->lastMessage->sender_id,
                        'created_at' => $chat->lastMessage->created_at
                    ] : null,
                    'user' => $chat->user,
                    'trainer' => [
                        'id' => $chat->trainer->id,
                        'user' => $chat->trainer->user
                    ]
                ];
            });

        Log::info('Chats encontrados: ' . count($chats));
        Log::info('User ID: ' . $userId);

        return response()->json($chats);
    }



    public function storeMessage(Request $request, $chatId)
    {
        $request->validate(['message' => 'required|string']);

        $user = $request->user();
        $chat = Chat::with('trainer')->findOrFail($chatId);

        // Solo alguno de los dos participantes del chat puede escribir en el.
        $isParticipant = $chat->user_id == $user->id
            || optional($chat->trainer)->user_id == $user->id;
        if (!$isParticipant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $user->id,
            'sender_type' => $user->user_type === 'user' ? 'user' : 'trainer',
            'message' => $request->message
        ]);

        // Al canal de la conversacion: lo escucha quien la tenga abierta.
        broadcast(new NewMessage($message))->toOthers();

        // Al canal personal del destinatario: le actualiza la lista de chats y
        // el contador de no leidos aunque no tenga esa conversacion abierta.
        broadcast(new NewChat($message));

        return response()->json($message);
    }



    public function markAsRead($chatId)
    {
        $userId = Auth::id();
        $chat = Chat::with('trainer')->findOrFail($chatId);

        $isParticipant = $chat->user_id == $userId
            || optional($chat->trainer)->user_id == $userId;
        if (!$isParticipant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        broadcast(new MessageRead($chatId))->toOthers();

        return response()->json(['success' => true]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:trainer,id',
        ]);

        $userId = $request->user()->id;

        // Verificar si ya existe un chat
        $existingChat = Chat::where('user_id', $userId)
            ->where('trainer_id', $request->trainer_id)
            ->first();

        if ($existingChat) {
            return response()->json([
                'message' => 'Ya existe un chat entre estos usuarios',
                'chat' => $existingChat
            ], 200);
        }

        $chat = Chat::create([
            'user_id' => $userId,
            'trainer_id' => $request->trainer_id,
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'Chat creado exitosamente',
            'chat' => $chat
        ], 201);
    }



    public function show(Request $request, $id)
    {
        $chat = Chat::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'user', 'trainer'])
            ->findOrFail($id);

        // Solo alguno de los dos participantes puede leer la conversacion; si no,
        // cualquiera podria leer los mensajes privados de otros con solo
        // adivinar/incrementar el id del chat.
        $userId = $request->user()->id;
        $isParticipant = $chat->user_id == $userId
            || optional($chat->trainer)->user_id == $userId;
        if (!$isParticipant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

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
