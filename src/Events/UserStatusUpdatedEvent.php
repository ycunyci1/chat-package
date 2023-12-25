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

    public $userId;
    public $isOnline;
    public $lastSeen;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $isOnline, $lastSeen)
    {
        $this->userId = $userId;
        $this->isOnline = $isOnline;
        $this->lastSeen = Carbon::parse($lastSeen)->format('H:i');
        $this->channel = 'user-' . $this->userId . '-status';
    }
}
