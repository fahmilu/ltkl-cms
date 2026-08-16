<?php

use App\Enums\CollectionComponentSource;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

it('offers exactly the three collection sources', function () {
    $labels = array_map(
        fn(CollectionComponentSource $case): string => $case->getLabel(),
        CollectionComponentSource::cases()
    );

    expect($labels)->toBe(['Kabupaten Map', 'Pillars', 'Participation Pathways']);
});

it('points each source at a real endpoint', function () {
    expect(CollectionComponentSource::PILLARS->getEndpoint())->toBe('/api/pillars')
        ->and(CollectionComponentSource::KABUPATEN_MAP->getEndpoint())->toBe('/api/kabupatens/map')
        // Pathways are their own resource now, not a collection type.
        ->and(CollectionComponentSource::PARTICIPATION_PATHWAYS->getEndpoint())
        ->toBe('/api/participation-pathways');
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
