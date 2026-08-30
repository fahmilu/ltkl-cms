<?php

namespace App\Http\Resources;

use App\Enums\ImpactType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KabupatenResource extends JsonResource
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
        $data['banner'] = $this->banner ? Storage::disk('public')->url($this->banner) : null;

        // The decimal cast hands back strings, so the landscape figures are
        // converted to real numbers for the JSON payload.
        $data['forest_cover_ha'] = $this->number($this->forest_cover_ha);
        $data['protected_area_ha'] = $this->number($this->protected_area_ha);
        $data['social_forestry_tora_ha'] = $this->number($this->social_forestry_tora_ha);
        $data['area_km2'] = $this->number($this->area_km2);
        $data['latitude'] = $this->coordinate($this->latitude);
        $data['longitude'] = $this->coordinate($this->longitude);

        // Slim references only. The full pillar page lives at /api/pillar/{slug}.
        $data['pillars'] = $this->whenLoaded('pillars', fn(): array => $this->pillars
            ->map(fn($pillar): array => [
                'id' => $pillar->id,
                'slug' => $pillar->slug,
                'slug_id' => $pillar->slug_id,
                'title' => $pillar->title,
                'title_id' => $pillar->title_id,
                'technical_term' => $pillar->technical_term,
                'technical_term_id' => $pillar->technical_term_id,
            ])
            ->all(), []);

        // Each language keeps its own list, so the two are normalised separately
        // and may legitimately differ in length.
        $data['commodities'] = $this->commodities($this->commodities);
        $data['commodities_id'] = $this->commodities($this->commodities_id);
        $data['achievements'] = $this->achievements($this->achievements);
        $data['achievements_id'] = $this->achievements($this->achievements_id);

        return $data;
    }

    /**
     * Coordinates stay floats even when whole, so map libraries never receive
     * a latitude typed differently from a longitude.
     */
    private function coordinate($value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function number($value): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Whole hectares and square kilometres stay integers rather than x.0 floats.
        return fmod((float) $value, 1.0) === 0.0 ? (int) $value : (float) $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function commodities($rows): array
    {
        return $this->rows($rows, fn(array $row): array => [
            'name' => $row['name'] ?? null,
            // Optional, so a commodity saved without one still serialises.
            'icon' => !empty($row['icon'])
                ? Storage::disk('public')->url($row['icon'])
                : null,
            'description' => $row['description'] ?? null,
        ]);
    }

    /**
     * An impact row carries only the fields of its own type, with the type
     * itself always present so the frontend can switch on it. Rows saved before
     * the types existed have no type and serialise as data rows, unchanged.
     *
     * @return array<int, array<string, mixed>>
     */
    private function achievements($rows): array
    {
        return $this->rows($rows, function (array $row): array {
            $type = ImpactType::fromState($row['type'] ?? null);

            return match ($type) {
                ImpactType::QUOTE => [
                    'type' => $type->value,
                    'quote' => $row['quote'] ?? null,
                    'name' => $row['name'] ?? null,
                    'image' => !empty($row['image'])
                        ? Storage::disk('public')->url($row['image'])
                        : null,
                ],
                ImpactType::IMAGE_TEXT => [
                    'type' => $type->value,
                    'title' => $row['title'] ?? null,
                    'description' => $row['description'] ?? null,
                    'image' => !empty($row['image'])
                        ? Storage::disk('public')->url($row['image'])
                        : null,
                ],
                ImpactType::TEXT => [
                    'type' => $type->value,
                    'title' => $row['title'] ?? null,
                    'description' => $row['description'] ?? null,
                ],
                ImpactType::DATA => [
                    'type' => $type->value,
                    'value' => $row['value'] ?? null,
                    'title' => $row['title'] ?? null,
                    'description' => $row['description'] ?? null,
                    'source' => $row['source'] ?? null,
                ],
            };
        });
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
