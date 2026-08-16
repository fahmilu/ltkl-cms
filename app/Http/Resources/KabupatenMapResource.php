<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Slim payload for the member map: only what a pin needs to be drawn and linked.
 */
class KabupatenMapResource extends JsonResource
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
            'slug' => $this->slug,
            'slug_id' => $this->slug_id,
            'title' => $this->title,
            'title_id' => $this->title_id,
            'role' => $this->role,
            'role_id' => $this->role_id,
            'city' => $this->city,
            'province' => $this->province,
            'is_founding_member' => (bool) $this->is_founding_member,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }
}
