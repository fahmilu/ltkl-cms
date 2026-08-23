<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePageWithBlocks(array $blocks): Page
{
    return Page::create([
        'is_active' => true,
        'title' => 'Homepage',
        'title_id' => 'Beranda',
        'slug' => 'homepage',
        'slug_id' => 'beranda',
        'components' => $blocks,
        'components_id' => $blocks,
    ]);
}

it('carries the label through banner, text image and latest news', function () {
    makePageWithBlocks([
        ['type' => 'banner', 'data' => ['banner_label' => 'Welcome', 'banner_title' => 'LTKL']],
        ['type' => 'text_image', 'data' => ['label' => 'Our work', 'title' => 'On the ground']],
        ['type' => 'latest_news', 'data' => ['label' => 'Newsroom', 'title' => 'Latest stories']],
    ]);

    $components = $this->getJson('/api/page/homepage')->assertOk()->json('data.components');

    expect($components[0]['data']['banner_label'])->toBe('Welcome')
        ->and($components[1]['data']['label'])->toBe('Our work')
        ->and($components[2]['data']['label'])->toBe('Newsroom');
});

it('shifts headings in the latest news description', function () {
    makePageWithBlocks([
        [
            'type' => 'latest_news',
            'data' => [
                'label' => 'Newsroom',
                'title' => 'Latest stories',
                'description' => '<h2>Read on</h2><p>Fresh from the districts.</p>',
                'button_text' => 'See all',
            ],
        ],
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    foreach (['components', 'components_id'] as $column) {
        expect($data[$column][0]['data']['description'])
            ->toContain('<h3>Read on</h3>')
            ->not->toContain('<h2>');
    }
});

it('shifts headings in the banner statistic description and keeps the stat items', function () {
    makePageWithBlocks([
        [
            'type' => 'banner_statistic',
            'data' => [
                'label' => 'Why it matters',
                'title' => 'The landscape',
                'description' => '<h2>Where we work</h2><p>Nine districts.</p>',
                'items' => [
                    ['title' => 'Forest cover', 'value' => '1.2', 'unit' => 'M ha'],
                ],
                'button_1_text' => 'Read more',
                'button_1_url' => 'https://example.test',
            ],
        ],
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    foreach (['components', 'components_id'] as $column) {
        expect($data[$column][0]['data']['description'])
            ->toContain('<h3>Where we work</h3>')
            ->not->toContain('<h2>');
        expect($data[$column][0]['data']['items'][0])
            ->toMatchArray(['title' => 'Forest cover', 'value' => '1.2', 'unit' => 'M ha']);
        expect($data[$column][0]['data']['button_1_text'])->toBe('Read more');
    }
});

it('survives a latest news block saved before description existed', function () {
    // Blocks stored before the field was added carry no description key at all.
    makePageWithBlocks([
        ['type' => 'latest_news', 'data' => ['title' => 'Latest stories', 'button_text' => 'See all']],
    ]);

    $this->getJson('/api/page/homepage')
        ->assertOk()
        ->assertJsonPath('data.components.0.data.description', null)
        ->assertJsonPath('data.components.0.data.title', 'Latest stories');
});

it('publishes the text image block styling and image position', function () {
    makePageWithBlocks([
        [
            'type' => 'text_image',
            'data' => [
                'title' => 'On the ground',
                'is_block' => true,
                'background_color' => '#A20056',
                'image_position' => 'top',
            ],
        ],
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    foreach (['components', 'components_id'] as $column) {
        expect($data[$column][0]['data']['is_block'])->toBeTrue()
            ->and($data[$column][0]['data']['background_color'])->toBe('#A20056')
            ->and($data[$column][0]['data']['image_position'])->toBe('top');
    }
});

it('reads a text image block saved before the styling existed as a plain right-hand image', function () {
    makePageWithBlocks([
        ['type' => 'text_image', 'data' => ['title' => 'On the ground']],
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    foreach (['components', 'components_id'] as $column) {
        expect($data[$column][0]['data']['is_block'])->toBeFalse()
            ->and($data[$column][0]['data']['background_color'])->toBeNull()
            ->and($data[$column][0]['data']['image_position'])->toBe('right');
    }
});

it('drops a background colour that is no longer in the palette', function () {
    makePageWithBlocks([
        [
            'type' => 'text_image',
            'data' => ['title' => 'On the ground', 'is_block' => true, 'background_color' => '#123456'],
        ],
    ]);

    $data = $this->getJson('/api/page/homepage')->assertOk()->json('data');

    expect($data['components'][0]['data']['background_color'])->toBeNull();
});
