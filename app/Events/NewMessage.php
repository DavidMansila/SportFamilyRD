<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class NewMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastAs()
    {
        return 'message-sent';
    }

    public function broadcastOn()
    {
        Log::info("Broadcasting message {$this->message->id} on chat.{$this->message->chat_id}");
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }

    public function broadcastWith()
    {
        return [
            'id'          => $this->message->id,
            'chat_id'     => $this->message->chat_id,
            'sender_id'   => $this->message->sender_id,
            'sender_type' => $this->message->sender_type === 'trainer' ? 'trainer' : 'user',
            'message'     => $this->message->message,
            // toJSON() y no toDateTimeString(): el mensaje que llega en vivo
            // tiene que traer la fecha en el MISMO formato que la que devuelve
            // la API al recargar la conversacion (ISO-8601 con la Z de UTC).
            // "Y-m-d H:i:s" no lleva zona horaria, y `new Date(...)` en el
            // navegador interpreta esa cadena como hora LOCAL: el mismo mensaje
            // aparecia con una hora al recibirlo y con otra distinta al
            // recargar la pagina -cuatro horas de diferencia en Republica
            // Dominicana-.
            'created_at'  => $this->message->created_at->toJSON(),
            'read'        => $this->message->read,
        ];
    }
}
