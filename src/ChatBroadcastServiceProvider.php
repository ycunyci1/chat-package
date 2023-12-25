<?php
declare(strict_types=1);

namespace Dd1\Chat;

use Dd1\Chat\Events\ChatsUpdated;
use Dd1\Chat\Events\MessageSent;
use Dd1\Chat\Events\TypingEvent;
use Dd1\Chat\Events\UserStatusUpdatedEvent;
use Dd1\Chat\Listeners\CompanionTyping;
use Dd1\Chat\Listeners\PushChats;
use Dd1\Chat\Listeners\PushMessage;
use Dd1\Chat\Listeners\UserStatusUpdate;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ChatBroadcastServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageSent::class => [
            PushMessage::class,
        ],
        ChatsUpdated::class => [
            PushChats::class,
        ],
        TypingEvent::class => [
            CompanionTyping::class
        ],
        UserStatusUpdatedEvent::class => [
            UserStatusUpdate::class
        ]
    ];
}
