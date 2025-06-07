<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatId;
    public $count;

    public function __construct($chatId, $count)
    {
        $this->chatId = $chatId;
        $this->count = $count;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->chatId);
    }
}
