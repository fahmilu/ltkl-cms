<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ParticipationPathwayResource extends JsonResource
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
        $data['components'] = $this->components($data['components'] ?? null);
        $data['components_id'] = $this->components($data['components_id'] ?? null);

        return $data;
    }

    /**
     * Resolve uploads to URLs and shift headings, matching how page components
     * are served.
     *
     * @return array<int, array<string, mixed>>
     */
    private function components($components): array
    {
        if (!is_array($components)) {
            return [];
        }

        return array_values(array_map(function ($component): array {
            $component = is_array($component) ? $component : [];
            $type = $component['type'] ?? null;
            $blockData = $component['data'] ?? [];

            if ($type === 'lead_text') {
                $blockData['lead'] = $this->convertHeadings($blockData['lead'] ?? null);
            } else if ($type === 'text_image') {
                $blockData['lead'] = $this->convertHeadings($blockData['lead'] ?? null);
                $blockData['description'] = $this->convertHeadings($blockData['description'] ?? null);
                $blockData['image'] = !empty($blockData['image'])
                    ? Storage::disk('public')->url($blockData['image'])
                    : null;
            } else if ($type === 'stats') {
                $blockData['items'] = $this->statsItems($blockData['items'] ?? []);
            }

            $component['data'] = $blockData;

            return $component;
        }, $components));
    }

    /**
     * Keep every stats row on the same three keys, so a row saved before the
     * unit field existed still serialises with a unit.
     *
     * @return array<int, array<string, mixed>>
     */
    private function statsItems($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item): array {
            $item = is_array($item) ? $item : [];

            return [
                'title' => $item['title'] ?? null,
                'value' => $item['value'] ?? null,
                'unit' => $item['unit'] ?? null,
            ];
        }, $items));
    }

    private function convertHeadings(?string $content): ?string
    {
        if (empty($content)) {
            return $content;
        }

        // Convert H3 to H4 first (so existing H3s become H4)
        $content = preg_replace('/<h3([^>]*)>/i', '<h4$1>', $content);
        $content = preg_replace('/<\/h3>/i', '</h4>', $content);

        // Then convert H2 to H3 (so H2s become H3, and won't be affected by H3→H4 conversion)
        $content = preg_replace('/<h2([^>]*)>/i', '<h3$1>', $content);
        $content = preg_replace('/<\/h2>/i', '</h3>', $content);

        return $content;
    }
}
