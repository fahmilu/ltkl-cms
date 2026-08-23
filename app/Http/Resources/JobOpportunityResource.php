<?php

namespace App\Http\Resources;

use App\Enums\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class JobOpportunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['status'] = JobStatus::fromState($this->status)->value;
        // Taking applications is the status alone: a passed deadline does not
        // close a vacancy, so the frontend can show both facts as they are.
        $data['is_open'] = $data['status'] === JobStatus::OPEN->value;
        $data['attachment'] = $this->attachment
            ? Storage::disk('public')->url($this->attachment)
            : null;
        $data['posted_at'] = $this->posted_at?->toDateString();
        $data['deadline_at'] = $this->deadline_at?->toDateString();

        return $data;
    }
}
