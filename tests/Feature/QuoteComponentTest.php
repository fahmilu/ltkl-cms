<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithQuote(array $data = []): Page
{
    $block = [
        'type' => 'quote',
        'data' => array_merge([
            'quote' => 'We mapped the boundaries together, village by village.',
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

it('registers the quote block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('quote');
});

it('serves the quote in both languages', function () {
    makePageWithQuote();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['type'])->toBe('quote')
        ->and($data['components'][0]['data']['quote'])
        ->toBe('We mapped the boundaries together, village by village.')
        ->and($data['components_id'][0]['data']['quote'])
        ->toBe('We mapped the boundaries together, village by village.');
});

it('publishes the quote as a submenu anchor', function () {
    Page::query()->forceDelete();

    Page::create([
        'is_active' => true,
        'menu_is_active' => true,
        'menu_group' => ['main'],
        'title' => 'About',
        'title_id' => 'Tentang',
        'slug' => 'about',
        'slug_id' => 'tentang',
        'components' => [
            [
                'type' => 'quote',
                'data' => [
                    'quote' => 'Village by village.',
                    'add_as_submenu' => true,
                    'submenu_title' => 'In their words',
                ],
            ],
        ],
        'components_id' => [],
    ]);

    expect($this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0.subs.0'))
        ->toMatchArray(['is_anchor' => true, 'anchor' => 'in-their-words', 'title' => 'In their words']);
});
