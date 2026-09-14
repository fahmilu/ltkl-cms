<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePost(array $overrides = []): Post
{
    return Post::create(array_merge([
        'is_active' => true,
        'type' => 'article',
        'title' => 'Peat restored in Siak',
        'title_id' => 'Gambut pulih di Siak',
        'slug' => 'peat-restored-in-siak',
        'slug_id' => 'gambut-pulih-di-siak',
        'lead' => 'Village by village.',
        'lead_id' => 'Desa demi desa.',
        'image' => 'posts/peat.jpg',
        'published_at' => now(),
    ], $overrides));
}

function makePageWithProofOfConcept(array $data = []): Page
{
    $block = [
        'type' => 'proof_of_concept',
        'data' => array_merge([
            'label' => 'Bukti nyata',
            'title' => 'Proof of Concept',
            'description' => '<h2>Sudah terbukti</h2><p>Bukan sekadar wacana.</p>',
            'items' => [
                ['title' => 'Pemulihan gambut', 'description' => 'Desa demi desa.'],
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

it('registers the proof of concept block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('proof_of_concept');
});

it('returns the block fields through the api', function () {
    makePageWithProofOfConcept();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Bukti nyata')
        ->and($data['title'])->toBe('Proof of Concept')
        ->and($data['description'])->toBe('<h3>Sudah terbukti</h3><p>Bukan sekadar wacana.</p>')
        ->and($data['items'])->toHaveCount(1)
        ->and($data['items'][0])->toBe([
            'title' => 'Pemulihan gambut',
            'description' => 'Desa demi desa.',
            'image' => null,
            'post' => null,
        ]);
});

it('links an item to the picked post', function () {
    $post = makePost();

    makePageWithProofOfConcept([
        'items' => [
            ['title' => 'Pemulihan gambut', 'description' => 'Desa demi desa.', 'post_id' => $post->id],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item['post'])->toBe([
        'id' => $post->id,
        'title' => 'Peat restored in Siak',
        'title_id' => 'Gambut pulih di Siak',
        'slug' => 'peat-restored-in-siak',
        'slug_id' => 'gambut-pulih-di-siak',
        'lead' => 'Village by village.',
        'lead_id' => 'Desa demi desa.',
        'image' => Storage::disk('public')->url('posts/peat.jpg'),
        'published_at' => $post->published_at->toJSON(),
    ]);
});

it('drops the link when the picked post is unpublished', function () {
    $post = makePost(['is_active' => false]);

    makePageWithProofOfConcept([
        'items' => [
            ['title' => 'Pemulihan gambut', 'post_id' => $post->id],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item['post'])->toBeNull();
});

it('carries an image on an item', function () {
    makePageWithProofOfConcept([
        'items' => [
            ['title' => 'Pemulihan gambut', 'image' => 'pages/peat.jpg'],
        ],
    ]);

    $item = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0');

    expect($item['image'])->toBe(Storage::disk('public')->url('pages/peat.jpg'));
});

it('survives a block with no items at all', function () {
    makePageWithProofOfConcept(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('serves the block in both languages', function () {
    makePageWithProofOfConcept();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][0]['title'])->toBe('Pemulihan gambut')
        ->and($data['components_id'][0]['data']['items'][0]['title'])->toBe('Pemulihan gambut');
});
