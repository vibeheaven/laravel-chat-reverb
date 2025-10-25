<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Message $message,
        public Conversation $conversation
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $messageData = [
            'id' => $this->message->id,
            'text' => $this->message->text,
            'type' => $this->message->type ?? 'normal',
            'metadata' => $this->message->metadata,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'sender_avatar' => $this->message->sender->avatar_url,
            'created_at' => $this->message->created_at->format('Y-m-d H:i:s'),
            'time' => $this->message->created_at->format('H:i'),
            'date' => $this->message->created_at->format('d M Y'),
            'status' => 'delivered',
        ];

        // Dosya eki varsa ekle
        if ($this->message->attachment_path) {
            $messageData['attachment'] = [
                'url' => asset('storage/' . $this->message->attachment_path),
                'name' => $this->message->attachment_name,
                'type' => $this->message->attachment_type,
                'size' => $this->message->attachment_size,
            ];
        }

        return [
            'message' => $messageData,
            'conversation_id' => $this->conversation->id,
        ];
    }
}
