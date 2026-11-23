<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat-room.'.$this->message->chat_room_id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message' => $this->message->message,
            'is_proposal' => $this->message->is_proposal,
            'proposal_status' => $this->message->proposal_status,
            'is_active_proposal' => $this->message->isActiveProposal(),
            'is_system_message' => $this->message->is_system_message,
            'created_at' => $this->message->created_at->format('H:i'),
        ];
    }
}