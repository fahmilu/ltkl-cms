<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PageResource extends JsonResource
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
        $data['meta_image'] = $this->meta_image ? Storage::disk('public')->url($this->meta_image) : null;
        /* $data['components'] = isset($data['components']) ? array_filter(
            $data['components'],
            fn($component) => !empty($component['data']['is_active']) 
        ) : [];
        $data['components_id'] = isset($data['components_id']) ? array_filter(
            $data['components_id'],
            fn($component) => !empty($component['data']['is_active']) 
        ) : []; */
        foreach ($data['components'] as $key => $component) {
            // Images type
            // Every optional key is read defensively: blocks saved before a field
            // was added to the builder simply have no key for it.
            if ($component['type'] == 'images') {
                $imageItem = [];
                foreach ($component['data']['image'] ?? [] as $image) {
                    $imageItem[] = $image ? Storage::disk('public')->url($image) : null;
                }
                $data['components'][$key]['data']['image'] = $imageItem;
            }
            else if ($component['type'] == 'paragraph') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'collection') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'your_role') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components'][$key]['data']['items'] = $this->yourRoleItems($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            }
        }
        foreach ($data['components_id'] as $key => $component) {
            // Images type
            if ($component['type'] == 'images') {
                $imageItem = [];
                foreach ($component['data']['image'] ?? [] as $image) {
                    $imageItem[] = $image ? Storage::disk('public')->url($image) : null;
                }
                $data['components_id'][$key]['data']['image'] = $imageItem;
            } else if ($component['type'] == 'paragraph') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'collection') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'your_role') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components_id'][$key]['data']['items'] = $this->yourRoleItems($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components_id'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components_id'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            }
        }
        return $data;
    }

    /**
     * Normalise the Your Role items: every key is always present and the icon
     * upload path becomes a full URL.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function yourRoleItems($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item): array {
            $item = is_array($item) ? $item : [];

            return [
                'label' => $item['label'] ?? null,
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
                'icon' => !empty($item['icon'])
                    ? Storage::disk('public')->url($item['icon'])
                    : null,
                'link' => $item['link'] ?? null,
                'link_label' => $item['link_label'] ?? null,
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
