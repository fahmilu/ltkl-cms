<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Restore a snapshot of the site content.
 *
 * The data lives in database/seeders/data/content.json, exported from a working
 * database. Rows are matched on their natural key rather than id, so running
 * this repeatedly updates in place instead of duplicating, and ids may differ
 * between environments without breaking the relations.
 */
class ContentSeeder extends Seeder
{
    private const DATA_FILE = 'seeders/data/content.json';

    public function run(): void
    {
        $path = database_path(self::DATA_FILE);

        if (! is_file($path)) {
            throw new RuntimeException('Content snapshot not found at ' . $path);
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        // Collections and kabupatens first: posts and pillars reference them.
        $collectionIds = $this->upsert('collections', $data['collections'] ?? [], 'slug');
        $kabupatenIds = $this->upsert('kabupatens', $data['kabupatens'] ?? [], 'slug');

        $this->upsert('pages', $data['pages'] ?? [], 'slug');
        $this->upsert('participation_pathways', $data['participation_pathways'] ?? [], 'slug');
        $this->upsert('master_links', $data['master_links'] ?? [], 'title');

        $this->seedPillars($data['pillars'] ?? [], $kabupatenIds);
        $this->seedPosts($data['posts'] ?? [], $collectionIds, $kabupatenIds);
        $this->seedSettings($data['settings'] ?? []);

        $this->command?->info(sprintf(
            'Seeded %d pages, %d posts, %d collections, %d kabupatens, %d pillars, %d pathways, %d settings.',
            count($data['pages'] ?? []),
            count($data['posts'] ?? []),
            count($data['collections'] ?? []),
            count($data['kabupatens'] ?? []),
            count($data['pillars'] ?? []),
            count($data['participation_pathways'] ?? []),
            count($data['settings'] ?? []),
        ));
    }

    /**
     * Insert or update rows on a natural key, returning that key mapped to id.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function upsert(string $table, array $rows, string $key): array
    {
        foreach ($rows as $row) {
            if (! isset($row[$key])) {
                continue;
            }

            DB::table($table)->updateOrInsert([$key => $row[$key]], $row);
        }

        return $this->keyBySlug($table, $key);
    }

    /**
     * @return array<string, int>
     */
    private function keyBySlug(string $table, string $key = 'slug'): array
    {
        return DB::table($table)->pluck('id', $key)->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $pillars
     * @param  array<string, int>  $kabupatenIds
     */
    private function seedPillars(array $pillars, array $kabupatenIds): void
    {
        foreach ($pillars as $pillar) {
            $practices = $pillar['practices'] ?? [];
            $kabupatenSlugs = $pillar['kabupaten_slugs'] ?? [];
            unset($pillar['practices'], $pillar['kabupaten_slugs']);

            DB::table('pillars')->updateOrInsert(['slug' => $pillar['slug']], $pillar);

            $pillarId = DB::table('pillars')->where('slug', $pillar['slug'])->value('id');

            // Practices have no natural key, so the set is rebuilt each run.
            DB::table('pillar_practices')->where('pillar_id', $pillarId)->delete();

            foreach ($practices as $practice) {
                $kabupatenSlug = $practice['kabupaten_slug'] ?? null;
                unset($practice['kabupaten_slug']);

                DB::table('pillar_practices')->insert(array_merge($practice, [
                    'pillar_id' => $pillarId,
                    'kabupaten_id' => $kabupatenIds[$kabupatenSlug] ?? null,
                ]));
            }

            $this->syncPivot(
                'kabupaten_pillars',
                'pillar_id',
                $pillarId,
                'kabupaten_id',
                $this->mapSlugs($kabupatenSlugs, $kabupatenIds)
            );
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $posts
     * @param  array<string, int>  $collectionIds
     * @param  array<string, int>  $kabupatenIds
     */
    private function seedPosts(array $posts, array $collectionIds, array $kabupatenIds): void
    {
        foreach ($posts as $post) {
            $relations = [
                'post_tags' => [$post['post_tags'] ?? [], $collectionIds, 'collection_id'],
                'post_topics' => [$post['post_topics'] ?? [], $collectionIds, 'collection_id'],
                'post_kabupatens' => [$post['post_kabupatens'] ?? [], $kabupatenIds, 'kabupaten_id'],
            ];

            unset($post['post_tags'], $post['post_topics'], $post['post_kabupatens']);

            DB::table('posts')->updateOrInsert(['slug' => $post['slug']], $post);

            $postId = DB::table('posts')->where('slug', $post['slug'])->value('id');

            foreach ($relations as $table => [$slugs, $lookup, $column]) {
                $this->syncPivot($table, 'post_id', $postId, $column, $this->mapSlugs($slugs, $lookup));
            }
        }
    }

    /**
     * Settings are keyed by group and key, and their value column holds JSON.
     *
     * @param  array<int, array<string, mixed>>  $settings
     */
    private function seedSettings(array $settings): void
    {
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * @param  array<int, string>  $slugs
     * @param  array<string, int>  $lookup
     * @return array<int, int>
     */
    private function mapSlugs(array $slugs, array $lookup): array
    {
        return array_values(array_filter(array_map(
            fn(string $slug): ?int => $lookup[$slug] ?? null,
            $slugs
        )));
    }

    /**
     * @param  array<int, int>  $relatedIds
     */
    private function syncPivot(string $table, string $ownerColumn, int $ownerId, string $relatedColumn, array $relatedIds): void
    {
        DB::table($table)->where($ownerColumn, $ownerId)->delete();

        foreach ($relatedIds as $relatedId) {
            DB::table($table)->insert([
                $ownerColumn => $ownerId,
                $relatedColumn => $relatedId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
