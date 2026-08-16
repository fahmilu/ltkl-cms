<?php

namespace App\Http\Resources;

use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PillarResource extends JsonResource
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

        // "Pilar 01" is the list position. The controller stamps it on the model
        // so it always matches the order the API returns.
        $data['number'] = isset($data['number']) ? (int) $data['number'] : null;

        $data['statistics'] = $this->statistics($this->statistics);
        $data['statistics_id'] = $this->statistics($this->statistics_id);
        $data['results'] = $this->results($this->results);
        $data['results_id'] = $this->results($this->results_id);

        $data['kabupatens_count'] = Kabupaten::where('is_active', true)->count();

        $data['practices'] = $this->whenLoaded('practices', fn(): array => $this->practices
            ->map(fn($practice): array => [
                'id' => $practice->id,
                'since_year' => $practice->since_year,
                'title' => $practice->title,
                'title_id' => $practice->title_id,
                'description' => $practice->description,
                'description_id' => $practice->description_id,
                'image' => $practice->image
                    ? Storage::disk('public')->url($practice->image)
                    : null,
                'kabupaten' => $practice->relationLoaded('kabupaten') && $practice->kabupaten
                    ? [
                        'id' => $practice->kabupaten->id,
                        'slug' => $practice->kabupaten->slug,
                        'slug_id' => $practice->kabupaten->slug_id,
                        'title' => $practice->kabupaten->title,
                        'title_id' => $practice->kabupaten->title_id,
                    ]
                    : null,
            ])
            ->all(), []);

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statistics($rows): array
    {
        return $this->rows($rows, fn(array $row): array => [
            'value' => $row['value'] ?? null,
            'label' => $row['label'] ?? null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function results($rows): array
    {
        return $this->rows($rows, fn(array $row): array => [
            'value' => $row['value'] ?? null,
            'title' => $row['title'] ?? null,
            'description' => $row['description'] ?? null,
            'source' => $row['source'] ?? null,
        ]);
    }

    /**
     * Repeater state is keyed by uuid in the database, so it is re-indexed into a
     * plain list here to keep the stored order and serialise as a JSON array.
     */
    private function rows($rows, callable $map): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_map(
            fn($row) => $map(is_array($row) ? $row : []),
            $rows
        ));
    }
}
