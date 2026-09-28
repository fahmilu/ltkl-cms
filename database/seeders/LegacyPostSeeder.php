<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Seeds the article, event and media coverage posts migrated from the legacy
 * site (legacy.kabupatenlestari.org), from 2022 onwards (plus the two older
 * 2017 media coverage items).
 *
 * The data lives in database/seeders/data/legacy_posts.json. Images were
 * stripped from the content and are uploaded manually. Posts are matched by
 * slug, so re-running the seeder updates them instead of duplicating.
 */
class LegacyPostSeeder extends Seeder
{
    private const DATA_FILE = 'seeders/data/legacy_posts.json';

    public function run(): void
    {
        $path = database_path(self::DATA_FILE);

        if (! file_exists($path)) {
            throw new RuntimeException('Legacy post data not found at ' . $path);
        }

        $posts = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($posts as $item) {
            Post::withTrashed()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'is_active' => true,
                    'type' => $item['type'],
                    'type_data' => $item['type_data'],
                    'title' => $item['title'],
                    'title_id' => $item['title_id'],
                    'slug_id' => $item['slug_id'],
                    'components' => $this->components($item['content']),
                    'components_id' => $this->components($item['content_id']),
                    'meta_title' => Str::limit($item['title'], 250, ''),
                    'meta_description' => $item['meta_description'],
                    'is_featured' => false,
                    'is_external_url' => false,
                    'published_at' => Carbon::parse($item['published_at']),
                ]
            );
        }

        $this->command?->info('Seeded ' . count($posts) . ' legacy posts.');
    }

    /**
     * Wrap the legacy body in a single paragraph block of the component builder.
     */
    private function components(?string $content): array
    {
        if (blank($content)) {
            return [];
        }

        return [
            [
                'type' => 'paragraph',
                'data' => [
                    'is_active' => true,
                    'title' => null,
                    'content' => $content,
                ],
            ],
        ];
    }
}
