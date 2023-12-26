<?php

namespace Dd1\Chat\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'text' => $this->resource->text,
            'user' => UserResource::make($this->resource->user),
            'was_read' => $this->resource->was_read,
            'timestamp' => Carbon::parse($this->resource->created_at)->format('H:i'),
        ];
    }
}
