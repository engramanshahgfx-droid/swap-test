<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        $conversation = $this->message->conversation;
        $recipientId = $conversation->user_one_id === $this->message->sender_id 
            ? $conversation->user_two_id 
            : $conversation->user_one_id;

        return new PrivateChannel('user.' . $recipientId);
    }

    public function broadcastAs()
    {
        return 'new.message';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'message' => $this->message->message,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->full_name,
            ],
            'created_at' => $this->message->created_at,
        ];
    }
}
