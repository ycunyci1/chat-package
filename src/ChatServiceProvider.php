<?php
declare(strict_types=1);

namespace Dd1\Chat;

use Dd1\Chat\Console\Commands\SetOfflineStatus;
use Dd1\Chat\Events\ChatsUpdated;
use Dd1\Chat\Events\MessageSent;
use Dd1\Chat\Listeners\PushChats;
use Dd1\Chat\Listeners\PushMessage;
use Dd1\Chat\Middleware\UpdateUserStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            SetOfflineStatus::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database');
        $this->app->booted(function () {
            $this->schedule($this->app->make(Schedule::class));
        });


        $this->publishes([
            __DIR__ . '/Database' => database_path('migrations'),
        ], 'migrations');
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('set-offline-status')->everyMinute();
    }
}
