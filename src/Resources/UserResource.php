<?php

namespace Dd1\Chat\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'avatar' => $this->resource->avatar,
            'is_online' => $this->resource->is_online,
            'name' => $this->resource->name,
            'last_seen_at' => Carbon::parse($this->resource->last_seen_at)->format('d.m.Y H:i'),
        ];
    }
}
