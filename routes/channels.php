<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Chat;


// Canal personal de cada usuario. Sirve para avisarle que le llego un mensaje
// en CUALQUIERA de sus chats, aunque no lo tenga abierto (lo escucha la burbuja
// de chats para actualizar la lista y el contador de no leidos en vivo).
// Solo el propio dueño puede suscribirse.
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});


// Canal privado
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::with(['user', 'trainer'])->find($chatId);

    if (!$chat) return false;

    // Verificar si el usuario es participante del chat
    // Sin Log::info por autorizacion: se escribia una linea con el id de
    // usuario y de chat en CADA suscripcion a un canal. Si hace falta depurar
    // esto, es un caso puntual y se sube el nivel de log a proposito.
    return $user->id == $chat->user_id ||
        ($chat->trainer && $user->id == $chat->trainer->user_id);
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
