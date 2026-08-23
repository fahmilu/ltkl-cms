<?php

namespace App\Http\Resources;

use App\Enums\BlockBackgroundColor;
use App\Enums\CollectionComponentSource;
use App\Enums\CollectionDisplay;
use App\Enums\ImagePosition;
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
                $data['components'][$key]['data'] = $this->collectionBlock($component['data'] ?? []);
            } else if ($component['type'] == 'banner_statistic') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if (in_array($component['type'], ['the_values', 'the_problems', 'the_vision'], true)) {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                // Only The Vision illustrates its items.
                $data['components'][$key]['data']['items'] = $this->listItems(
                    $component['data']['items'] ?? [],
                    withImage: $component['type'] === 'the_vision',
                );
            } else if ($component['type'] == 'journey') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components'][$key]['data']['items'] = $this->journeySteps($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components'][$key]['data'] = $this->textImage($component['data'] ?? []);
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
                $data['components_id'][$key]['data'] = $this->collectionBlock($component['data'] ?? []);
            } else if ($component['type'] == 'banner_statistic') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if (in_array($component['type'], ['the_values', 'the_problems', 'the_vision'], true)) {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                // Only The Vision illustrates its items.
                $data['components_id'][$key]['data']['items'] = $this->listItems(
                    $component['data']['items'] ?? [],
                    withImage: $component['type'] === 'the_vision',
                );
            } else if ($component['type'] == 'journey') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components_id'][$key]['data']['items'] = $this->journeySteps($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components_id'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components_id'][$key]['data'] = $this->textImage($component['data'] ?? []);
            }
        }
        return $data;
    }

    /**
     * Normalise a Collection block.
     *
     * The display is only part of the pathways source, which renders three ways;
     * every other source has one layout, so it publishes no display at all.
     * Pathway blocks saved before the choice existed read as the side accordion
     * they already render as.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function collectionBlock($data): array
    {
        $data = is_array($data) ? $data : [];

        $data['description'] = $this->convertHeadings($data['description'] ?? null);
        $data['display'] = ($data['source'] ?? null) === CollectionComponentSource::PARTICIPATION_PATHWAYS->value
            ? CollectionDisplay::fromState($data['display'] ?? null)->value
            : null;

        return $data;
    }

    /**
     * Normalise a Text Image block, so every block serialises on the same keys.
     *
     * Blocks saved before the block styling existed carry none of those keys:
     * they read as a plain, uncoloured section with the image on the right.
     * The colour is only published when the block is actually filled.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function textImage($data): array
    {
        $data = is_array($data) ? $data : [];

        $data['lead'] = $this->convertHeadings($data['lead'] ?? null);
        $data['description'] = $this->convertHeadings($data['description'] ?? null);
        $data['is_block'] = (bool) ($data['is_block'] ?? false);
        $data['background_color'] = $data['is_block']
            ? BlockBackgroundColor::fromState($data['background_color'] ?? null)
            : null;
        $data['image_position'] = ImagePosition::fromState($data['image_position'] ?? null)->value;

        return $data;
    }

    /**
     * Normalise the items of The Values, The Problems and The Vision, so every
     * row serialises on the same keys. The image is only part of The Vision.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function listItems($items, bool $withImage = false): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item) use ($withImage): array {
            $item = is_array($item) ? $item : [];

            $row = [
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
            ];

            if ($withImage) {
                $row['image'] = !empty($item['image'])
                    ? Storage::disk('public')->url($item['image'])
                    : null;
            }

            return $row;
        }, $items));
    }

    /**
     * Normalise the Journey steps. The number is not an input: it comes from the
     * row order, so reordering the steps renumbers them.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function journeySteps($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $steps = [];
        foreach (array_values($items) as $index => $item) {
            $item = is_array($item) ? $item : [];

            $steps[] = [
                'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'title' => $item['title'] ?? null,
                'period' => $item['period'] ?? null,
                'description' => $item['description'] ?? null,
                // Always a real boolean, including for steps saved before the toggle existed.
                'is_past' => (bool) ($item['is_past'] ?? false),
            ];
        }

        return $steps;
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
