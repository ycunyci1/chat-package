<?php

namespace Dd1\Chat\Listeners;

use Dd1\Chat\Services\CentrifugoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CompanionTyping
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $centrifugo = app()->make(CentrifugoService::class);
        $centrifugo->publishToChannel($event->channel, json_encode([
            'chatId' => $event->chatId,
            'userId' => $event->userId,
            'typing' => $event->typing
        ]));
    }
}
