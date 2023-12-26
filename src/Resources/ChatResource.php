<?php

namespace Dd1\Chat\Resources;

use Carbon\Carbon;
use Dd1\Chat\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $users = $this->resource->users;
        $companion = $users->where('id', '!=', auth()->id())->first();
        $lastMessage = $this->resource->messages->sortByDesc('id')->first();
        $data = [
            'id' => $this->resource->id,
            'companion_name' => $companion->name,
            'avatar' => $companion->avatar,
            'last_message' => null
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
        return $data;
    }
}
