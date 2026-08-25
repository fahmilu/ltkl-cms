<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMenuPage(array $attributes = []): Page
{
    return Page::create(array_merge([
        'is_active' => true,
        'menu_is_active' => true,
        'menu_group' => ['main'],
        'title' => 'About',
        'title_id' => 'Tentang',
        'slug' => 'about',
        'slug_id' => 'tentang',
    ], $attributes));
}

it('publishes flagged components as anchor submenu entries per language', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
            ['type' => 'text_image', 'data' => ['title' => 'On the ground', 'add_as_submenu' => false]],
        ],
        'components_id' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Misi Kami', 'add_as_submenu' => true]],
            ['type' => 'text_image', 'data' => ['title' => 'Di lapangan', 'add_as_submenu' => false]],
        ],
    ]);

    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect($item['subs'])->toHaveCount(1);
    expect($item['subs'][0])->toMatchArray([
        'is_anchor' => true,
        'anchor' => 'our-mission',
        'title' => 'Our Mission',
        'title_id' => 'Our Mission',
        'slug' => 'about',
        'slug_id' => 'tentang',
    ]);

    expect($item['subs_id'])->toHaveCount(1);
    expect($item['subs_id'][0])->toMatchArray([
        'is_anchor' => true,
        'anchor' => 'misi-kami',
        'title' => 'Misi Kami',
        'title_id' => 'Misi Kami',
    ]);
});

it('lets the two languages hold different blocks in different order', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
            ['type' => 'statistic', 'data' => ['submenu_title' => 'Impact', 'add_as_submenu' => true]],
        ],
        // Fewer blocks, flagged in the other order: nothing is paired by index.
        'components_id' => [
            ['type' => 'text_image', 'data' => ['title' => 'Dampak', 'add_as_submenu' => true]],
        ],
    ]);

    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect(array_column($item['subs'], 'anchor'))->toBe(['our-mission', 'impact']);
    expect(array_column($item['subs_id'], 'anchor'))->toBe(['dampak']);
});

it('prefers the submenu title override and falls back per block type', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Long winded heading', 'submenu_title' => 'Mission', 'add_as_submenu' => true]],
            ['type' => 'text_image', 'data' => ['title' => 'Welcome Home', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    $subs = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0.subs');

    expect(array_column($subs, 'anchor'))->toBe(['mission', 'welcome-home']);
});

it('leaves the English list empty when only the Indonesian builder is flagged', function () {
    makeMenuPage([
        'components' => [],
        'components_id' => [
            ['type' => 'paragraph', 'data' => ['submenu_title' => 'Persimpangan', 'add_as_submenu' => true]],
        ],
    ]);

    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect($item['subs'])->toBe([]);
    expect($item['subs_id'])->toHaveCount(1);
    expect($item['subs_id'][0])->toMatchArray([
        'is_anchor' => true,
        'anchor' => 'persimpangan',
        'title' => 'Persimpangan',
    ]);
});

it('keeps anchors unique when two sections share a title', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Impact', 'add_as_submenu' => true]],
            ['type' => 'lead_text', 'data' => ['submenu_title' => 'Impact', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    $subs = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0.subs');

    expect(array_column($subs, 'anchor'))->toBe(['impact', 'impact-2']);
});

it('lists subpages before component anchors', function () {
    $parent = makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    makeMenuPage([
        'title' => 'Team',
        'title_id' => 'Tim',
        'slug' => 'team',
        'slug_id' => 'tim',
        'menu_parent_id' => $parent->id,
    ]);

    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect(array_column($item['subs'], 'title'))->toBe(['Team', 'Our Mission']);
    expect($item['subs'][0]['is_anchor'])->toBeFalse();

    // The subpage heads up both lists; only the anchors differ per language.
    expect(array_column($item['subs_id'], 'title'))->toBe(['Team']);
    expect($item['subs_id'][0]['title_id'])->toBe('Tim');
});

it('skips flagged components that have nothing to label them', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'single_image', 'data' => ['add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    $subs = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0.subs');

    expect($subs)->toBe([]);
});

it('publishes the same anchor on the page component as the menu entry carries', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'banner', 'data' => ['title' => 'Welcome', 'add_as_submenu' => false]],
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
            ['type' => 'statistic', 'data' => ['submenu_title' => 'Impact', 'add_as_submenu' => true]],
        ],
        'components_id' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Misi Kami', 'add_as_submenu' => true]],
        ],
    ]);

    $page = $this->getJson('/api/page/about')->assertOk()->json('data');
    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect($page['components'][0]['data'])->not->toHaveKey('anchor')
        ->and($page['components'][1]['data']['anchor'])->toBe('our-mission')
        ->and($page['components'][2]['data']['anchor'])->toBe('impact')
        ->and($page['components_id'][0]['data']['anchor'])->toBe('misi-kami');

    expect(collect($item['subs'])->pluck('anchor')->all())->toBe(['our-mission', 'impact'])
        ->and(collect($item['subs_id'])->pluck('anchor')->all())->toBe(['misi-kami']);
});

it('numbers repeated page anchors the way the menu does', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Work', 'add_as_submenu' => true]],
            ['type' => 'text_image', 'data' => ['title' => 'Our Work', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    $page = $this->getJson('/api/page/about')->assertOk()->json('data');
    $item = $this->getJson('/api/navigations')->assertOk()->json('0.navigation.0');

    expect(collect($page['components'])->pluck('data.anchor')->all())->toBe(['our-work', 'our-work-2'])
        ->and(collect($item['subs'])->pluck('anchor')->all())->toBe(['our-work', 'our-work-2']);
});

it('leaves a flagged component without a label unanchored', function () {
    makeMenuPage([
        'components' => [
            ['type' => 'single_image', 'data' => ['add_as_submenu' => true]],
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    $page = $this->getJson('/api/page/about')->assertOk()->json('data');

    expect($page['components'][0]['data'])->not->toHaveKey('anchor')
        ->and($page['components'][1]['data']['anchor'])->toBe('our-mission');
});
