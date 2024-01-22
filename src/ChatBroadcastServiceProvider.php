<?php
declare(strict_types=1);

namespace Dd1\Chat;

use Dd1\Chat\Events\ChatsUpdated;
use Dd1\Chat\Events\MessageSent;
use Dd1\Chat\Events\TypingEvent;
use Dd1\Chat\Events\UserStatusUpdatedEvent;
use Dd1\Chat\Listeners\CentrifugoPushToChannel;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ChatBroadcastServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            CentrifugoPushToChannel::class,
        ],
        ChatsUpdated::class => [
            CentrifugoPushToChannel::class,
        ],
        TypingEvent::class => [
            CentrifugoPushToChannel::class
        ],
        UserStatusUpdatedEvent::class => [
            CentrifugoPushToChannel::class
        ]
    ];
}
