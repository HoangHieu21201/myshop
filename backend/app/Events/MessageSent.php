<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Phát sóng đồng thời trên kênh của CẢ HAI bên (gửi + nhận)
     * Giúp cả user lẫn admin thấy tin nhắn ngay lập tức mà không cần dùng toOthers()
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];

        // Nếu sender khác receiver thì thêm kênh của sender (để sender thấy confirm)
        if ($this->message->sender_id !== $this->message->receiver_id) {
            $channels[] = new PrivateChannel('chat.' . $this->message->sender_id);
        }

        return $channels;
    }
    
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}