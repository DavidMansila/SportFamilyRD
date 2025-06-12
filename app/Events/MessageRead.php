<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;

    public function __construct($chatId)
    {
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chatId);
    }

    public function broadcastAs()
    {
        return 'MessageRead';
    }
}
