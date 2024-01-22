<?php

namespace Dd1\Chat\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $channel;

    public function __construct($message)
    {
        $this->channel = 'user-' . $message->chat_id . '-messages';
        $this->data = $message;
    }
}
