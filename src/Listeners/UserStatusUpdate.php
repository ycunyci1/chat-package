<?php

namespace Dd1\Chat\Listeners;

use Dd1\Chat\Services\CentrifugoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserStatusUpdate
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $centrifugo = app()->make(CentrifugoService::class);
        $centrifugo->publishToChannel($event->channel, json_encode([
            'userId' => $event->userId,
            'isOnline' => $event->isOnline,
            'lastSeen' => $event->lastSeen
        ]));
    }
}
