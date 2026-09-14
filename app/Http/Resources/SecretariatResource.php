<?php

namespace App\Http\Resources;

use App\Enums\SecretariatLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SecretariatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['image'] = $this->image ? Storage::disk('public')->url($this->image) : null;

        // The model casts the column to the enum already; a row saved before the
        // column existed reads as the default rank rather than failing to serialise.
        $level = $this->level instanceof SecretariatLevel ? $this->level : SecretariatLevel::STAFF;
        $data['level'] = $level->value;
        $data['level_label'] = $level->getLabel();

        return $data;
    }
}
