<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('private-chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    if (!$chat) {
        Log::warning('Chat no encontrado', ['chatId' => $chatId]);
        return false;
    }
    Log::info('Auth canal privado', [
        'user_id' => $user->id,
        'chat_user_id' => $chat->user_id,
        'trainer_user_id' => optional($chat->trainer)->user_id
    ]);
    // Permitir acceso si el usuario es parte del chat o es entrenador
    if ($user->id == $chat->user_id || ($chat->trainer && $user->id == $chat->trainer->user_id)) {
        return true;
    }
    return false;
});

Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    if (!$chat) {
        Log::warning('Chat no encontrado (presence)', ['chatId' => $chatId]);
        return false;
    }
    Log::info('Auth canal presence', [
        'user_id' => $user->id,
        'chat_user_id' => $chat->user_id,
        'trainer_user_id' => optional($chat->trainer)->user_id
    ]);
    if (
        $user->id == $chat->user_id ||
        ($chat->trainer && $user->trainer && $user->trainer->id == $chat->trainer_id)
    ) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }
    return false;
});

Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    Log::info('Canal private-user', [
        'user' => $user,
        'userId_param' => $userId,
        'user_id' => $user ? $user->id : null,
        'auth_check' => $user && ((int) $user->id === (int) $userId)
    ]);
    return (int) $user->id === (int) $userId;
});
