<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Chat;


// Canal privado
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::with(['user', 'trainer'])->find($chatId);

    if (!$chat) return false;

    // Verificar si el usuario es participante del chat
    $isParticipant = $user->id == $chat->user_id ||
        ($chat->trainer && $user->id == $chat->trainer->user_id);

    Log::info("Autenticando usuario {$user->id} para chat {$chatId}: " .
        ($isParticipant ? 'APROBADO' : 'RECHAZADO'));

    return $isParticipant;
});


// Presencia en el canal
Broadcast::channel('online.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) return false;

    $isMember = $user->id == $chat->user_id
        || ($chat->trainer && $user->id == $chat->trainer->user_id);
    if (!$isMember) return false;

    return [
        'id'        => $user->id,
        'name'      => $user->name,
        'avatar'    => $user->image,
        'user_type' => $user->user_type,
    ];
});
