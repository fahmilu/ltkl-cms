<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Models\Page;
use App\Models\User;
use Filament\Forms\Components\Builder as BuilderField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makePageWithAnnualReports(array $data = []): Page
{
    $block = [
        'type' => 'annual_reports',
        'data' => array_merge([
            'label' => 'Reports',
            'title' => 'Annual reports',
            'description' => '<h2>Our year</h2><p>In numbers.</p>',
            'cover_image' => 'pages/cover-2025.jpg',
            'file' => 'pages/annual-report-2025.pdf',
            'items' => [
                ['title' => 'Annual report 2024', 'file' => 'pages/annual-report-2024.pdf'],
                // No file uploaded yet.
                ['title' => 'Annual report 2023'],
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

function pageBlockNames(): array
{
    $names = [];
    foreach (Livewire::test(CreatePage::class)->instance()->getSchema('form')->getFlatComponents() as $field) {
        if ($field instanceof BuilderField) {
            foreach ($field->getBlocks() as $block) {
                $names[] = $block->getName();
            }
        }
    }

    return $names;
}

it('registers the annual reports block and drops proof of concept', function () {
    $this->actingAs(User::factory()->create());

    expect(pageBlockNames())->toContain('annual_reports')
        ->not->toContain('proof_of_concept');
});

it('returns the block with full file urls through the api', function () {
    makePageWithAnnualReports();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['label'])->toBe('Reports')
        ->and($data['title'])->toBe('Annual reports')
        ->and($data['description'])->toContain('<h3>Our year</h3>')
        ->and($data['cover_image'])->toBe(Storage::disk('public')->url('pages/cover-2025.jpg'))
        ->and($data['file'])->toBe(Storage::disk('public')->url('pages/annual-report-2025.pdf'))
        ->and($data['items'])->toBe([
            ['title' => 'Annual report 2024', 'file' => Storage::disk('public')->url('pages/annual-report-2024.pdf')],
            ['title' => 'Annual report 2023', 'file' => null],
        ]);
});

it('reads missing uploads as null', function () {
    makePageWithAnnualReports(['cover_image' => null, 'file' => '', 'items' => null]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data.components.0.data');

    expect($data['cover_image'])->toBeNull()
        ->and($data['file'])->toBeNull()
        ->and($data['items'])->toBe([]);
});

it('serves the block in both languages', function () {
    makePageWithAnnualReports();

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['items'][0]['title'])->toBe('Annual report 2024')
        ->and($data['components_id'][0]['data']['items'][0]['title'])->toBe('Annual report 2024');
});
