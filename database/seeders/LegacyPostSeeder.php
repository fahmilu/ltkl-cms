<?php

namespace Database\Seeders;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Seeds the article, event, media coverage and library posts migrated from the
 * legacy site (legacy.kabupatenlestari.org), from 2022 onwards (plus the two
 * older 2017 media coverage items).
 *
 * The data lives in database/seeders/data/legacy_posts.json. Images were
 * stripped from the content and are uploaded manually. Library documents and
 * covers are downloaded from the legacy site into the public disk. Posts are
 * matched by slug, so re-running the seeder updates them instead of
 * duplicating.
 */
class LegacyPostSeeder extends Seeder
{
    private const DATA_FILE = 'seeders/data/legacy_posts.json';

    private const FILE_DIRECTORY = 'posts';

    /**
     * Library type_data keys that hold a legacy file URL to download.
     */
    private const LIBRARY_FILE_KEYS = ['cover', 'file', 'file_id'];

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
                    'type_data' => $item['type'] === PostType::LIBRARY->value
                        ? $this->libraryTypeData($item['type_data'])
                        : $item['type_data'],
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
     * Swap the legacy file URLs of a library post for paths on the public disk.
     */
    private function libraryTypeData(array $typeData): array
    {
        foreach (self::LIBRARY_FILE_KEYS as $key) {
            $typeData[$key] = $this->download($typeData[$key] ?? null);
        }

        return $typeData;
    }

    /**
     * Download a legacy file into the public disk, skipping files already there.
     * Returns the stored path, or null when the download fails.
     */
    private function download(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $path = self::FILE_DIRECTORY . '/' . Str::afterLast(rawurldecode(parse_url($url, PHP_URL_PATH)), '/');
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        // Stream to a temp file so large PDFs never sit fully in memory.
        $temp = tempnam(sys_get_temp_dir(), 'legacy');

        try {
            Http::timeout(300)->retry(2, 1000)->sink($temp)->get($url);

            $stream = fopen($temp, 'r');
            $disk->writeStream($path, $stream);
            fclose($stream);
        } catch (\Throwable $e) {
            $this->command?->warn('Failed to download ' . $url . ': ' . $e->getMessage());

            return null;
        } finally {
            @unlink($temp);
        }

        return $path;
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
