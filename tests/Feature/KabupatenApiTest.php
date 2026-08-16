<?php

use App\Models\Kabupaten;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        'peatland_ha' => 57000,
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
        ->and($data['peatland_ha'])->toBe(57000)
        ->and($data['area_km2'])->toBe(8556.75)
        ->and($data['city'])->toBe('Siak')
        ->and($data['province'])->toBe('Riau')
        ->and($data['is_founding_member'])->toBeTrue()
        ->and($data['joined_year'])->toBe(2017);
});

it('returns null landscape figures when they are unset', function () {
    makeKabupaten([
        'forest_cover_ha' => null,
        'peatland_ha' => null,
        'area_km2' => null,
        'city' => null,
        'province' => null,
        'is_founding_member' => false,
        'joined_year' => null,
    ]);

    $data = $this->getJson('/api/kabupaten/siak-regency')->assertOk()->json('data');

    expect($data['forest_cover_ha'])->toBeNull()
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
