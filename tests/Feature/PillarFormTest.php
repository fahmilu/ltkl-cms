<?php

use App\Filament\Resources\Pillars\Pages\CreatePillar;
use App\Filament\Resources\Pillars\Pages\EditPillar;
use App\Models\Kabupaten;
use App\Models\Pillar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the create form with a list per language', function () {
    Livewire::test(CreatePillar::class)
        ->assertSchemaStateSet([
            'statistics' => [],
            'statistics_id' => [],
            'results' => [],
            'results_id' => [],
        ]);
});

it('saves statistics and results separately per language', function () {
    Livewire::test(CreatePillar::class)
        ->fillForm([
            'title' => 'Shared governance',
            'title_id' => 'Tata kelola bersama',
            'slug' => 'shared-governance',
            'slug_id' => 'tata-kelola-bersama',
            'technical_term_id' => 'Tata kelola multipihak',
            'description_id' => 'Satu meja, satu data yang sama.',
            'statistics' => [
                ['value' => '6', 'label' => 'Policies'],
                ['value' => '38', 'label' => 'Institutions in the forum'],
            ],
            'statistics_id' => [
                ['value' => '6', 'label' => 'Kebijakan'],
            ],
            'results_id' => [
                [
                    'value' => '12rb ha',
                    'title' => 'Kawasan terlindungi lewat keputusan forum',
                    'description' => 'Disepakati bersama masyarakat.',
                    'source' => 'Sumber: SK Bupati 2024',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $pillar = Pillar::firstWhere('slug', 'shared-governance');

    expect($pillar->statistics)->toHaveCount(2)
        ->and($pillar->statistics_id)->toHaveCount(1)
        ->and(array_values($pillar->statistics_id)[0]['label'])->toBe('Kebijakan')
        ->and(array_values($pillar->results_id)[0]['value'])->toBe('12rb ha')
        ->and($pillar->technical_term_id)->toBe('Tata kelola multipihak');
});

it('requires a value and label on every statistic row', function () {
    Livewire::test(CreatePillar::class)
        ->fillForm([
            'title' => 'Shared governance',
            'title_id' => 'Tata kelola bersama',
            'slug' => 'shared-governance',
            'slug_id' => 'tata-kelola-bersama',
            'statistics_id' => [
                ['value' => null, 'label' => null],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['statistics_id.0.value', 'statistics_id.0.label']);
});

it('saves in-practice examples against a kabupaten', function () {
    $kabupaten = Kabupaten::create([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
    ]);

    Livewire::test(CreatePillar::class)
        ->fillForm([
            'title' => 'Shared governance',
            'title_id' => 'Tata kelola bersama',
            'slug' => 'shared-governance',
            'slug_id' => 'tata-kelola-bersama',
            'practices' => [
                [
                    'kabupaten_id' => $kabupaten->id,
                    'since_year' => 2019,
                    'title' => 'A district forum that meets every quarter',
                    'title_id' => 'Forum kabupaten yang bertemu tiap kuartal',
                    'description' => null,
                    'description_id' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $practice = Pillar::firstWhere('slug', 'shared-governance')->practices()->first();

    // One row carries both languages, so they can never point at different kabupatens.
    expect($practice->kabupaten_id)->toBe($kabupaten->id)
        ->and($practice->since_year)->toBe(2019)
        ->and($practice->title_id)->toBe('Forum kabupaten yang bertemu tiap kuartal')
        ->and($practice->title)->toBe('A district forum that meets every quarter');
});

it('deletes an example row without touching the other language lists', function () {
    $pillar = Pillar::create([
        'is_active' => true,
        'title' => 'Shared governance',
        'title_id' => 'Tata kelola bersama',
        'slug' => 'shared-governance',
        'slug_id' => 'tata-kelola-bersama',
        'statistics_id' => [['value' => '6', 'label' => 'Kebijakan']],
    ]);

    $pillar->practices()->createMany([
        ['title' => 'First', 'title_id' => 'Pertama', 'sorted_at' => 1],
        ['title' => 'Second', 'title_id' => 'Kedua', 'sorted_at' => 2],
    ]);

    Livewire::test(EditPillar::class, ['record' => $pillar->getRouteKey()])
        ->fillForm([
            'practices' => [
                ['kabupaten_id' => null, 'since_year' => null, 'title' => 'Only', 'title_id' => 'Satu', 'description' => null, 'description_id' => null],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $pillar->refresh();

    expect($pillar->practices)->toHaveCount(1)
        ->and($pillar->practices->first()->title_id)->toBe('Satu')
        ->and($pillar->statistics_id)->toHaveCount(1);
});
