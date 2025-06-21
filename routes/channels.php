<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

// Canal de chat
Broadcast::channel('chat.{chatId}', function () {
    return true;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
