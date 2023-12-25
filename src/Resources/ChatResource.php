<?php

namespace Dd1\Chat\Resources;

use Carbon\Carbon;
use Dd1\Chat\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public $resource;
    public $sender;
    public $companion;
    public function __construct($resource, $sender = null, $companion = null)
    {
        parent::__construct($resource);
        if ($sender === null || $companion === null) {
            $users = $this->resource->users;
        }
        $this->sender = $sender && gettype($sender) !== 'integer' ? $sender : $users->where('id', auth()->id())->first();
        $this->companion = $companion ?: $users->where('id', '!=', auth()->id())->first();
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastMessage = $this->resource->messages()->latest()->first();
        $data = [
            'id' => $this->resource->id,
            'companion_name' => $this->companion?->name,
            'avatar' => $this->companion->avatar,
            'last_message' => null
        ];
        if ($lastMessage) {
            $data['last_message'] = [
                'text' => $lastMessage->text,
                'timestamp' => Carbon::parse($lastMessage->created_at)->format('H:i'),
                'sender_id' => $lastMessage->user->id,
                'sender_name' => $lastMessage->user->name,
            ];
        }
        return $data;
    }
}
