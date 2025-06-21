<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) return false;

    return (int) $user->id === (int) $chat->user_id ||
        (int) $user->id === (int) $chat->trainer->user_id;
});

Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) return false;

    if (
        (int) $user->id === (int) $chat->user_id ||
        (int) $user->id === (int) $chat->trainer->user_id
    ) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});


Broadcast::channel('private-chat.{chatId}', function ($user, $chatId) {
  return $user->chats()->where('chats.id', $chatId)->exists();
});