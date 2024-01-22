<?php

namespace Dd1\Chat\Listeners;

use Dd1\Chat\Services\CentrifugoService;

class CentrifugoPushToChannel
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $centrifugo = app()->make(CentrifugoService::class);
        $centrifugo->publishToChannel($event->channel, json_encode($event->data));
    }
}
