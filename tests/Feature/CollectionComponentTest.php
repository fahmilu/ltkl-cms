<?php

use App\Enums\CollectionComponentSource;
use App\Enums\CollectionDisplay;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithCollectionBlock(array $data = []): Page
{
    $block = [
        'type' => 'collection',
        'data' => array_merge([
            'label' => 'Our members',
            'title' => 'Nine districts on one map',
            'description' => '<h2>Where they are</h2><p>Spread across five islands.</p>',
            'source' => CollectionComponentSource::KABUPATEN_MAP->value,
        ], $data),
    ];

    return Page::create([
        'is_active' => true,
        'title' => 'Homepage',
        'title_id' => 'Beranda',
        'slug' => 'homepage',
        'slug_id' => 'beranda',
        'components' => [$block],
        'components_id' => [$block],
    ]);
}

it('offers exactly the four collection sources', function () {
    $labels = array_map(
        fn(CollectionComponentSource $case): string => $case->getLabel(),
        CollectionComponentSource::cases()
    );

    expect($labels)->toBe(['Kabupaten Map', 'Pillars', 'Participation Pathways', 'Job Opportunities']);
});

it('points each source at a real endpoint', function () {
    expect(CollectionComponentSource::PILLARS->getEndpoint())->toBe('/api/pillars')
        ->and(CollectionComponentSource::KABUPATEN_MAP->getEndpoint())->toBe('/api/kabupatens/map')
        // Pathways are their own resource now, not a collection type.
        ->and(CollectionComponentSource::PARTICIPATION_PATHWAYS->getEndpoint())
        ->toBe('/api/participation-pathways')
        ->and(CollectionComponentSource::JOB_OPPORTUNITIES->getEndpoint())
        ->toBe('/api/job-opportunities');
});

it('returns the collection block through the page api', function () {
    makePageWithCollectionBlock();

    $components = $this->getJson('/api/page/homepage')->assertOk()->json('data.components');

    expect($components)->toHaveCount(1)
        ->and($components[0]['type'])->toBe('collection')
        ->and($components[0]['data']['label'])->toBe('Our members')
        ->and($components[0]['data']['title'])->toBe('Nine districts on one map')
        ->and($components[0]['data']['source'])->toBe('kabupaten_map');
});

it('shifts headings in the description like other blocks', function () {
    makePageWithCollectionBlock();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    // h2 becomes h3 so the block never competes with the page title.
    expect($data['description'])->toContain('<h3>Where they are</h3>')
        ->and($data['description'])->not->toContain('<h2>');
});

it('survives a block saved without a description', function () {
    makePageWithCollectionBlock(['description' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.description', null);
});

it('serves the block in both languages', function () {
    makePageWithCollectionBlock(['source' => CollectionComponentSource::PILLARS->value]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['source'])->toBe('pillars')
        ->and($data['components_id'][0]['data']['source'])->toBe('pillars');
});

it('offers three displays for the pathways source', function () {
    $labels = array_map(
        fn(CollectionDisplay $case): string => $case->getLabel(),
        CollectionDisplay::cases()
    );

    expect($labels)->toBe(['Side Accordion', 'Card', 'Full Accordion']);
});

it('publishes the chosen display on a pathways block', function () {
    makePageWithCollectionBlock([
        'source' => CollectionComponentSource::PARTICIPATION_PATHWAYS->value,
        'display' => CollectionDisplay::CARD->value,
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    foreach (['components', 'components_id'] as $column) {
        expect($data[$column][0]['data']['display'])->toBe('card');
    }
});

it('reads a pathways block saved before the display existed as a side accordion', function () {
    makePageWithCollectionBlock([
        'source' => CollectionComponentSource::PARTICIPATION_PATHWAYS->value,
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['display'])->toBe('side_accordion');
});

it('leaves the display off every other source', function () {
    makePageWithCollectionBlock([
        'source' => CollectionComponentSource::JOB_OPPORTUNITIES->value,
        // A display left over from a source swap is not published.
        'display' => CollectionDisplay::CARD->value,
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['display'])->toBeNull();
});

it('offers the display select on the pathways source only', function () {
    $this->actingAs(User::factory()->create());

    $fillSource = fn(string $source) => Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Careers',
            'title_id' => 'Karir',
            'slug' => 'careers',
            'slug_id' => 'karir',
            'components' => [
                'block1' => ['type' => 'collection', 'data' => ['source' => $source]],
            ],
        ])
        ->html();

    // The select hands its state back as an enum instance, so the visibility is
    // read through the enum rather than compared to the raw string.
    expect($fillSource(CollectionComponentSource::PARTICIPATION_PATHWAYS->value))
        ->toContain('How the pathways are laid out.')
        ->and($fillSource(CollectionComponentSource::PILLARS->value))
        ->not->toContain('How the pathways are laid out.');
});

it('saves the display as a plain string on the block', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Careers',
            'title_id' => 'Karir',
            'slug' => 'careers',
            'slug_id' => 'karir',
            'components' => [
                'block1' => [
                    'type' => 'collection',
                    'data' => [
                        'source' => CollectionComponentSource::PARTICIPATION_PATHWAYS->value,
                        'display' => CollectionDisplay::FULL_ACCORDION->value,
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $block = Page::firstOrFail()->components[0];

    expect($block['data']['source'])->toBe('participation_pathways')
        ->and($block['data']['display'])->toBe('full_accordion');
});
