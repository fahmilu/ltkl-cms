<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithStatisticWithTitle(array $data = []): Page
{
    $block = [
        'type' => 'statistic_with_title',
        'data' => array_merge([
            'label' => 'Dalam angka',
            'title' => 'Capaian Kabupaten Lestari',
            'description' => '<h2>Terus bertumbuh</h2><p>Diperbarui setiap tahun.</p>',
            'items' => [
                ['title' => 'Luas kawasan', 'value' => '1.2', 'unit' => 'juta ha'],
                ['title' => 'Kabupaten anggota', 'value' => '12', 'unit' => 'kabupaten'],
            ],
            'button_text' => 'Lihat data lengkap',
            'button_url' => 'https://kabupatenlestari.org/data',
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

it('registers the statistic with title block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('statistic_with_title')
        ->and($names)->toContain('statistic');
});

it('returns the label, title and description from the root, not the items', function () {
    makePageWithStatisticWithTitle();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Dalam angka')
        ->and($data['title'])->toBe('Capaian Kabupaten Lestari')
        // Headings step down one level, as everywhere else in the builder.
        ->and($data['description'])->toBe('<h3>Terus bertumbuh</h3><p>Diperbarui setiap tahun.</p>')
        ->and($data['items'])->toBe([
            ['title' => 'Luas kawasan', 'value' => '1.2', 'unit' => 'juta ha'],
            ['title' => 'Kabupaten anggota', 'value' => '12', 'unit' => 'kabupaten'],
        ])
        ->and($data['button_text'])->toBe('Lihat data lengkap')
        ->and($data['button_url'])->toBe('https://kabupatenlestari.org/data');
});

it('serves the block in both languages', function () {
    makePageWithStatisticWithTitle();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['title'])->toBe('Capaian Kabupaten Lestari')
        ->and($data['components_id'][0]['data']['title'])->toBe('Capaian Kabupaten Lestari');
});

it('anchors the block when it is flagged as a submenu entry', function () {
    makePageWithStatisticWithTitle(['add_as_submenu' => true]);

    $block = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0');

    expect($block['data']['anchor'])->toBe('capaian-kabupaten-lestari');
});
