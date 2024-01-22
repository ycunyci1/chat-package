<?php

namespace Dd1\Chat\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TypingEvent
{
    public $channel;
    public $data;

    public function __construct($chatId, $userId, $typing)
    {
        $this->channel = 'chat.' . $chatId;
        $this->data = [
            'chatId' => $chatId,
            'userId' => $userId,
            'typing' => $typing
        ];
    }
}
