<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithYourRole(array $data = []): Page
{
    $block = [
        'type' => 'your_role',
        'data' => array_merge([
            'label' => 'Get involved',
            'title' => 'Your role',
            'description' => '<h2>Where you fit</h2><p>Pick a path.</p>',
            'show_icon' => true,
            'items' => [
                [
                    'label' => 'For districts',
                    'title' => 'Become a member',
                    'description' => 'Join the nine.',
                    'icon' => 'pages/district.svg',
                    'link' => 'https://example.test/join',
                    'link_label' => 'Start here',
                ],
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

it('registers the your role block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('your_role');
});

it('returns every item field through the api', function () {
    makePageWithYourRole();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Get involved')
        ->and($data['title'])->toBe('Your role')
        ->and($data['show_icon'])->toBeTrue()
        ->and($data['items'])->toHaveCount(1)
        ->and($data['items'][0]['label'])->toBe('For districts')
        ->and($data['items'][0]['title'])->toBe('Become a member')
        ->and($data['items'][0]['description'])->toBe('Join the nine.')
        ->and($data['items'][0]['link'])->toBe('https://example.test/join')
        ->and($data['items'][0]['link_label'])->toBe('Start here');
});

it('resolves the item icon to a full url', function () {
    makePageWithYourRole();

    $icon = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0.icon');

    expect($icon)->toStartWith('http')
        ->and($icon)->toContain('pages/district.svg');
});

it('returns a null icon rather than a broken url when none is set', function () {
    makePageWithYourRole([
        'items' => [['title' => 'No icon here', 'icon' => null]],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    // Missing keys are filled in, so the frontend always sees the same shape.
    expect($item['icon'])->toBeNull()
        ->and($item['title'])->toBe('No icon here')
        ->and($item)->toHaveKeys(['label', 'title', 'description', 'icon', 'link', 'link_label']);
});

it('shifts headings in the block description', function () {
    makePageWithYourRole();

    $description = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.description');

    expect($description)->toContain('<h3>Where you fit</h3>')
        ->not->toContain('<h2>');
});

it('survives a block with no items at all', function () {
    makePageWithYourRole(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('serves the block in both languages', function () {
    makePageWithYourRole(['show_icon' => false]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['show_icon'])->toBeFalse()
        ->and($data['components_id'][0]['data']['items'][0]['link_label'])->toBe('Start here');
});
