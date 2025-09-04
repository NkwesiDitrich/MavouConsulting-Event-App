<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slogan' => $this->slogan,
            'description' => $this->description,
            'event_type' => $this->event_type,
            'audience' => $this->audience,
            'location' => $this->location,
            'image_url' => $this->image_url,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'category_id' => $this->category_id,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'attendees_count' => $this->whenCounted('attendees'),
            'attendees' => AttendeeResource::collection($this->whenLoaded('attendees')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}