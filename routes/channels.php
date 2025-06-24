<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Chat;

// Canal privado para mensajes
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::findOrFail($chatId);

    $isMember = $user->id == $chat->user_id ||
        ($chat->trainer && $user->id == $chat->trainer->user_id);

    return $isMember;
});

// Canal de presencia
Broadcast::channel('online.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        Log::warning("Chat no encontrado: $chatId");
        return false;
    }

    $isMember = $user->id == $chat->user_id ||
        ($chat->trainer && $user->id == $chat->trainer->user_id);

    if (!$isMember) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'trainer_id' => optional($user->trainer)->id,
        'type' => $user->user_type
    ];
});
