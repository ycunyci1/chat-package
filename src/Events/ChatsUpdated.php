<?php

namespace Dd1\Chat\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ChatsUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $channel;

    public function __construct($chats, $userId)
    {
        $this->channel = 'user-' . $userId . '-chats';
        $this->data = $chats;
    }
}
