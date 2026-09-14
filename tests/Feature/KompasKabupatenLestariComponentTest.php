<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithKompasKabupatenLestari(array $data = []): Page
{
    $block = [
        'type' => 'kompas_kabupaten_lestari',
        'data' => array_merge([
            'label' => 'Arah gerak',
            'title' => 'Kompas Kabupaten Lestari',
            'description' => '<h2>Menuju kabupaten lestari</h2><p>Empat arah utama.</p>',
            'items' => [
                ['label' => 'Ekologi', 'title' => 'Hutan Lestari', 'description' => 'Kawasan hutan terjaga.'],
                ['label' => 'Ekonomi', 'title' => 'Ekonomi Hijau', 'description' => 'Rantai pasok berkelanjutan.'],
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

it('registers the kompas kabupaten lestari block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('kompas_kabupaten_lestari');
});

it('returns the block fields through the api, each item carrying a label', function () {
    makePageWithKompasKabupatenLestari();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Arah gerak')
        ->and($data['title'])->toBe('Kompas Kabupaten Lestari')
        ->and($data['items'])->toHaveCount(2)
        ->and($data['items'][0])->toBe([
            'label' => 'Ekologi',
            'title' => 'Hutan Lestari',
            'description' => 'Kawasan hutan terjaga.',
            'image' => null,
        ]);
});

it('fills in a missing label rather than dropping the key', function () {
    makePageWithKompasKabupatenLestari(['items' => [['title' => 'Hutan Lestari']]]);

    expect($this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0'))
        ->toBe(['label' => null, 'title' => 'Hutan Lestari', 'description' => null, 'image' => null]);
});

it('carries an image on every item, like the vision', function () {
    makePageWithKompasKabupatenLestari([
        'items' => [
            ['label' => 'Ekologi', 'title' => 'Hutan Lestari', 'description' => 'Kawasan hutan terjaga.', 'image' => 'pages/forest.jpg'],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item)->toBe([
        'label' => 'Ekologi',
        'title' => 'Hutan Lestari',
        'description' => 'Kawasan hutan terjaga.',
        'image' => Storage::disk('public')->url('pages/forest.jpg'),
    ]);
});

it('shifts headings in the block description', function () {
    makePageWithKompasKabupatenLestari();

    $description = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.description');

    expect($description)->toContain('<h3>Menuju kabupaten lestari</h3>')
        ->not->toContain('<h2>');
});

it('survives a block with no items at all', function () {
    makePageWithKompasKabupatenLestari(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('serves the block in both languages', function () {
    makePageWithKompasKabupatenLestari();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][1]['label'])->toBe('Ekonomi')
        ->and($data['components_id'][0]['data']['items'][1]['label'])->toBe('Ekonomi');
});
