<?php

namespace  Dd1\Chat\Console\Commands;

use Dd1\Chat\Events\UserStatusUpdatedEvent;
use Dd1\Chat\Models\User;
use Illuminate\Console\Command;

class SetOfflineStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-offline-status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->where('last_seen_at', '<', now()->subMinutes(3))
            ->where('is_online', 1)
            ->get();
        foreach ($users as $user) {
            $user->update([
                'is_online' => 0
            ]);
            event(new UserStatusUpdatedEvent($user->id, 0, $user->last_seen_at));
        }
    }
}
