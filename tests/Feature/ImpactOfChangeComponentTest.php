<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithImpactOfChange(array $data = []): Page
{
    $block = [
        'type' => 'impact_of_change',
        'data' => array_merge([
            'label' => 'Impact',
            'title' => 'Impact of change',
            'description' => '<h2>What changed</h2><p>On the ground.</p>',
            'items' => [
                [
                    'title' => 'Forest cover kept',
                    'description' => '<h2>Standing forest</h2><p>Worth more than cleared land.</p>',
                    'image' => 'pages/forest.jpg',
                    'link' => 'https://example.test/forest',
                ],
                // No image or link picked.
                ['title' => 'Fair supply chains', 'description' => '<p>Traceable to the village.</p>'],
            ],
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

it('registers the impact of change block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('impact_of_change');
});

it('returns the block and its items through the api', function () {
    makePageWithImpactOfChange();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Impact')
        ->and($data['title'])->toBe('Impact of change')
        ->and($data['description'])->toContain('<h3>What changed</h3>')
        ->and($data['items'][0])->toBe([
            'title' => 'Forest cover kept',
            'description' => '<h3>Standing forest</h3><p>Worth more than cleared land.</p>',
            'image' => Storage::disk('public')->url('pages/forest.jpg'),
            'link' => 'https://example.test/forest',
        ])
        ->and($data['items'][1]['image'])->toBeNull()
        ->and($data['items'][1]['link'])->toBeNull();
});

it('serves the block in both languages', function () {
    makePageWithImpactOfChange();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][1]['title'])->toBe('Fair supply chains')
        ->and($data['components_id'][0]['data']['items'][1]['title'])->toBe('Fair supply chains');
});

it('survives a block with no items at all', function () {
    makePageWithImpactOfChange(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});
