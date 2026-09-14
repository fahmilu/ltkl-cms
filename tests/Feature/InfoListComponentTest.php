<?php

use App\Enums\InfoListLayout;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithInfoList(array $data = []): Page
{
    $block = [
        'type' => 'info_list',
        'data' => array_merge([
            'label' => 'Pertanyaan umum',
            'title' => 'FAQ',
            'description' => '<h2>Sering ditanyakan</h2><p>Jawaban singkat.</p>',
            'layout' => InfoListLayout::ACCORDION->value,
            'items' => [
                ['title' => 'Apa itu LTKL?', 'description' => 'Kolektif sembilan kabupaten.'],
                ['title' => 'Bagaimana bergabung?', 'description' => 'Hubungi sekretariat.'],
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

it('registers the info list block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('info_list');
});

it('offers exactly two layouts', function () {
    $labels = array_map(
        fn(InfoListLayout $case): string => $case->getLabel(),
        InfoListLayout::cases()
    );

    expect($labels)->toBe(['Accordion', 'Card']);
});

it('returns the block fields through the api', function () {
    makePageWithInfoList();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Pertanyaan umum')
        ->and($data['title'])->toBe('FAQ')
        ->and($data['description'])->toBe('<h3>Sering ditanyakan</h3><p>Jawaban singkat.</p>')
        ->and($data['layout'])->toBe('accordion')
        ->and($data['items'])->toBe([
            ['title' => 'Apa itu LTKL?', 'description' => 'Kolektif sembilan kabupaten.'],
            ['title' => 'Bagaimana bergabung?', 'description' => 'Hubungi sekretariat.'],
        ]);
});

it('defaults to accordion when the layout is missing or unknown', function () {
    makePageWithInfoList(['layout' => null]);

    expect($this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data.layout'))
        ->toBe('accordion');
});

it('publishes the card layout when chosen', function () {
    makePageWithInfoList(['layout' => InfoListLayout::CARD->value]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['layout'])->toBe('card')
        ->and($data['components_id'][0]['data']['layout'])->toBe('card');
});

it('keeps no image or label on an item', function () {
    makePageWithInfoList([
        'items' => [
            ['title' => 'Apa itu LTKL?', 'description' => 'Kolektif sembilan kabupaten.', 'image' => 'pages/x.jpg', 'label' => 'Umum'],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item)->toBe([
        'title' => 'Apa itu LTKL?',
        'description' => 'Kolektif sembilan kabupaten.',
    ]);
});

it('survives a block with no items at all', function () {
    makePageWithInfoList(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('anchors the block when it is flagged as a submenu entry', function () {
    makePageWithInfoList(['add_as_submenu' => true]);

    $block = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0');

    expect($block['data']['anchor'])->toBe('faq');
});
