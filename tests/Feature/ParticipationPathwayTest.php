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
use Filament\Forms\Components\FileUpload;
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

it('saves title and description in both languages', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateParticipationPathway::class)
        ->fillForm([
            'title_id' => 'Menjadi anggota',
            'title' => 'Become a member',
            'slug_id' => 'menjadi-anggota',
            'slug' => 'become-a-member',
            'description_id' => 'Untuk kabupaten yang siap berkomitmen.',
            'description' => 'For districts ready to commit.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $pathway = ParticipationPathway::firstWhere('slug', 'become-a-member');

    expect($pathway->title_id)->toBe('Menjadi anggota')
        ->and($pathway->description)->toBe('For districts ready to commit.')
        ->and($pathway->description_id)->toBe('Untuk kabupaten yang siap berkomitmen.');
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


it('no longer offers an image or a page builder', function () {
    $this->actingAs(User::factory()->create());

    $fields = Livewire::test(CreateParticipationPathway::class)
        ->instance()->getSchema('form')->getFlatComponents();

    foreach ($fields as $field) {
        expect($field)->not->toBeInstanceOf(BuilderField::class)
            ->and($field)->not->toBeInstanceOf(FileUpload::class);
    }
});

it('keeps image and components out of the payload', function () {
    makePathway();

    $data = $this->getJson('/api/participation-pathway/become-a-member')->assertOk()->json('data');

    expect($data)->not->toHaveKeys(['image', 'components', 'components_id'])
        ->and($data['description'])->toBe('For districts ready to commit.');
});
