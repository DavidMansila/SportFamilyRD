<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);

    if (!$chat) return false;

    return (int) $user->id === (int) $chat->user_id ||
        (int) $user->id === (int) $chat->trainer->user_id;
});

// Para private-user.{id}
Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Para private-chat.{chatId}
Broadcast::channel('private-chat.{chatId}', function ($user, $chatId) {
    return (int) $user->chats()->where('id', $chatId)->exists();
});

Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    if ($user->chats()->where('id', $chatId)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
    }
});
