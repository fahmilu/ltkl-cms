<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMenuEntry(array $groups, array $attributes = []): Page
{
    return Page::create(array_merge([
        'is_active' => true,
        'menu_is_active' => true,
        'menu_group' => $groups,
        'title' => 'About',
        'title_id' => 'Tentang',
        'slug' => 'about',
        'slug_id' => 'tentang',
    ], $attributes));
}

it('returns every group with its menu items', function () {
    makeMenuEntry(['header', 'footer']);
    makeMenuEntry(['footer'], ['title' => 'Contact', 'slug' => 'contact', 'slug_id' => 'kontak']);

    $response = $this->getJson('/api/menus')->assertOk()->json();

    expect(array_column($response, 'group'))->toBe(['header', 'footer']);
    expect(array_column($response[1]['navigation'], 'title'))->toBe(['About', 'Contact']);
});

it('filters down to one group', function () {
    makeMenuEntry(['header', 'footer']);
    makeMenuEntry(['footer'], ['title' => 'Contact', 'slug' => 'contact', 'slug_id' => 'kontak']);

    $response = $this->getJson('/api/menus?group=header')->assertOk()->json();

    expect($response)->toHaveCount(1);
    expect($response[0]['group'])->toBe('header');
    expect(array_column($response[0]['navigation'], 'title'))->toBe(['About']);
});

it('returns nothing for a group no page uses', function () {
    makeMenuEntry(['header']);

    expect($this->getJson('/api/menus?group=sidebar')->assertOk()->json())->toBe([]);
});

it('nests subpages and component anchors under the item', function () {
    $parent = makeMenuEntry(['main'], [
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
        'components_id' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Misi Kami', 'add_as_submenu' => true]],
        ],
    ]);

    makeMenuEntry(['main'], [
        'title' => 'Team',
        'title_id' => 'Tim',
        'slug' => 'team',
        'slug_id' => 'tim',
        'menu_parent_id' => $parent->id,
    ]);

    $item = $this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0');

    expect($item['subs'][0])->toMatchArray([
        'is_anchor' => false,
        'title' => 'Team',
        'slug' => 'team',
    ]);
    expect($item['subs'][1])->toMatchArray([
        'is_anchor' => true,
        'anchor' => 'our-mission',
        'title' => 'Our Mission',
        'slug' => 'about',
    ]);

    // Same page, the Indonesian builder's own anchor.
    expect($item['subs_id'][1])->toMatchArray([
        'is_anchor' => true,
        'anchor' => 'misi-kami',
        'title' => 'Misi Kami',
        'slug' => 'about',
    ]);
});

it('leaves the navigations endpoint answering the same payload', function () {
    makeMenuEntry(['header', 'footer'], [
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    expect($this->getJson('/api/navigations')->assertOk()->json())
        ->toBe($this->getJson('/api/menus')->assertOk()->json());
});
