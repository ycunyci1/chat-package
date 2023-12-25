<?php

namespace Dd1\Chat\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatsUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chats;
    public $channel;

    public function __construct($chats, $userId)
    {
        $this->chats = $chats;
        $this->channel = 'user-' . $userId . '-chats';
    }
}
