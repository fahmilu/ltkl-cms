<?php

namespace App\Http\Resources;

use App\Enums\ImpactType;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
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

        // The story block is served as one object per language, so the flat
        // story_* columns are dropped from the payload.
        $posts = $this->storyPosts();

        $data['story'] = $this->story('', $posts);
        $data['story_id'] = $this->story('_id', $posts);

        return Arr::except($data, [
            'story_label',
            'story_label_id',
            'story_title',
            'story_title_id',
            'story_description',
            'story_description_id',
            'story_image',
            'story_image_id',
            'story_posts',
            'story_posts_id',
        ]);
    }

    /**
     * The "Cerita Gerakan" block of one language.
     *
     * @param  \Illuminate\Support\Collection<int, Post>  $posts
     * @return array<string, mixed>
     */
    private function story(string $suffix, $posts): array
    {
        $image = $this->{'story_image' . $suffix};

        return [
            'label' => $this->{'story_label' . $suffix} ?: null,
            'title' => $this->{'story_title' . $suffix} ?: null,
            'description' => $this->{'story_description' . $suffix} ?: null,
            'image' => $image ? Storage::disk('public')->url($image) : null,
            // The picked order is the editor's, so it is kept rather than the
            // order the posts come back from the database in.
            'posts' => array_values(array_filter(array_map(
                fn($id): ?array => ($post = $posts->get((int) $id)) ? $this->storyPost($post) : null,
                $this->storyPostIds($suffix),
            ))),
        ];
    }

    /**
     * Both languages are resolved in one query, so a list of kabupatens does not
     * fire a lookup per language per record.
     *
     * @return \Illuminate\Support\Collection<int, Post>
     */
    private function storyPosts()
    {
        $ids = array_unique([...$this->storyPostIds(''), ...$this->storyPostIds('_id')]);

        if ($ids === []) {
            return collect();
        }

        // Only published posts, so unpublishing one takes it off the kabupaten
        // page without anyone having to edit the kabupaten.
        return Post::where('is_active', true)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    /**
     * @return array<int, int>
     */
    private function storyPostIds(string $suffix): array
    {
        $ids = $this->{'story_posts' . $suffix};

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_map('intval', array_filter($ids, 'is_numeric')));
    }

    /**
     * A slim post reference. The full post lives at /api/post/{slug}.
     *
     * @return array<string, mixed>
     */
    private function storyPost(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'title_id' => $post->title_id,
            'slug' => $post->slug,
            'slug_id' => $post->slug_id,
            'lead' => $post->lead,
            'lead_id' => $post->lead_id,
            'image' => $post->image ? Storage::disk('public')->url($post->image) : null,
            'published_at' => $post->published_at,
        ];
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
