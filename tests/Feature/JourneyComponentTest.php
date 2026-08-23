<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithJourney(array $data = []): Page
{
    $block = [
        'type' => 'journey',
        'data' => array_merge([
            'label' => 'Perjalanan',
            'title' => 'Gerakan yang Terus Bertumbuh',
            'description' => '<h2>Perjalanan dimulai</h2><p>Berpacu lebih cepat.</p>',
            'items' => [
                ['title' => 'Mencari cara', 'period' => '2018–2020', 'description' => 'Beberapa kabupaten mulai berkumpul.', 'is_past' => true],
                ['title' => 'Menguji cara', 'period' => '2021–2022', 'description' => 'Berbagai model pembangunan mulai diuji.', 'is_past' => true],
                ['title' => 'Pembuktian cara', 'period' => '2023–sekarang', 'description' => 'Gerakan berdampak direplikasi.', 'is_past' => false],
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

it('registers the journey block on the page form', function () {
    $this->actingAs(User::factory()->create());

    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    expect($names)->toContain('journey');
});

it('numbers the steps from their order', function () {
    makePageWithJourney();

    $steps = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items');

    expect(array_column($steps, 'number'))->toBe(['01', '02', '03']);
    expect($steps[0])->toBe([
        'number' => '01',
        'title' => 'Mencari cara',
        'period' => '2018–2020',
        'description' => 'Beberapa kabupaten mulai berkumpul.',
        'is_past' => true,
    ]);

    expect(array_column($steps, 'is_past'))->toBe([true, true, false]);
});

it('keeps two digits past the ninth step', function () {
    makePageWithJourney([
        'items' => array_map(
            fn(int $n): array => ['title' => 'Step ' . $n],
            range(1, 11),
        ),
    ]);

    $numbers = array_column(
        $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data.items'),
        'number',
    );

    expect($numbers[8])->toBe('09')
        ->and($numbers[9])->toBe('10')
        ->and($numbers[10])->toBe('11');
});

it('fills in the keys a step was saved without', function () {
    makePageWithJourney(['items' => [['title' => 'Mencari cara']]]);

    expect($this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items.0'))
        ->toBe([
            'number' => '01',
            'title' => 'Mencari cara',
            'period' => null,
            'description' => null,
            'is_past' => false,
        ]);
});

it('shifts headings in the block description', function () {
    makePageWithJourney();

    expect($this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.description'))
        ->toContain('<h3>Perjalanan dimulai</h3>')
        ->not->toContain('<h2>');
});

it('survives a journey with no steps at all', function () {
    makePageWithJourney(['items' => null]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.items', []);
});

it('serves the journey in both languages', function () {
    makePageWithJourney();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][2]['number'])->toBe('03')
        ->and($data['components_id'][0]['data']['items'][2]['title'])->toBe('Pembuktian cara');
});

it('serves is past as a boolean whatever the toggle stored', function () {
    makePageWithJourney([
        'items' => [
            // Livewire can hand back the checkbox state as 1/0 rather than true/false.
            ['title' => 'Mencari cara', 'is_past' => 1],
            ['title' => 'Menguji cara', 'is_past' => 0],
            ['title' => 'Cara berkelanjutan'],
        ],
    ]);

    $steps = $this->getJson('/api/page/homepage')->assertOk()
        ->json('data.components.0.data.items');

    expect(array_column($steps, 'is_past'))->toBe([true, false, false]);
});
