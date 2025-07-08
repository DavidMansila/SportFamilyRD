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
            'created_at'  => $this->message->created_at->toDateTimeString(),
            'read'        => $this->message->read,
        ];
    }
}
