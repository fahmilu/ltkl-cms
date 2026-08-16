<?php

namespace Database\Seeders;

use App\Enums\CollectionType;
use App\Enums\PostType;
use App\Models\Collection;
use App\Models\Kabupaten;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Number of dummy posts generated for each PostType.
     */
    private const PER_TYPE = 3;

    /**
     * Seed dummy posts, three for every PostType.
     */
    public function run(): void
    {
        $faker = fake();

        $tagIds = Collection::where('type', CollectionType::TAG->value)->pluck('id')->all();
        $topicIds = Collection::where('type', CollectionType::TOPIC->value)->pluck('id')->all();
        $kabupatenIds = Kabupaten::pluck('id')->all();

        $index = 0;

        foreach (PostType::cases() as $type) {
            for ($i = 1; $i <= self::PER_TYPE; $i++) {
                $index++;

                $title = ucfirst($faker->words(rand(4, 7), true));
                $titleId = ucfirst($faker->words(rand(4, 7), true));
                $publishedAt = Carbon::now()->subDays($index * 3)->setTime(rand(8, 17), 0);

                $post = Post::create([
                    'is_active' => true,
                    'type' => $type->value,
                    'type_data' => $this->typeData($type, $faker, $publishedAt),
                    'title' => $title,
                    'title_id' => $titleId,
                    'slug' => Str::slug($title) . '-' . $index,
                    'slug_id' => Str::slug($titleId) . '-' . $index,
                    'components' => $this->components($faker),
                    'components_id' => $this->components($faker),
                    'meta_title' => $title,
                    'meta_description' => $faker->sentence(15),
                    'is_featured' => $i === 1,
                    'is_external_url' => false,
                    'published_at' => $publishedAt,
                ]);

                if ($tagIds !== []) {
                    $post->post_tags()->sync($faker->randomElements($tagIds, min(2, count($tagIds))));
                }

                if ($topicIds !== []) {
                    $post->post_topics()->sync($faker->randomElements($topicIds, min(2, count($topicIds))));
                }

                if ($kabupatenIds !== []) {
                    $post->post_kabupatens()->sync($faker->randomElements($kabupatenIds, min(1, count($kabupatenIds))));
                }
            }
        }

        $this->command?->info('Seeded ' . $index . ' posts across ' . count(PostType::cases()) . ' types.');
    }

    /**
     * Build the type specific payload stored in the posts.type_data json column.
     */
    private function typeData(PostType $type, $faker, Carbon $publishedAt): array
    {
        return match ($type) {
            PostType::ARTICLE => [
                'author' => $faker->name(),
                'read_time' => rand(3, 12),
            ],

            PostType::VIDEO => [
                'video_url' => 'https://www.youtube.com/watch?v=' . Str::random(11),
                'duration' => sprintf('%d:%02d', rand(2, 25), rand(0, 59)),
                'subtitles' => $faker->randomElements(['id', 'en'], rand(1, 2)),
            ],

            PostType::EVENT => $this->eventData($faker, $publishedAt),

            PostType::LIBRARY => [
                'pages' => rand(12, 180),
                'license' => $faker->randomElement(['CC BY 4.0', 'CC BY-SA 4.0', 'CC BY-NC 4.0']),
                'cover' => null,
                'file' => null,
                'file_id' => null,
                'access_note' => ucfirst($faker->words(3, true)),
                'access_note_id' => ucfirst($faker->words(3, true)),
            ],

            PostType::MEDIA_COVERAGE => [
                'publisher_name' => $faker->company(),
                'journalist' => $faker->name(),
                'source_published_at' => $publishedAt->copy()->subDays(rand(1, 10))->toDateString(),
                'source_url' => $faker->url(),
                'publisher_logo' => null,
            ],
        };
    }

    /**
     * Events run for one to five days starting a few weeks after publication.
     */
    private function eventData($faker, Carbon $publishedAt): array
    {
        $startDate = $publishedAt->copy()->addDays(rand(14, 60));
        $startHour = rand(8, 12);

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays(rand(0, 4))->toDateString(),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $startHour + rand(3, 9)),
            'timezone' => $faker->randomElement(['WIB', 'WITA', 'WIT']),
            'register_url' => $faker->url(),
            'is_public' => true,
            'is_registration_open' => (bool) rand(0, 1),
            'location' => ucfirst($faker->words(3, true)),
            'location_id' => ucfirst($faker->words(3, true)),
            'cost' => ucfirst($faker->words(3, true)),
            'cost_id' => ucfirst($faker->words(3, true)),
        ];
    }

    /**
     * A couple of paragraph blocks matching the component builder state shape.
     */
    private function components($faker): array
    {
        return collect(range(1, rand(2, 4)))
            ->map(fn() => [
                'type' => 'paragraph',
                'data' => [
                    'is_active' => true,
                    'title' => ucfirst($faker->words(rand(2, 5), true)),
                    'content' => '<p>' . $faker->paragraph(6) . '</p>',
                ],
            ])
            ->all();
    }
}
