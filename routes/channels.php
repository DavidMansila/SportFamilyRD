<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-chat.{chatId}', function ($user, $chatId) {
    return (int) $user->id === (int) \App\Models\Chat::findOrFail($chatId)->user_id ||
        (int) $user->id === (int) \App\Models\Chat::findOrFail($chatId)->trainer->user_id;
});

Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::findOrFail($chatId);

    if (
        $user->id === $chat->user_id ||
        ($user->trainer && $user->trainer->id === $chat->trainer_id)
    ) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }

    return false;
});
