<?php

use App\Filament\Resources\Kabupatens\Pages\CreateKabupaten;
use App\Filament\Resources\Kabupatens\Pages\EditKabupaten;
use App\Models\Kabupaten;
use App\Models\Pillar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the create form with a repeater per language', function () {
    Livewire::test(CreateKabupaten::class)
        ->assertSchemaStateSet([
            'commodities' => [],
            'commodities_id' => [],
            'achievements' => [],
            'achievements_id' => [],
        ]);
});

it('saves a separate list of commodities and achievements per language', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'commodities' => [
                ['name' => 'Peat pineapple', 'description' => 'Grown without clearing new land.'],
                ['name' => 'Forest honey', 'description' => 'Value depends on the forest staying up.'],
            ],
            'commodities_id' => [
                ['name' => 'Nanas gambut', 'description' => 'Ditanam tanpa membuka lahan baru.'],
            ],
            'achievements' => [
                [
                    'type' => 'data',
                    'value' => '12k ha',
                    'title' => 'High conservation value areas designated',
                    'description' => 'Mapped together with villages.',
                    'source' => 'Source: Regent Decree 2024',
                ],
            ],
            'achievements_id' => [
                [
                    'type' => 'data',
                    'value' => '12rb ha',
                    'title' => 'Kawasan bernilai konservasi tinggi ditetapkan',
                    'description' => 'Dipetakan bersama masyarakat desa.',
                    'source' => 'Sumber: SK Bupati 2024',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'siak-regency');

    // The two languages hold independent lists, including different row counts.
    expect($kabupaten->commodities)->toHaveCount(2)
        ->and($kabupaten->commodities_id)->toHaveCount(1)
        ->and(array_values($kabupaten->commodities)[0]['name'])->toBe('Peat pineapple')
        ->and(array_values($kabupaten->commodities_id)[0]['name'])->toBe('Nanas gambut')
        ->and(array_values($kabupaten->achievements)[0]['value'])->toBe('12k ha')
        ->and(array_values($kabupaten->achievements_id)[0]['value'])->toBe('12rb ha')
        ->and(array_values($kabupaten->achievements_id)[0]['source'])->toBe('Sumber: SK Bupati 2024');
});

it('saves the role separately for each language', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'role' => 'Founding member',
            'role_id' => 'Anggota pendiri',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'siak-regency');

    expect($kabupaten->role)->toBe('Founding member')
        ->and($kabupaten->role_id)->toBe('Anggota pendiri');
});

it('accepts a kabupaten with no role at all', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Sintang Regency',
            'title_id' => 'Kabupaten Sintang',
            'slug' => 'sintang-regency',
            'slug_id' => 'kabupaten-sintang',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Kabupaten::firstWhere('slug', 'sintang-regency')->role)->toBeNull();
});

it('attaches pillars to a kabupaten', function () {
    $governance = Pillar::create([
        'is_active' => true,
        'title' => 'Shared governance',
        'title_id' => 'Tata kelola bersama',
        'slug' => 'shared-governance',
        'slug_id' => 'tata-kelola-bersama',
        'sorted_at' => 1,
    ]);
    $economy = Pillar::create([
        'is_active' => true,
        'title' => 'Community economy',
        'title_id' => 'Ekonomi lestari warga',
        'slug' => 'community-economy',
        'slug_id' => 'ekonomi-lestari-warga',
        'sorted_at' => 2,
    ]);

    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'pillars' => [$economy->id, $governance->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $pillars = Kabupaten::firstWhere('slug', 'siak-regency')->pillars;

    // The relation orders by the pillars' own sorted_at, not by selection order.
    expect($pillars)->toHaveCount(2)
        ->and($pillars->pluck('slug')->all())->toBe(['shared-governance', 'community-economy']);
});

it('detaches pillars without deleting them', function () {
    $pillar = Pillar::create([
        'is_active' => true,
        'title' => 'Shared governance',
        'title_id' => 'Tata kelola bersama',
        'slug' => 'shared-governance',
        'slug_id' => 'tata-kelola-bersama',
    ]);

    $kabupaten = Kabupaten::create([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
    ]);
    $kabupaten->pillars()->attach($pillar);

    Livewire::test(EditKabupaten::class, ['record' => $kabupaten->getRouteKey()])
        ->fillForm(['pillars' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($kabupaten->refresh()->pillars)->toHaveCount(0)
        ->and(Pillar::find($pillar->id))->not->toBeNull();
});

it('saves the landscape and membership fields', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'forest_cover_ha' => 312000,
            'peatland_ha' => 57000,
            'area_km2' => 8556.75,
            'city' => 'Siak',
            'province' => 'Riau',
            'is_founding_member' => true,
            'joined_year' => 2017,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'siak-regency');

    expect((float) $kabupaten->forest_cover_ha)->toBe(312000.0)
        ->and((float) $kabupaten->area_km2)->toBe(8556.75)
        ->and($kabupaten->city)->toBe('Siak')
        ->and($kabupaten->province)->toBe('Riau')
        ->and($kabupaten->is_founding_member)->toBeTrue()
        ->and($kabupaten->joined_year)->toBe(2017);
});

it('saves coordinates picked on the map', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'latitude' => 0.8118,
            'longitude' => 101.8,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'siak-regency');

    expect((float) $kabupaten->latitude)->toBe(0.8118)
        ->and((float) $kabupaten->longitude)->toBe(101.8);
});

it('rejects coordinates outside Indonesia', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Nowhere',
            'title_id' => 'Entah',
            'slug' => 'nowhere',
            'slug_id' => 'entah',
            'latitude' => 51.5,
            'longitude' => -0.12,
        ])
        ->call('create')
        ->assertHasFormErrors(['latitude', 'longitude']);
});

it('requires both coordinates or neither', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Half Pinned',
            'title_id' => 'Setengah',
            'slug' => 'half-pinned',
            'slug_id' => 'setengah',
            'latitude' => 0.8118,
            'longitude' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['longitude']);
});

it('rejects a join year outside the allowed range', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Sigi Regency',
            'title_id' => 'Kabupaten Sigi',
            'slug' => 'sigi-regency',
            'slug_id' => 'kabupaten-sigi',
            'joined_year' => 1800,
        ])
        ->call('create')
        ->assertHasFormErrors(['joined_year']);
});

it('allows a language to have no rows at all', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Sintang Regency',
            'title_id' => 'Kabupaten Sintang',
            'slug' => 'sintang-regency',
            'slug_id' => 'kabupaten-sintang',
            'commodities' => [
                ['name' => 'Rubber', 'description' => null],
            ],
            'commodities_id' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $kabupaten = Kabupaten::firstWhere('slug', 'sintang-regency');

    expect($kabupaten->commodities)->toHaveCount(1)
        ->and($kabupaten->commodities_id)->toBe([]);
});

it('requires a name on every commodity row', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Sigi Regency',
            'title_id' => 'Kabupaten Sigi',
            'slug' => 'sigi-regency',
            'slug_id' => 'kabupaten-sigi',
            'commodities_id' => [
                ['name' => null, 'description' => 'Tanpa nama'],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['commodities_id.0.name']);
});

it('edits and deletes rows in one language without touching the other', function () {
    $kabupaten = Kabupaten::create([
        'is_active' => true,
        'title' => 'Kapuas Hulu Regency',
        'title_id' => 'Kabupaten Kapuas Hulu',
        'slug' => 'kapuas-hulu-regency',
        'slug_id' => 'kabupaten-kapuas-hulu',
        'commodities' => [
            ['name' => 'Cocoa', 'description' => null],
            ['name' => 'Coffee', 'description' => null],
        ],
        'commodities_id' => [
            ['name' => 'Kakao', 'description' => null],
            ['name' => 'Kopi', 'description' => null],
        ],
    ]);

    Livewire::test(EditKabupaten::class, ['record' => $kabupaten->getRouteKey()])
        ->fillForm([
            'commodities' => [
                ['name' => 'Cocoa beans', 'description' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $kabupaten->refresh();

    expect(array_values($kabupaten->commodities))->toHaveCount(1)
        ->and(array_values($kabupaten->commodities)[0]['name'])->toBe('Cocoa beans')
        ->and(array_values($kabupaten->commodities_id))->toHaveCount(2)
        ->and(array_values($kabupaten->commodities_id)[1]['name'])->toBe('Kopi');
});

it('saves a quote impact with its own fields', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'achievements' => [
                [
                    'type' => 'quote',
                    'quote' => 'We mapped the boundaries together.',
                    'name' => 'Head of Siak District',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $row = array_values(Kabupaten::firstWhere('slug', 'siak-regency')->achievements)[0];

    expect($row['type'])->toBe('quote')
        ->and($row['quote'])->toBe('We mapped the boundaries together.')
        ->and($row['name'])->toBe('Head of Siak District');
});

it('saves a text impact with a title and description', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'achievements' => [
                [
                    'type' => 'text',
                    'title' => 'How the district works',
                    'description' => 'Villages, mills and the district office share one plan.',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $row = array_values(Kabupaten::firstWhere('slug', 'siak-regency')->achievements)[0];

    expect($row['type'])->toBe('text')
        ->and($row['title'])->toBe('How the district works')
        ->and($row['description'])->toBe('Villages, mills and the district office share one plan.');
});

it('needs a value only on a data impact', function () {
    Livewire::test(CreateKabupaten::class)
        ->fillForm([
            'title' => 'Siak Regency',
            'title_id' => 'Kabupaten Siak',
            'slug' => 'siak-regency',
            'slug_id' => 'kabupaten-siak',
            'achievements' => [
                ['type' => 'data', 'title' => 'No number given'],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['achievements.0.value']);
});
