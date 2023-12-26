<?php
declare(strict_types=1);

namespace Dd1\Chat\Controllers;

use Carbon\Carbon;
use Dd1\Chat\Events\ChatsUpdated;
use Dd1\Chat\Events\MessageSent;
use Dd1\Chat\Events\TypingEvent;
use Dd1\Chat\Events\UserStatusUpdatedEvent;
use Dd1\Chat\Models\User;
use Dd1\Chat\Requests\MessageRequest;
use Dd1\Chat\Resources\ChatResource;
use Dd1\Chat\Resources\MessageResource;
use Dd1\Chat\Resources\UserResource;
use Dd1\Chat\Models\Chat;
use Dd1\Chat\Models\Message;
use Dd1\Chat\Services\CentrifugoService;
use Dd1\Chat\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getChats(): JsonResponse
    {
        $userChats = Chat::query()
            ->whereHas('users', fn($users) => $users->where('id', auth()->id()))
            ->with('users', 'messages.user')
            ->get()
            ->sortByDesc(function ($chat) {
                return $chat->messages->sortByDesc('id')->first()->created_at;
            });
        return response()->json(ChatResource::collection($userChats));
    }

    public function getMessages($chatId): JsonResponse
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
            event(new ChatsUpdated(ChatService::getChatsDataForCurrentUser($userChats, auth()->user()), auth()->id()));

            $companion = $unreadMessages->first()->chat->users->where('id', '!=', auth()->id())->first();
            $companionChats = Chat::query()
                ->whereHas('users', fn($users) => $users->where('users.id', $companion->id))
                ->with('users', 'messages.user')
                ->get()
                ->sortByDesc(function ($chat) {
                    return $chat->messages->sortByDesc('id')->first()->created_at;
                });
            event(new ChatsUpdated(ChatService::getChatsDataForCurrentUser($companionChats, $companion), $companion->id));
        }
        return response()->json([
            'messages' => MessageResource::collection($messages),
            'companion' => UserResource::make(Chat::find($chatId)->users()->whereNot('id', auth()->id())->first()),
            'chat_id' => $chatId,
        ]);
    }

    public function sendMessage(MessageRequest $request, $chatId): JsonResponse
    {
        $chatId = intval($chatId);
        $chat = Chat::query()->find($chatId);
        $userId = $request->user()->id;
        $data = $request->validated();
        $message = Message::query()->create([
            'user_id' => $userId,
            'text' => $data['text'],
            'chat_id' => $chatId
        ]);
        $userChats = Chat::query()
            ->whereHas('users', fn($users) => $users->where('users.id', $userId))
            ->with('users', 'messages.user')
            ->get()
            ->sortByDesc(function ($chat) {
                return $chat->messages->sortByDesc('id')->first()->created_at;
            });

        $companion = $chat->users()->whereNot('id', auth()->id())->first();
        $companionChats = Chat::query()
            ->whereHas('users', fn($users) => $users->where('users.id', $companion->id))
            ->with('users', 'messages.user')
            ->get()
            ->sortByDesc(function ($chat) {
                return $chat->messages->sortByDesc('id')->first()->created_at;
            });

        event(new MessageSent(MessageResource::make($message)));
        event(new ChatsUpdated(ChatService::getChatsDataForCurrentUser($userChats, auth()->user()), $userId));
        event(new ChatsUpdated(ChatService::getChatsDataForCurrentUser($companionChats, $companion), $companion->id));

        User::query()->find($userId)->update([
            'is_online' => 1,
            'last_seen_at' => now(),
        ]);
        event(new UserStatusUpdatedEvent($userId, 1, now()));
        return response()->json(MessageResource::make($message));
    }

    public function handleTyping(Request $request)
    {
        $userId = auth()->id();
        $chatId = $request->chatId;
        $typing = $request->typing;
        if (!auth()->user()->is_online) {
            User::query()->find($userId)->update([
                'is_online' => 1,
                'last_seen_at' => now(),
            ]);
            event(new UserStatusUpdatedEvent($userId, 1, now()));
        }
        event(new TypingEvent($chatId, $userId, $typing));
    }

    public function searchUsers(Request $request)
    {
        $data = $request->validate([
            'search' => 'string|required'
        ]);
        $searchString = $data['search'];
        $searchUsers = User::query()
            ->whereNot('id', auth()->id())
            ->where('name', 'like', "%$searchString%")
            ->get();
        return response()->json(UserResource::collection($searchUsers));
    }

    public function createChat(Request $request)
    {
        $data = $request->validate([
            'companionId' => 'exists:users,id'
        ]);
        $chat = Chat::query()
            ->whereHas('users', fn($users) => $users->where('id', auth()->id()))
            ->whereHas('users', fn($users) => $users->where('id', $data['companionId']))
            ->first();
        if (!$chat) {
            $chat = Chat::query()->create();
            $chat->users()->attach([auth()->id(), $data['companionId']]);
        }
        return response()->json([
            'chatId' => $chat->id
        ]);
    }
}
