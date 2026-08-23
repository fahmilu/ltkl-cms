<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithTheValues(array $data = [], string $type = 'the_values'): Page
{
    $block = [
        'type' => $type,
        'data' => array_merge([
            'label' => 'What we stand for',
            'title' => 'The values',
            'description' => '<h2>Where you fit</h2><p>Pick a path.</p>',
            'items' => [
                ['title' => 'Transparency', 'description' => 'Every number is traceable.'],
                ['title' => 'Collaboration', 'description' => 'Districts lead, we support.'],
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

it('registers the values, problems and vision blocks on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('the_values')
        ->and($names)->toContain('the_problems')
        ->and($names)->toContain('the_vision')
        ->and($names)->not->toContain('your_role');
});

it('returns the block fields through the api', function () {
    makePageWithTheValues();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('What we stand for')
        ->and($data['title'])->toBe('The values')
        ->and($data['items'])->toHaveCount(2)
        ->and($data['items'][0])->toBe([
            'title' => 'Transparency',
            'description' => 'Every number is traceable.',
        ]);
});

it('keeps only title and description on an item', function () {
    // A row left over from the old block still serialises on the new shape.
    makePageWithTheValues([
        'items' => [
            [
                'label' => 'For districts',
                'title' => 'Transparency',
                'description' => 'Every number is traceable.',
                'icon' => 'pages/district.svg',
                'link' => 'https://example.test/join',
                'link_label' => 'Start here',
            ],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item)->toBe([
        'title' => 'Transparency',
        'description' => 'Every number is traceable.',
    ]);
});

it('fills in a missing description rather than dropping the key', function () {
    makePageWithTheValues(['items' => [['title' => 'Transparency']]]);

    expect($this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0'))
        ->toBe(['title' => 'Transparency', 'description' => null]);
});

it('shifts headings in the block description', function () {
    makePageWithTheValues();

    $description = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.description');

    expect($description)->toContain('<h3>Where you fit</h3>')
        ->not->toContain('<h2>');
});

it('survives a block with no items at all', function () {
    makePageWithTheValues(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('serves the block in both languages', function () {
    makePageWithTheValues();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][1]['title'])->toBe('Collaboration')
        ->and($data['components_id'][0]['data']['items'][1]['title'])->toBe('Collaboration');
});

it('serves the problems block on the same shape as the values', function () {
    makePageWithTheValues(type: 'the_problems');

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0');

    expect($data['type'])->toBe('the_problems')
        ->and($data['data']['items'][0])->toBe([
            'title' => 'Transparency',
            'description' => 'Every number is traceable.',
        ]);
});

it('carries an image on every vision item', function () {
    makePageWithTheValues([
        'items' => [
            ['title' => 'Standing forest', 'description' => 'Worth more than cleared land.', 'image' => 'pages/forest.jpg'],
            // No image picked yet.
            ['title' => 'Fair supply chains', 'description' => 'Traceable to the village.'],
        ],
    ], type: 'the_vision');

    $items = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items');

    expect($items[0])->toBe([
        'title' => 'Standing forest',
        'description' => 'Worth more than cleared land.',
        'image' => Storage::disk('public')->url('pages/forest.jpg'),
    ]);

    expect($items[1]['image'])->toBeNull();
});

it('keeps an image out of the values and problems items', function () {
    foreach (['the_values', 'the_problems'] as $type) {
        Page::query()->forceDelete();
        makePageWithTheValues([
            'items' => [['title' => 'Transparency', 'image' => 'pages/forest.jpg']],
        ], type: $type);

        expect($this->getJson('/api/page/homepage')->assertOk()
            ->json('data.components.0.data.items.0'))
            ->toBe(['title' => 'Transparency', 'description' => null]);
    }
});
