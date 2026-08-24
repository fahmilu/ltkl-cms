<?php

namespace App\Http\Resources;

use App\Enums\PostType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['type'] = $this->postType()?->value;
        $data['image'] = $this->image ? Storage::disk('public')->url($this->image) : null;
        $data['meta_image'] = $this->meta_image ? Storage::disk('public')->url($this->meta_image) : null;
        $data['external_file'] = $this->external_file ? Storage::disk('public')->url($this->external_file) : null;
        $data['components'] = isset($data['components']) ? array_filter(
            $data['components'],
            fn($component) => !empty($component['data']['is_active'])
        ) : [];
        $data['components_id'] = isset($data['components_id']) ? array_filter(
            $data['components_id'],
            fn($component) => !empty($component['data']['is_active'])
        ) : [];
        foreach ($data['components'] as $key => $component) {
            if ($component['type'] == 'paragraph') {
                $data['components'][$key]['data']['content'] = $this->convertHeadings($component['data']['content']);
            } else if ($component['type'] == 'lead_text') {
                $data['components'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead']);
            } else if ($component['type'] == 'quote') {
                $data['components'][$key]['data']['quote'] = $this->convertHeadings($component['data']['quote']);
            }
        }
        foreach ($data['components_id'] as $key => $component) {
            if ($component['type'] == 'paragraph') {
                $data['components_id'][$key]['data']['content'] = $this->convertHeadings($component['data']['content']);
            } else if ($component['type'] == 'lead_text') {
                $data['components_id'][$key]['data']['lead'] = $this->convertHeadings($component['data']['lead']);
            } else if ($component['type'] == 'quote') {
                $data['components_id'][$key]['data']['quote'] = $this->convertHeadings($component['data']['quote']);
            }
        }

        $data['lead'] = $this->convertHeadings($data['lead'] ?? null);
        $data['lead_id'] = $this->convertHeadings($data['lead_id'] ?? null);

        $data['type_data'] = $this->typeData();

        // The nested kabupaten is only a reference here, so its own page payload
        // (commodities, achievements) is left out to keep post responses small.
        if (isset($data['post_kabupatens'])) {
            $data['post_kabupatens'] = array_map(
                fn(array $kabupaten) => Arr::except($kabupaten, [
                    'commodities',
                    'commodities_id',
                    'achievements',
                    'achievements_id',
                ]),
                $data['post_kabupatens']
            );
        }

        return $data;
    }

    /**
     * The type column is cast to an enum, but stays a plain string on unsaved instances.
     */
    private function postType(): ?PostType
    {
        if ($this->type instanceof PostType) {
            return $this->type;
        }

        return $this->type ? PostType::tryFrom((string) $this->type) : null;
    }

    /**
     * Resolve the stored type_data json into the shape the frontend consumes:
     * upload paths become full URLs and a few values that cannot be derived
     * client side are computed here.
     */
    private function typeData(): array
    {
        $typeData = is_array($this->type_data) ? $this->type_data : [];

        return match ($this->postType()) {
            PostType::ARTICLE => $this->articleData($typeData),
            PostType::VIDEO => $this->videoData($typeData),
            PostType::EVENT => $this->eventData($typeData),
            PostType::LIBRARY => $this->libraryData($typeData),
            PostType::MEDIA_COVERAGE => $this->mediaCoverageData($typeData),
            default => $typeData,
        };
    }

    private function articleData(array $typeData): array
    {
        return [
            'author' => $typeData['author'] ?? null,
            'read_time' => isset($typeData['read_time'])
                ? (int) $typeData['read_time']
                : $this->estimatedReadTime(),
        ];
    }

    private function videoData(array $typeData): array
    {
        $videoUrl = $typeData['video_url'] ?? null;

        return [
            'video_url' => $videoUrl,
            'embed_url' => $this->embedUrl($videoUrl),
            'duration' => $typeData['duration'] ?? null,
            'subtitles' => array_values($typeData['subtitles'] ?? []),
        ];
    }

    private function eventData(array $typeData): array
    {
        $startDate = $typeData['start_date'] ?? null;
        $endDate = $typeData['end_date'] ?? null;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_multi_day' => (bool) ($endDate && $endDate !== $startDate),
            'start_time' => $typeData['start_time'] ?? null,
            'end_time' => $typeData['end_time'] ?? null,
            'timezone' => $typeData['timezone'] ?? null,
            'register_url' => $typeData['register_url'] ?? null,
            'is_public' => (bool) ($typeData['is_public'] ?? false),
            'is_registration_open' => (bool) ($typeData['is_registration_open'] ?? false),
            'location' => $typeData['location'] ?? null,
            'location_id' => $typeData['location_id'] ?? null,
            'cost' => $typeData['cost'] ?? null,
            'cost_id' => $typeData['cost_id'] ?? null,
        ];
    }

    private function libraryData(array $typeData): array
    {
        return [
            'pages' => isset($typeData['pages']) ? (int) $typeData['pages'] : null,
            'license' => $typeData['license'] ?? null,
            'cover' => $this->fileUrl($typeData['cover'] ?? null),
            'file' => $this->fileUrl($typeData['file'] ?? null),
            'file_meta' => $this->fileMeta($typeData['file'] ?? null),
            'file_id' => $this->fileUrl($typeData['file_id'] ?? null),
            'file_id_meta' => $this->fileMeta($typeData['file_id'] ?? null),
            'access_note' => $typeData['access_note'] ?? null,
            'access_note_id' => $typeData['access_note_id'] ?? null,
        ];
    }

    private function mediaCoverageData(array $typeData): array
    {
        return [
            'publisher_name' => $typeData['publisher_name'] ?? null,
            'publisher_logo' => $this->fileUrl($typeData['publisher_logo'] ?? null),
            'journalist' => $typeData['journalist'] ?? null,
            'source_published_at' => $typeData['source_published_at'] ?? null,
            'source_url' => $typeData['source_url'] ?? null,
        ];
    }

    private function fileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Format and size of a stored document, used for the "PDF · 4,2 MB" label.
     * Returns null when the file is missing from disk.
     */
    private function fileMeta(?string $path): ?array
    {
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $bytes = Storage::disk('public')->size($path);

        return [
            'extension' => Str::upper(pathinfo($path, PATHINFO_EXTENSION)),
            'size' => $bytes,
            'size_label' => $this->humanFileSize($bytes),
        ];
    }

    private function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $size = $bytes / (1024 ** $power);

        return ($power === 0 ? (string) $bytes : number_format($size, 1)) . ' ' . $units[$power];
    }

    /**
     * Turn a YouTube or Vimeo watch URL into its embeddable equivalent.
     */
    private function embedUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return $url;
    }

    /**
     * Fallback reading time in minutes, based on 200 words per minute
     * across the English component content.
     */
    private function estimatedReadTime(): ?int
    {
        $components = is_array($this->components) ? $this->components : [];

        if ($components === [] && blank($this->lead)) {
            return null;
        }

        $text = (string) $this->lead;
        foreach ($components as $component) {
            foreach (['content', 'lead', 'quote'] as $field) {
                $text .= ' ' . ($component['data'][$field] ?? '');
            }
        }

        $words = str_word_count(strip_tags($text));

        return $words > 0 ? max(1, (int) ceil($words / 200)) : null;
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
