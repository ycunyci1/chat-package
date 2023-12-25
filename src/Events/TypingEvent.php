<?php

namespace Dd1\Chat\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TypingEvent
{
    public $chatId;
    public $userId;
    public $typing;
    public $channel;

    public function __construct($chatId, $userId, $typing)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->typing = $typing;
        $this->channel = 'chat.' . $this->chatId;
    }
}
