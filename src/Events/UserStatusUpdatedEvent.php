<?php

namespace Dd1\Chat\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $channel;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $isOnline, $lastSeen)
    {
        $this->channel = 'user-' . $userId . '-status';
        $this->data = [
            'userId' => $userId,
            'isOnline' => $isOnline,
            'lastSeen' => $lastSeen
        ];
    }
}
