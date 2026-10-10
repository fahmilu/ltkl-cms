<?php

namespace App\Http\Resources;

use App\Enums\BlockBackgroundColor;
use App\Enums\CollectionComponentSource;
use App\Enums\CollectionDisplay;
use App\Enums\ImagePosition;
use App\Enums\InfoListLayout;
use App\Models\Kabupaten;
use App\Support\ComponentAnchors;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PageResource extends JsonResource
{
    /**
     * Kabupatens referenced by Text Image blocks, looked up once each. The same
     * kabupaten is usually referenced by both the English and Indonesian copy
     * of a block.
     *
     * @var array<int, Kabupaten|null>
     */
    private array $kabupatenCache = [];

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
        // Anchors for the sections flagged as submenu entries, matching the ones
        // the menus endpoint lists, so a menu link has something to scroll to.
        foreach (ComponentAnchors::map($data['components'] ?? []) as $key => $anchor) {
            $data['components'][$key]['data']['anchor'] = $anchor;
        }

        foreach (ComponentAnchors::map($data['components_id'] ?? []) as $key => $anchor) {
            $data['components_id'][$key]['data']['anchor'] = $anchor;
        }

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
            } else if ($component['type'] == 'embed_iframe') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'collection') {
                $data['components'][$key]['data'] = $this->collectionBlock($component['data'] ?? []);
            } else if ($component['type'] == 'banner_statistic') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'statistic_with_title') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if (in_array($component['type'], ['the_values', 'the_problems', 'the_vision', 'kompas_kabupaten_lestari', 'info_list'], true)) {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                // The Vision and The Problems illustrate their items. Kompas
                // Kabupaten Lestari shares The Vision's shape, plus a label.
                // Info List shares The Values' shape, plus a layout choice.
                $data['components'][$key]['data']['items'] = $this->listItems(
                    $component['data']['items'] ?? [],
                    withImage: in_array($component['type'], ['the_vision', 'the_problems', 'kompas_kabupaten_lestari'], true),
                    withLabel: $component['type'] === 'kompas_kabupaten_lestari',
                );
                if ($component['type'] === 'info_list') {
                    $data['components'][$key]['data']['layout'] = InfoListLayout::tryFrom((string) ($component['data']['layout'] ?? ''))?->value
                        ?? InfoListLayout::ACCORDION->value;
                }
            } else if ($component['type'] == 'journey') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components'][$key]['data']['items'] = $this->journeySteps($component['data']['items'] ?? []);
            } else if ($component['type'] == 'impact_of_change') {
                $data['components'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components'][$key]['data']['items'] = $this->impactOfChangeItems($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components'][$key]['data'] = $this->textImage($component['data'] ?? [], 'en');
            } else if ($component['type'] == 'bergabung_form') {
                $data['components'][$key]['data'] = $this->bergabungForm($component['data'] ?? []);
            } else if ($component['type'] == 'annual_reports') {
                $data['components'][$key]['data'] = $this->annualReports($component['data'] ?? []);
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
            } else if ($component['type'] == 'embed_iframe') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'collection') {
                $data['components_id'][$key]['data'] = $this->collectionBlock($component['data'] ?? []);
            } else if ($component['type'] == 'banner_statistic') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'statistic_with_title') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if ($component['type'] == 'latest_news') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
            } else if (in_array($component['type'], ['the_values', 'the_problems', 'the_vision', 'kompas_kabupaten_lestari', 'info_list'], true)) {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                // The Vision and The Problems illustrate their items. Kompas
                // Kabupaten Lestari shares The Vision's shape, plus a label.
                // Info List shares The Values' shape, plus a layout choice.
                $data['components_id'][$key]['data']['items'] = $this->listItems(
                    $component['data']['items'] ?? [],
                    withImage: in_array($component['type'], ['the_vision', 'the_problems', 'kompas_kabupaten_lestari'], true),
                    withLabel: $component['type'] === 'kompas_kabupaten_lestari',
                );
                if ($component['type'] === 'info_list') {
                    $data['components_id'][$key]['data']['layout'] = InfoListLayout::tryFrom((string) ($component['data']['layout'] ?? ''))?->value
                        ?? InfoListLayout::ACCORDION->value;
                }
            } else if ($component['type'] == 'journey') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components_id'][$key]['data']['items'] = $this->journeySteps($component['data']['items'] ?? []);
            } else if ($component['type'] == 'impact_of_change') {
                $data['components_id'][$key]['data']['description'] = $this->convertHeadings($component['data']['description'] ?? null);
                $data['components_id'][$key]['data']['items'] = $this->impactOfChangeItems($component['data']['items'] ?? []);
            } else if ($component['type'] == 'lead_text') {
                $data['components_id'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead'] ?? null);
            } else if ($component['type'] == 'text_image') {
                $data['components_id'][$key]['data'] = $this->textImage($component['data'] ?? [], 'id');
            } else if ($component['type'] == 'bergabung_form') {
                $data['components_id'][$key]['data'] = $this->bergabungForm($component['data'] ?? []);
            } else if ($component['type'] == 'annual_reports') {
                $data['components_id'][$key]['data'] = $this->annualReports($component['data'] ?? []);
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
     * Normalise a Bergabung Form block, so every block serialises on the same
     * keys. The job opportunity and newsletter pointers are sections of their
     * own, each published as a nested object whether or not the editor filled
     * any of it in.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function bergabungForm($data): array
    {
        $data = is_array($data) ? $data : [];

        $data['description'] = $this->convertHeadings($data['description'] ?? null);
        $data['contact_info'] = $this->convertHeadings($data['contact_info'] ?? null);
        $data['job_opportunity'] = $this->bergabungFormSection($data['job_opportunity'] ?? []);
        $data['newsletter'] = $this->bergabungFormSection($data['newsletter'] ?? []);

        return $data;
    }

    /**
     * Normalise a Bergabung Form pointer section (job opportunity, newsletter),
     * so every block serialises on the same keys.
     *
     * @param  mixed  $section
     * @return array<string, mixed>
     */
    private function bergabungFormSection($section): array
    {
        $section = is_array($section) ? $section : [];

        return [
            'label' => $section['label'] ?? null,
            'title' => $section['title'] ?? null,
            'description' => $this->convertHeadings($section['description'] ?? null),
            'button_text' => $section['button_text'] ?? null,
            'button_url' => $section['button_url'] ?? null,
        ];
    }

    /**
     * Normalise a Text Image block, so every block serialises on the same keys.
     *
     * Blocks saved before the block styling existed carry none of those keys:
     * they read as a plain, uncoloured section with the image on the right.
     * The colour is only published when the block is actually filled.
     *
     * The subtitle is a kabupaten reference rather than free text. It publishes
     * as the kabupaten's name in the language of the block, alongside the full
     * record under `kabupaten` so the frontend can link to it. Blocks saved
     * before the field became a reference keep the text they already carry.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function textImage($data, string $locale = 'en'): array
    {
        $data = is_array($data) ? $data : [];

        $data['lead'] = $this->convertHeadings($data['lead'] ?? null);
        $data['description'] = $this->convertHeadings($data['description'] ?? null);
        $data['is_block'] = (bool) ($data['is_block'] ?? false);
        $data['background_color'] = $data['is_block']
            ? BlockBackgroundColor::fromState($data['background_color'] ?? null)
            : null;
        $data['image_position'] = ImagePosition::fromState($data['image_position'] ?? null)->value;

        $kabupaten = $this->resolveKabupaten($data['kabupaten_id'] ?? null);

        $data['kabupaten_id'] = $kabupaten?->id;
        $data['kabupaten'] = $kabupaten ? [
            'id' => $kabupaten->id,
            'slug' => $kabupaten->slug,
            'slug_id' => $kabupaten->slug_id,
            'title' => $kabupaten->title,
            'title_id' => $kabupaten->title_id,
            'city' => $kabupaten->city,
            'province' => $kabupaten->province,
            'image' => $kabupaten->image ? Storage::disk('public')->url($kabupaten->image) : null,
        ] : null;

        if ($kabupaten) {
            $data['subtitle'] = $locale === 'id'
                ? ($kabupaten->title_id ?: $kabupaten->title)
                : ($kabupaten->title ?: $kabupaten->title_id);
        } else {
            $data['subtitle'] = $data['subtitle'] ?? null;
        }

        return $data;
    }

    private function resolveKabupaten($id): ?Kabupaten
    {
        if (blank($id) || ! is_numeric($id)) {
            return null;
        }

        $id = (int) $id;

        return $this->kabupatenCache[$id] ??= Kabupaten::find($id);
    }

    /**
     * Normalise the items of The Values, The Problems, The Vision and Kompas
     * Kabupaten Lestari, so every row serialises on the same keys. The image is
     * part of every one of them but The Values; the label is only part of Kompas
     * Kabupaten Lestari.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function listItems($items, bool $withImage = false, bool $withLabel = false): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item) use ($withImage, $withLabel): array {
            $item = is_array($item) ? $item : [];

            $row = [];

            if ($withLabel) {
                $row['label'] = $item['label'] ?? null;
            }

            $row['title'] = $this->convertHeadings($item['title'] ?? null);
            $row['description'] = $this->convertHeadings($item['description'] ?? null);

            if ($withImage) {
                $row['image'] = !empty($item['image'])
                    ? Storage::disk('public')->url($item['image'])
                    : null;
            }

            return $row;
        }, $items));
    }

    /**
     * Normalise an Annual Reports block, so every block serialises on the same
     * keys. The cover image and every report file are published as full URLs;
     * an upload left empty reads as null rather than dropping the key.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function annualReports($data): array
    {
        $data = is_array($data) ? $data : [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $data['description'] = $this->convertHeadings($data['description'] ?? null);
        $data['cover_image'] = $this->publicUrl($data['cover_image'] ?? null);
        $data['file'] = $this->publicUrl($data['file'] ?? null);
        $data['items'] = array_values(array_map(function ($item): array {
            $item = is_array($item) ? $item : [];

            return [
                'title' => $item['title'] ?? null,
                'file' => $this->publicUrl($item['file'] ?? null),
            ];
        }, $items));

        return $data;
    }

    /**
     * The public URL of a stored upload, or null when nothing was uploaded.
     */
    private function publicUrl($path): ?string
    {
        return is_string($path) && $path !== '' ? Storage::disk('public')->url($path) : null;
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

    /**
     * Normalise the items of an Impact of Change block, so every row serialises
     * on the same keys. The image is published as a full URL, and the link is
     * optional, so a row without one carries null rather than dropping the key.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function impactOfChangeItems($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_map(function ($item): array {
            $item = is_array($item) ? $item : [];

            return [
                'title' => $item['title'] ?? null,
                'description' => $this->convertHeadings($item['description'] ?? null),
                'image' => !empty($item['image'])
                    ? Storage::disk('public')->url($item['image'])
                    : null,
                'link' => ($item['link'] ?? null) ?: null,
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
