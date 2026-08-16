<?php

use App\Enums\CollectionComponentSource;
use App\Enums\CollectionType;
use App\Filament\Resources\ParticipationPathways\Pages\CreateParticipationPathway;
use App\Filament\Resources\ParticipationPathways\Pages\EditParticipationPathway;
use App\Filament\Resources\ParticipationPathways\ParticipationPathwayResource;
use App\Models\ParticipationPathway;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePathway(array $overrides = []): ParticipationPathway
{
    return ParticipationPathway::create(array_merge([
        'is_active' => true,
        'title' => 'Become a member',
        'title_id' => 'Menjadi anggota',
        'slug' => 'become-a-member',
        'slug_id' => 'menjadi-anggota',
        'description' => 'For districts ready to commit.',
        'description_id' => 'Untuk kabupaten yang siap berkomitmen.',
        'components' => [
            ['type' => 'lead_text', 'data' => ['lead' => '<h2>Start here</h2><p>Three steps.</p>']],
        ],
        'components_id' => [
            ['type' => 'lead_text', 'data' => ['lead' => '<h2>Mulai di sini</h2><p>Tiga langkah.</p>']],
        ],
        'sorted_at' => 1,
    ], $overrides));
}

it('is no longer a collection type', function () {
    $values = array_column(CollectionType::cases(), 'value');

    expect($values)->not->toContain('participation_pathway');
});

it('lives under Masters as its own resource', function () {
    expect(ParticipationPathwayResource::getNavigationGroup())->toBe('Masters')
        ->and(ParticipationPathwayResource::getNavigationLabel())->toBe('Participation Pathways');

    $resources = Filament::getPanel('administrator')->getResources();

    expect($resources)->toContain(ParticipationPathwayResource::class);
});

it('points the collection page block at the new endpoint', function () {
    expect(CollectionComponentSource::PARTICIPATION_PATHWAYS->getEndpoint())
        ->toBe('/api/participation-pathways');
});

it('saves title, description and components in both languages', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateParticipationPathway::class)
        ->fillForm([
            'title_id' => 'Menjadi anggota',
            'title' => 'Become a member',
            'slug_id' => 'menjadi-anggota',
            'slug' => 'become-a-member',
            'description_id' => 'Untuk kabupaten yang siap berkomitmen.',
            'description' => 'For districts ready to commit.',
            'components_id' => [
                ['type' => 'lead_text', 'data' => ['lead' => '<p>Tiga langkah.</p>']],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $pathway = ParticipationPathway::firstWhere('slug', 'become-a-member');

    expect($pathway->title_id)->toBe('Menjadi anggota')
        ->and($pathway->description_id)->toBe('Untuk kabupaten yang siap berkomitmen.')
        ->and($pathway->components_id)->toHaveCount(1)
        ->and(array_values($pathway->components_id)[0]['type'])->toBe('lead_text');
});

it('returns stats rows on a stable shape', function () {
    makePathway([
        'components' => [
            [
                'type' => 'stats',
                'data' => [
                    'items' => [
                        ['title' => 'Districts', 'value' => '9', 'unit' => null],
                        // Saved before the unit field existed.
                        ['title' => 'Forest cover', 'value' => '312k'],
                    ],
                    'button_text' => 'See the data',
                    'button_url' => 'https://example.test/data',
                ],
            ],
        ],
    ]);

    $block = $this->getJson('/api/participation-pathway/become-a-member')->assertOk()
        ->json('data.components.0.data');

    expect($block['items'])->toHaveCount(2)
        ->and($block['items'][0])->toBe(['title' => 'Districts', 'value' => '9', 'unit' => null])
        ->and($block['items'][1])->toBe(['title' => 'Forest cover', 'value' => '312k', 'unit' => null])
        ->and($block['button_text'])->toBe('See the data');
});

it('returns an empty stats list rather than null', function () {
    makePathway([
        'components' => [['type' => 'stats', 'data' => ['button_text' => 'See the data']]],
    ]);

    expect($this->getJson('/api/participation-pathway/become-a-member')->assertOk()
        ->json('data.components.0.data.items'))->toBe([]);
});

it('offers the lead text, text image and stats blocks', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreateParticipationPathway::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[$block->getName()] = true;
            }
        }
    }

    expect(array_keys($names))->toEqualCanonicalizing(['lead_text', 'text_image', 'stats']);
});

it('edits an existing pathway', function () {
    $this->actingAs(User::factory()->create());
    $pathway = makePathway();

    Livewire::test(EditParticipationPathway::class, ['record' => $pathway->getRouteKey()])
        ->fillForm(['title_id' => 'Menjadi mitra'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($pathway->refresh()->title_id)->toBe('Menjadi mitra');
});

it('lists active pathways ordered by sorted_at', function () {
    makePathway(['sorted_at' => 2]);
    makePathway([
        'title' => 'Become a donor',
        'title_id' => 'Menjadi donatur',
        'slug' => 'become-a-donor',
        'slug_id' => 'menjadi-donatur',
        'sorted_at' => 1,
    ]);

    $data = $this->getJson('/api/participation-pathways')->assertOk()->json('data');

    expect(array_column($data, 'slug'))->toBe(['become-a-donor', 'become-a-member']);
});

it('hides inactive pathways', function () {
    makePathway(['is_active' => false]);

    $this->getJson('/api/participation-pathways')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/participation-pathway/become-a-member')->assertNotFound();
});

it('resolves a pathway by either slug', function () {
    makePathway();

    $this->getJson('/api/participation-pathway/become-a-member')->assertOk()
        ->assertJsonPath('data.slug', 'become-a-member');
    $this->getJson('/api/participation-pathway/menjadi-anggota')->assertOk()
        ->assertJsonPath('data.slug', 'become-a-member');
});

it('shifts headings in the lead text block of both languages', function () {
    makePathway();

    $data = $this->getJson('/api/participation-pathway/become-a-member')->assertOk()->json('data');

    expect($data['components'][0]['data']['lead'])->toContain('<h3>Start here</h3>')
        ->and($data['components_id'][0]['data']['lead'])->toContain('<h3>Mulai di sini</h3>')
        ->and($data['components'][0]['data']['lead'])->not->toContain('<h2>');
});

it('resolves the text image block upload to a full url', function () {
    makePathway([
        'components' => [
            [
                'type' => 'text_image',
                'data' => [
                    'image' => 'participation-pathways/step.png',
                    'title' => 'Sign the letter',
                    'description' => '<p>Then meet the forum.</p>',
                ],
            ],
        ],
    ]);

    $block = $this->getJson('/api/participation-pathway/become-a-member')->assertOk()
        ->json('data.components.0.data');

    expect($block['image'])->toStartWith('http')
        ->and($block['image'])->toContain('participation-pathways/step.png');
});

it('returns empty component lists when a pathway has none', function () {
    makePathway(['components' => null, 'components_id' => null]);

    $data = $this->getJson('/api/participation-pathway/become-a-member')->assertOk()->json('data');

    expect($data['components'])->toBe([])
        ->and($data['components_id'])->toBe([]);
});
