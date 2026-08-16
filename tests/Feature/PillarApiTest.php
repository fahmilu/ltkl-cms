<?php

use App\Models\Kabupaten;
use App\Models\Pillar;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePillar(array $overrides = []): Pillar
{
    return Pillar::create(array_merge([
        'is_active' => true,
        'title' => 'Shared governance',
        'title_id' => 'Tata kelola bersama',
        'slug' => 'shared-governance',
        'slug_id' => 'tata-kelola-bersama',
        'technical_term' => 'Multi-stakeholder governance',
        'technical_term_id' => 'Tata kelola multipihak',
        'description' => 'One table, one set of data.',
        'description_id' => 'Satu meja, satu data yang sama.',
        'statistics' => [
            ['value' => '6', 'label' => 'Policies'],
            ['value' => '38', 'label' => 'Institutions in the forum'],
        ],
        'statistics_id' => [
            ['value' => '6', 'label' => 'Kebijakan'],
        ],
        'results' => [
            [
                'value' => '12k ha',
                'title' => 'Area protected through forum decisions',
                'description' => 'Agreed with communities.',
                'source' => 'Source: Regent Decree 2024',
            ],
        ],
        'results_id' => [
            [
                'value' => '12rb ha',
                'title' => 'Kawasan terlindungi lewat keputusan forum',
                'description' => 'Disepakati bersama masyarakat.',
                'source' => 'Sumber: SK Bupati 2024',
            ],
        ],
        'sorted_at' => 1,
    ], $overrides));
}

it('lists active pillars ordered by sorted_at', function () {
    makePillar(['sorted_at' => 2]);
    makePillar([
        'title' => 'Community economy',
        'title_id' => 'Ekonomi lestari warga',
        'slug' => 'community-economy',
        'slug_id' => 'ekonomi-lestari-warga',
        'sorted_at' => 1,
    ]);

    $data = $this->getJson('/api/pillars')->assertOk()->json('data');

    expect(array_column($data, 'slug'))->toBe(['community-economy', 'shared-governance']);
});

it('numbers pillars by their position, not by a stored field', function () {
    makePillar(['sorted_at' => 5]);
    makePillar([
        'title' => 'Community economy',
        'title_id' => 'Ekonomi lestari warga',
        'slug' => 'community-economy',
        'slug_id' => 'ekonomi-lestari-warga',
        'sorted_at' => 40,
    ]);

    $data = $this->getJson('/api/pillars')->assertOk()->json('data');

    // sorted_at values are 5 and 40, but the numbering is 1 and 2.
    expect(array_column($data, 'number'))->toBe([1, 2]);
});

it('numbers a single pillar by its place in the whole list', function () {
    makePillar(['sorted_at' => 1]);
    makePillar([
        'title' => 'Community economy',
        'title_id' => 'Ekonomi lestari warga',
        'slug' => 'community-economy',
        'slug_id' => 'ekonomi-lestari-warga',
        'sorted_at' => 2,
    ]);

    expect($this->getJson('/api/pillar/community-economy')->assertOk()->json('data.number'))
        ->toBe(2);
});

it('hides inactive pillars', function () {
    makePillar(['is_active' => false]);

    $this->getJson('/api/pillars')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/pillar/shared-governance')->assertNotFound();
});

it('resolves a pillar by either slug', function () {
    makePillar();

    $this->getJson('/api/pillar/shared-governance')->assertOk()
        ->assertJsonPath('data.slug', 'shared-governance');
    $this->getJson('/api/pillar/tata-kelola-bersama')->assertOk()
        ->assertJsonPath('data.slug', 'shared-governance');
});

it('returns statistics and results per language with their own lengths', function () {
    makePillar();

    $data = $this->getJson('/api/pillar/tata-kelola-bersama')->assertOk()->json('data');

    expect($data['statistics'])->toHaveCount(2)
        ->and($data['statistics_id'])->toHaveCount(1)
        ->and($data['statistics_id'][0])->toBe(['value' => '6', 'label' => 'Kebijakan'])
        ->and($data['results'][0]['value'])->toBe('12k ha')
        ->and($data['results_id'][0]['source'])->toBe('Sumber: SK Bupati 2024');
});

it('counts kabupatens live instead of storing the figure', function () {
    makePillar();

    expect($this->getJson('/api/pillar/shared-governance')->json('data.kabupatens_count'))->toBe(0);

    Kabupaten::create([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
    ]);
    Kabupaten::create([
        'is_active' => false,
        'title' => 'Draft Regency',
        'title_id' => 'Kabupaten Draf',
        'slug' => 'draft-regency',
        'slug_id' => 'kabupaten-draf',
    ]);

    // Only active kabupatens count towards the header figure.
    expect($this->getJson('/api/pillar/shared-governance')->json('data.kabupatens_count'))->toBe(1);
});

it('returns in-practice examples with their kabupaten', function () {
    $pillar = makePillar();

    $kabupaten = Kabupaten::create([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
    ]);

    $pillar->practices()->create([
        'kabupaten_id' => $kabupaten->id,
        'since_year' => 2019,
        'title' => 'A district forum that meets every quarter',
        'title_id' => 'Forum kabupaten yang bertemu tiap kuartal',
        'description' => 'Eight agencies sit together.',
        'description_id' => 'Delapan lembaga daerah duduk bersama.',
        'sorted_at' => 1,
    ]);

    $practices = $this->getJson('/api/pillar/shared-governance')->assertOk()->json('data.practices');

    expect($practices)->toHaveCount(1)
        ->and($practices[0]['since_year'])->toBe(2019)
        ->and($practices[0]['title_id'])->toBe('Forum kabupaten yang bertemu tiap kuartal')
        ->and($practices[0]['kabupaten']['slug'])->toBe('siak-regency');
});

it('returns empty arrays when a pillar has no rows', function () {
    makePillar([
        'statistics' => null,
        'statistics_id' => null,
        'results' => null,
        'results_id' => null,
    ]);

    $data = $this->getJson('/api/pillar/shared-governance')->assertOk()->json('data');

    expect($data['statistics'])->toBe([])
        ->and($data['statistics_id'])->toBe([])
        ->and($data['results'])->toBe([])
        ->and($data['results_id'])->toBe([])
        ->and($data['practices'])->toBe([]);
});
