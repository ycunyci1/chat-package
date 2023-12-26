<?php

namespace Dd1\Chat\Services;

use Carbon\Carbon;

class ChatService
{
    public static function getChatsDataForCurrentUser($chats, $currentUser)
    {
        $chatsArr = [];
        foreach ($chats as $key => $chat) {
            $data = [];
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
}
