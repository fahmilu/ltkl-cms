<?php

use App\Models\Kabupaten;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeKabupaten(array $overrides = []): Kabupaten
{
    return Kabupaten::create(array_merge([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
        'role' => 'Founding member',
        'role_id' => 'Anggota pendiri',
        'content' => 'The first regency to sign the commitment.',
        'content_id' => 'Kabupaten pertama yang menandatangani komitmen lestari.',
        'forest_cover_ha' => 312000,
        'protected_area_ha' => 57000,
        'social_forestry_tora_ha' => 21000.5,
        'area_km2' => 8556.75,
        'city' => 'Siak',
        'province' => 'Riau',
        'latitude' => 0.8118,
        'longitude' => 101.8,
        'is_founding_member' => true,
        'joined_year' => 2017,
        'sorted_at' => 1,
        'commodities' => [
            ['name' => 'Peat pineapple', 'description' => 'Grown without clearing new land.'],
            ['name' => 'Forest honey', 'description' => null],
        ],
        'commodities_id' => [
            ['name' => 'Nanas gambut', 'description' => 'Ditanam tanpa membuka lahan baru.'],
        ],
        'achievements' => [
            [
                'value' => '12k ha',
                'title' => 'High conservation value areas designated',
                'description' => 'Mapped together with villages.',
                'source' => 'Source: Regent Decree 2024',
            ],
        ],
        'achievements_id' => [
            [
                'value' => '12rb ha',
                'title' => 'Kawasan bernilai konservasi tinggi ditetapkan',
                'description' => 'Dipetakan bersama masyarakat desa.',
                'source' => 'Sumber: SK Bupati 2024',
            ],
        ],
    ], $overrides));
}

it('lists active kabupatens ordered by sorted_at', function () {
    makeKabupaten(['sorted_at' => 2]);
    makeKabupaten([
        'title' => 'Sintang Regency',
        'title_id' => 'Kabupaten Sintang',
        'slug' => 'sintang-regency',
        'slug_id' => 'kabupaten-sintang',
        'sorted_at' => 1,
    ]);

    $response = $this->getJson('/api/kabupatens')->assertOk();

    expect($response->json('data.*.slug'))->toBe(['sintang-regency', 'siak-regency']);
});

it('hides inactive kabupatens from the list', function () {
    makeKabupaten(['is_active' => false]);

    $this->getJson('/api/kabupatens')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('searches by title in either language', function () {
    makeKabupaten();

    $this->getJson('/api/kabupatens?search=Sintang')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/kabupatens?search=Siak')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/kabupatens?search=Kabupaten')->assertOk()->assertJsonCount(1, 'data');
});

it('returns both language lists with their own lengths', function () {
    makeKabupaten();

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['commodities'])->toHaveCount(2)
        ->and($data['commodities_id'])->toHaveCount(1)
        ->and($data['commodities_id'][0])->toBe([
            'name' => 'Nanas gambut',
            'icon' => null,
            'description' => 'Ditanam tanpa membuka lahan baru.',
        ])
        ->and($data['achievements'][0]['value'])->toBe('12k ha')
        ->and($data['achievements_id'][0]['source'])->toBe('Sumber: SK Bupati 2024');
});

it('resolves a kabupaten by either slug', function () {
    makeKabupaten();

    $this->getJson('/api/kabupaten/siak-regency')->assertOk()->assertJsonPath('data.slug', 'siak-regency');
    $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->assertJsonPath('data.slug', 'siak-regency');
});

it('returns 404 for an unknown or inactive slug', function () {
    makeKabupaten(['is_active' => false]);

    $this->getJson('/api/kabupaten/siak-regency')->assertNotFound();
    $this->getJson('/api/kabupaten/nope')->assertNotFound();
});

it('exposes the landscape figures as numbers and the membership fields', function () {
    makeKabupaten();

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['forest_cover_ha'])->toBe(312000)
        ->and($data['protected_area_ha'])->toBe(57000)
        ->and($data['social_forestry_tora_ha'])->toBe(21000.5)
        ->and($data['area_km2'])->toBe(8556.75)
        ->and($data['city'])->toBe('Siak')
        ->and($data['province'])->toBe('Riau')
        ->and($data['is_founding_member'])->toBeTrue()
        ->and($data['joined_year'])->toBe(2017);
});

it('returns null landscape figures when they are unset', function () {
    makeKabupaten([
        'forest_cover_ha' => null,
        'protected_area_ha' => null,
        'social_forestry_tora_ha' => null,
        'area_km2' => null,
        'city' => null,
        'province' => null,
        'is_founding_member' => false,
        'joined_year' => null,
    ]);

    $data = $this->getJson('/api/kabupaten/siak-regency')->assertOk()->json('data');

    expect($data['forest_cover_ha'])->toBeNull()
        ->and($data['protected_area_ha'])->toBeNull()
        ->and($data['social_forestry_tora_ha'])->toBeNull()
        ->and($data['area_km2'])->toBeNull()
        ->and($data['province'])->toBeNull()
        ->and($data['is_founding_member'])->toBeFalse()
        ->and($data['joined_year'])->toBeNull();
});

it('returns the role in both languages', function () {
    makeKabupaten();

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['role'])->toBe('Founding member')
        ->and($data['role_id'])->toBe('Anggota pendiri');
});

it('returns a null role when it is unset', function () {
    makeKabupaten(['role' => null, 'role_id' => null]);

    $data = $this->getJson('/api/kabupaten/siak-regency')->assertOk()->json('data');

    expect($data['role'])->toBeNull()
        ->and($data['role_id'])->toBeNull();
});

it('returns attached pillars as slim references', function () {
    $kabupaten = makeKabupaten();

    $pillar = App\Models\Pillar::create([
        'is_active' => true,
        'title' => 'Shared governance',
        'title_id' => 'Tata kelola bersama',
        'slug' => 'shared-governance',
        'slug_id' => 'tata-kelola-bersama',
        'technical_term' => 'Multi-stakeholder governance',
        'technical_term_id' => 'Tata kelola multipihak',
        'description' => 'A long description that has no place in a reference.',
    ]);
    $kabupaten->pillars()->attach($pillar);

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['pillars'])->toHaveCount(1)
        ->and($data['pillars'][0])->toBe([
            'id' => $pillar->id,
            'slug' => 'shared-governance',
            'slug_id' => 'tata-kelola-bersama',
            'title' => 'Shared governance',
            'title_id' => 'Tata kelola bersama',
            'technical_term' => 'Multi-stakeholder governance',
            'technical_term_id' => 'Tata kelola multipihak',
        ]);
});

it('returns an empty pillar list when none are attached', function () {
    makeKabupaten();

    expect($this->getJson('/api/kabupatens')->assertOk()->json('data.0.pillars'))->toBe([]);
});

it('exposes coordinates as floats on the full payload', function () {
    makeKabupaten();

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['latitude'])->toBe(0.8118)
        ->and($data['longitude'])->toBe(101.8);
});

it('returns a slim map payload for pinned kabupatens', function () {
    makeKabupaten();

    $data = $this->getJson('/api/kabupatens/map')->assertOk()->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0])->toBe([
            'id' => $data[0]['id'],
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'role' => 'Founding member',
            'role_id' => 'Anggota pendiri',
            'city' => 'Siak',
            'province' => 'Riau',
            'is_founding_member' => true,
            'latitude' => 0.8118,
            'longitude' => 101.8,
        ]);

    // The heavy per-page content stays out of the map response.
    expect($data[0])->not->toHaveKeys(['commodities', 'achievements', 'content']);
});

it('skips kabupatens without coordinates on the map endpoint', function () {
    makeKabupaten(['latitude' => null, 'longitude' => null]);
    makeKabupaten([
        'title' => 'Sintang Regency',
        'title_id' => 'Kabupaten Sintang',
        'slug' => 'sintang-regency',
        'slug_id' => 'kabupaten-sintang',
        'latitude' => 0.0667,
        'longitude' => 111.5,
    ]);

    $data = $this->getJson('/api/kabupatens/map')->assertOk()->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['slug'])->toBe('sintang-regency');
});

it('hides inactive kabupatens from the map endpoint', function () {
    makeKabupaten(['is_active' => false]);

    $this->getJson('/api/kabupatens/map')->assertOk()->assertJsonCount(0, 'data');
});

it('returns empty arrays when a kabupaten has no rows', function () {
    makeKabupaten([
        'commodities' => null,
        'commodities_id' => null,
        'achievements' => null,
        'achievements_id' => null,
    ]);

    $data = $this->getJson('/api/kabupaten/siak-regency')->assertOk()->json('data');

    expect($data['commodities'])->toBe([])
        ->and($data['commodities_id'])->toBe([])
        ->and($data['achievements'])->toBe([])
        ->and($data['achievements_id'])->toBe([]);
});

it('serves each impact type on its own shape', function () {
    makeKabupaten([
        'achievements' => [
            [
                'type' => 'data',
                'value' => '12k ha',
                'title' => 'High conservation value areas designated',
                'description' => 'Mapped together with villages.',
                'source' => 'Source: Regent Decree 2024',
            ],
            [
                'type' => 'quote',
                'quote' => 'We mapped the boundaries together.',
                'name' => 'Head of Siak District',
                'image' => 'kabupatens/regent.jpg',
            ],
            [
                'type' => 'text',
                'title' => 'How the district works',
                'description' => 'Villages, mills and the district office share one plan.',
            ],
            [
                'type' => 'image_text',
                'title' => 'The nursery at kilometre nine',
                'description' => 'Seedlings raised by the village cooperative.',
                'image' => 'kabupatens/nursery.jpg',
            ],
        ],
    ]);

    $rows = $this->getJson('/api/kabupaten/siak-regency')->assertOk()->json('data.achievements');

    expect($rows[0])->toBe([
        'type' => 'data',
        'value' => '12k ha',
        'title' => 'High conservation value areas designated',
        'description' => 'Mapped together with villages.',
        'source' => 'Source: Regent Decree 2024',
    ]);

    expect($rows[1])->toBe([
        'type' => 'quote',
        'quote' => 'We mapped the boundaries together.',
        'name' => 'Head of Siak District',
        'image' => Storage::disk('public')->url('kabupatens/regent.jpg'),
    ]);

    expect($rows[2])->toBe([
        'type' => 'text',
        'title' => 'How the district works',
        'description' => 'Villages, mills and the district office share one plan.',
    ]);

    expect($rows[3])->toBe([
        'type' => 'image_text',
        'title' => 'The nursery at kilometre nine',
        'description' => 'Seedlings raised by the village cooperative.',
        'image' => Storage::disk('public')->url('kabupatens/nursery.jpg'),
    ]);
});

it('leaves an image text row without a picture as null', function () {
    makeKabupaten([
        'achievements' => [
            ['type' => 'image_text', 'title' => 'The nursery at kilometre nine'],
        ],
    ]);

    expect($this->getJson('/api/kabupaten/siak-regency')->assertOk()
        ->json('data.achievements.0.image'))->toBeNull();
});

it('reads a row saved before the impact types existed as a data row', function () {
    // No type key at all, exactly how the rows are stored today.
    $rows = $this->getJson('/api/kabupaten/' . makeKabupaten()->slug)->assertOk()
        ->json('data.achievements');

    expect($rows[0])->toBe([
        'type' => 'data',
        'value' => '12k ha',
        'title' => 'High conservation value areas designated',
        'description' => 'Mapped together with villages.',
        'source' => 'Source: Regent Decree 2024',
    ]);
});

it('leaves a quote without an image as null', function () {
    makeKabupaten([
        'achievements' => [
            ['type' => 'quote', 'quote' => 'Village by village.', 'name' => 'Head of Siak District'],
        ],
    ]);

    expect($this->getJson('/api/kabupaten/siak-regency')->assertOk()
        ->json('data.achievements.0.image'))->toBeNull();
});

it('serves the banner and the commodity icon as full urls', function () {
    makeKabupaten([
        'banner' => 'kabupatens/banner-siak.jpg',
        'commodities' => [
            ['name' => 'Peat pineapple', 'icon' => 'kabupatens/pineapple.svg', 'description' => null],
            // Both stay optional, so a row without an icon serialises as null.
            ['name' => 'Forest honey', 'description' => null],
        ],
    ]);

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['banner'])->toEndWith('/storage/kabupatens/banner-siak.jpg')
        ->and($data['commodities'][0]['icon'])->toEndWith('/storage/kabupatens/pineapple.svg')
        ->and($data['commodities'][1]['icon'])->toBeNull();
});

it('leaves the banner null when none was uploaded', function () {
    makeKabupaten();

    $this->getJson('/api/kabupaten/kabupaten-siak')
        ->assertOk()
        ->assertJsonPath('data.banner', null);
});

function makeStoryPost(array $overrides = []): Post
{
    return Post::create(array_merge([
        'is_active' => true,
        'type' => 'article',
        'title' => 'Peat restored in Siak',
        'title_id' => 'Gambut pulih di Siak',
        'slug' => 'peat-restored-in-siak',
        'slug_id' => 'gambut-pulih-di-siak',
        'lead' => 'Village by village.',
        'lead_id' => 'Desa demi desa.',
        'image' => 'posts/peat.jpg',
        'published_at' => now(),
    ], $overrides));
}

it('serves the story block per language, in the picked order', function () {
    $first = makeStoryPost();
    $second = makeStoryPost([
        'title' => 'Honey from a standing forest',
        'title_id' => 'Madu dari hutan yang berdiri',
        'slug' => 'honey-from-a-standing-forest',
        'slug_id' => 'madu-dari-hutan-yang-berdiri',
        'image' => null,
    ]);

    makeKabupaten([
        'story_label' => 'Stories',
        'story_title' => 'Stories from the ground',
        'story_description' => 'Told by the people running it.',
        'story_image' => 'kabupatens/story-siak.jpg',
        'story_posts' => [$second->id, $first->id],
        'story_label_id' => 'Cerita Gerakan',
        'story_title_id' => 'Cerita dari lapangan',
        'story_description_id' => 'Diceritakan oleh para pelakunya.',
        'story_posts_id' => [$first->id],
    ]);

    $data = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data');

    expect($data['story']['label'])->toBe('Stories')
        ->and($data['story']['title'])->toBe('Stories from the ground')
        ->and($data['story']['description'])->toBe('Told by the people running it.')
        ->and($data['story']['image'])->toEndWith('/storage/kabupatens/story-siak.jpg')
        // The order set in the CMS, not the order the posts were created in.
        ->and(array_column($data['story']['posts'], 'slug'))
        ->toBe(['honey-from-a-standing-forest', 'peat-restored-in-siak'])
        ->and($data['story']['posts'][1]['image'])->toEndWith('/storage/posts/peat.jpg')
        ->and($data['story']['posts'][0]['image'])->toBeNull();

    // Each language keeps its own selection and its own copy.
    expect($data['story_id']['label'])->toBe('Cerita Gerakan')
        ->and($data['story_id']['image'])->toBeNull()
        ->and(array_column($data['story_id']['posts'], 'slug_id'))->toBe(['gambut-pulih-di-siak']);

    // The flat columns stay out of the payload.
    expect($data)->not->toHaveKey('story_posts')
        ->and($data)->not->toHaveKey('story_label');
});

it('leaves an unpublished story post out of the block', function () {
    $published = makeStoryPost();
    $draft = makeStoryPost([
        'is_active' => false,
        'slug' => 'draft-story',
        'slug_id' => 'cerita-draf',
    ]);

    makeKabupaten(['story_posts' => [$draft->id, $published->id]]);

    $story = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data.story');

    expect(array_column($story['posts'], 'slug'))->toBe(['peat-restored-in-siak']);
});

it('returns an empty story block when nothing was filled in', function () {
    makeKabupaten();

    $story = $this->getJson('/api/kabupaten/kabupaten-siak')->assertOk()->json('data.story');

    expect($story)->toBe([
        'label' => null,
        'title' => null,
        'description' => null,
        'image' => null,
        'posts' => [],
    ]);
});
