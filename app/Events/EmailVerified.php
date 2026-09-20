<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Aviso de que una cuenta acaba de verificar su correo.
 *
 * ShouldBroadcastNow y no ShouldBroadcast: con el segundo, Laravel ENCOLA el
 * aviso, y este proyecto no tiene ningun worker corriendo -en Render hay un
 * unico proceso que atiende peticiones y nada mas-, asi que los eventos se
 * quedaban en la tabla 'jobs' para siempre. Habia dos ahi dentro, del 17 y del
 * 20 de septiembre, esperando a alguien que nunca iba a pasar: la pantalla de
 * "verifica tu correo" no se enteraba de la verificacion y habia que recargar.
 *
 * Los otros tres eventos del proyecto (NewMessage, NewChat, MessageRead) ya
 * usan ShouldBroadcastNow, que es justamente por lo que el chat si funciona en
 * vivo. Este se quedo atras.
 */
class EmailVerified implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('email-verified');
    }
}
