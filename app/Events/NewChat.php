<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Avisa al DESTINATARIO de un mensaje, en su canal personal, que le llego algo
 * nuevo. Se usa para que la burbuja de chats actualice la lista y el contador
 * de no leidos aunque esa conversacion no este abierta.
 *
 * Correcciones respecto a la version anterior (que nunca funciono):
 * - recipientUserId() devolvia $chat->trainer_id, que es el id de la tabla
 *   'trainer', NO el id del usuario. El aviso se mandaba a un canal de otra
 *   persona (o de nadie). Ahora resuelve el user_id real del entrenador.
 * - Emitia a PrivateChannel('online.X'), pero ese canal esta declarado como
 *   canal de presencia; nombres distintos, nadie escuchaba.
 * - Usaba ShouldBroadcast (encolado) con QUEUE_CONNECTION=database y sin worker
 *   corriendo, asi que el evento no se enviaba nunca. Ahora es ShouldBroadcastNow.
 */
class NewChat implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn()
    {
        $recipientId = $this->recipientUserId();

        return $recipientId
            ? [new PrivateChannel('user.' . $recipientId)]
            : [];
    }

    public function broadcastAs()
    {
        return 'chat.updated';
    }

    public function broadcastWith()
    {
        return [
            'chat_id'    => $this->message->chat_id,
            'message'    => $this->message->message,
            'sender_id'  => $this->message->sender_id,
            'created_at' => optional($this->message->created_at)->toDateTimeString(),
        ];
    }

    /**
     * user_id del participante que NO envio el mensaje.
     */
    private function recipientUserId(): ?int
    {
        $chat = $this->message->chat()->with('trainer')->first();

        if (!$chat) {
            return null;
        }

        $trainerUserId = optional($chat->trainer)->user_id;

        return (int) $this->message->sender_id === (int) $chat->user_id
            ? $trainerUserId
            : $chat->user_id;
    }
}
