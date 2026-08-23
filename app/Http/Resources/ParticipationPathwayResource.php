<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ParticipationPathwayResource extends JsonResource
{
    /**
     * Columns still on the table but no longer part of a pathway, so they stay
     * out of the payload until the migration drops them.
     *
     * @var array<int, string>
     */
    private const DROPPED = [
        'image',
        'components',
        'components_id',
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return Arr::except(parent::toArray($request), self::DROPPED);
    }
}
