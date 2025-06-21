<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;

class NewChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chat_id;
    public $sender_id;

    public function __construct($message)
    {
        $this->message = $message;
        $this->chat_id = $message->chat_id;
        $this->sender_id = $message->sender_id;
    }

    public function broadcastOn()
    {
        return [
            new Channel('chat.' . $this->chat_id),
            new Channel('user.' . $this->recipientId())
        ];
    }

    public function broadcastAs()
    {
        return 'NewMessage';
    }

    public function recipientId()
    {
        $chat = Chat::find($this->chat_id);
        return $this->sender_id == $chat->user_id
            ? $chat->trainer_id
            : $chat->user_id;
    }
}
