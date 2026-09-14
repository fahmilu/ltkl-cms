<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithEmbedIframe(array $data = []): Page
{
    $block = [
        'type' => 'embed_iframe',
        'data' => array_merge([
            'label' => 'Peta interaktif',
            'title' => 'Sebaran Kabupaten Lestari',
            'description' => '<h2>Jelajahi peta</h2><p>Klik kabupaten untuk detail.</p>',
            'iframe_url' => 'https://maps.example.test/embed/ltkl',
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

it('registers the embed iframe block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('embed_iframe');
});

it('returns the block fields through the api', function () {
    makePageWithEmbedIframe();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Peta interaktif')
        ->and($data['title'])->toBe('Sebaran Kabupaten Lestari')
        // Headings step down one level, as everywhere else in the builder.
        ->and($data['description'])->toBe('<h3>Jelajahi peta</h3><p>Klik kabupaten untuk detail.</p>')
        ->and($data['iframe_url'])->toBe('https://maps.example.test/embed/ltkl');
});

it('serves the block in both languages', function () {
    makePageWithEmbedIframe();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['iframe_url'])->toBe('https://maps.example.test/embed/ltkl')
        ->and($data['components_id'][0]['data']['iframe_url'])->toBe('https://maps.example.test/embed/ltkl');
});

it('anchors the block when it is flagged as a submenu entry', function () {
    makePageWithEmbedIframe(['add_as_submenu' => true]);

    $block = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0');

    expect($block['data']['anchor'])->toBe('sebaran-kabupaten-lestari');
});

it('requires the iframe url when saving from the page builder', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreatePage::class)
        ->fillForm([
            'title' => 'Homepage',
            'title_id' => 'Beranda',
            'slug' => 'homepage',
            'slug_id' => 'beranda',
            'components' => [
                'block1' => [
                    'type' => 'embed_iframe',
                    'data' => [
                        'label' => 'Peta interaktif',
                        'title' => 'Sebaran Kabupaten Lestari',
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['components.block1.data.iframe_url' => 'required']);
});
