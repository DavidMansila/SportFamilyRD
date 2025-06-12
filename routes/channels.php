<?php

use Illuminate\Support\Facades\Broadcast;

// Canal de chat
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return true;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
