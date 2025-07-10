<?php

namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel; 
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('online.' . $this->chatId);
    }

    public function broadcastAs()
    {
        return 'message.read';
    }
}
