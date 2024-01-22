<?php

namespace Dd1\Chat\Services;

use Carbon\Carbon;
use Dd1\Chat\Events\ChatsUpdated;
use Dd1\Chat\Models\Chat;
use Dd1\Chat\Models\Message;
use Dd1\Chat\Resources\MessageResource;
use Dd1\Chat\Resources\UserResource;

class ChatService
{
    public static function getChatsData($chats, $currentUser): array
    {
        $chatsArr = [];
        foreach ($chats as $chat) {
            $companion = $chat->users->where('id', '!=', $currentUser->id)->first();
            $lastMessage = $chat->messages->sortByDesc('id')->first();

            $data = [
                'id' => $chat->id,
                'companion_name' => $companion->name,
                'avatar' => $companion->avatar,
                'last_message' => null,
            ];

            if ($lastMessage) {
                $data['last_message'] = [
                    'text' => $lastMessage->text,
                    'timestamp' => Carbon::parse($lastMessage->created_at)->format('H:i'),
                    'sender_id' => $lastMessage->user->id,
                    'sender_name' => $lastMessage->user->name,
                    'was_read' => $lastMessage->was_read
                ];
            }
            $chatsArr[] = $data;
        }
        return $chatsArr;
    }

    public static function getMessages($chatId)
    {
        $chatId = intval($chatId);

        $messages = Message::where('chat_id', $chatId)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->sortBy('id');

        $unreadMessages = $messages->where('user_id', '!=', auth()->id())->where('was_read', 0);
        if ($unreadMessages->count()) {
            foreach ($unreadMessages as $unreadMessage) {
                $unreadMessage->update([
                    'was_read' => 1
                ]);
            }
            $userChats = Chat::query()
                ->whereHas('users', fn($users) => $users->where('users.id', auth()->id()))
                ->with('users', 'messages.user')
                ->get()
                ->sortByDesc(function ($chat) {
                    return $chat->messages->sortByDesc('id')->first()->created_at;
                });
            event(new ChatsUpdated(ChatService::getChatsData($userChats, auth()->user()), auth()->id()));

            $companion = $unreadMessages->first()->chat->users->where('id', '!=', auth()->id())->first();
            $companionChats = Chat::query()
                ->whereHas('users', fn($users) => $users->where('users.id', $companion->id))
                ->with('users', 'messages.user')
                ->get()
                ->sortByDesc(function ($chat) {
                    return $chat->messages->sortByDesc('id')->first()->created_at;
                });
            event(new ChatsUpdated(ChatService::getChatsData($companionChats, $companion), $companion->id));
        }
        return [
            'messages' => MessageResource::collection($messages),
            'companion' => UserResource::make(Chat::find($chatId)->users()->whereNot('id', auth()->id())->first()),
            'chat_id' => $chatId,
        ];
    }
}
