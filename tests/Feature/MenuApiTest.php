<?php

use App\Models\Kabupaten;
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

function makeMenuKabupaten(array $attributes = []): Kabupaten
{
    return Kabupaten::create(array_merge([
        'is_active' => true,
        'title' => 'Siak Regency',
        'title_id' => 'Kabupaten Siak',
        'slug' => 'siak-regency',
        'slug_id' => 'kabupaten-siak',
        'sorted_at' => 1,
    ], $attributes));
}

it('lists the kabupatens under the members entry', function () {
    makeMenuKabupaten(['sorted_at' => 2]);
    makeMenuKabupaten([
        'title' => 'Sintang Regency',
        'title_id' => 'Kabupaten Sintang',
        'slug' => 'sintang-regency',
        'slug_id' => 'kabupaten-sintang',
        'sorted_at' => 1,
    ]);

    makeMenuEntry(['main'], [
        'title' => 'Members',
        'title_id' => 'Anggota',
        'slug' => 'members',
        'slug_id' => 'anggota',
    ]);

    $item = $this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0');

    // Ordered by sorted_at, like /api/kabupatens.
    expect(array_column($item['subs'], 'title'))->toBe(['Sintang Regency', 'Siak Regency'])
        ->and(array_column($item['subs_id'], 'title_id'))->toBe(['Kabupaten Sintang', 'Kabupaten Siak']);

    expect($item['subs'][0])->toMatchArray([
        'resource' => 'kabupaten',
        'is_anchor' => false,
        'is_external' => false,
        'slug' => 'sintang-regency',
        'slug_id' => 'kabupaten-sintang',
    ]);
});

it('keeps an inactive kabupaten out of the members entry', function () {
    makeMenuKabupaten(['is_active' => false]);

    makeMenuEntry(['main'], [
        'title' => 'Members',
        'title_id' => 'Anggota',
        'slug' => 'members',
        'slug_id' => 'anggota',
    ]);

    expect($this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0.subs'))->toBe([]);
});

it('matches the members entry on the indonesian slug alone', function () {
    makeMenuKabupaten();

    makeMenuEntry(['main'], [
        'title' => 'Our Members',
        'title_id' => 'Anggota Kami',
        'slug' => 'our-members',
        'slug_id' => 'anggota',
    ]);

    expect($this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0.subs'))->toHaveCount(1);
});

it('appends the kabupatens after the subpages and anchors it already has', function () {
    makeMenuKabupaten();

    $parent = makeMenuEntry(['main'], [
        'title' => 'Members',
        'title_id' => 'Anggota',
        'slug' => 'members',
        'slug_id' => 'anggota',
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
    ]);

    makeMenuEntry(['main'], [
        'title' => 'Team',
        'title_id' => 'Tim',
        'slug' => 'team',
        'slug_id' => 'tim',
        'menu_parent_id' => $parent->id,
    ]);

    $subs = $this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0.subs');

    expect(array_column($subs, 'resource'))->toBe(['page', 'anchor', 'kabupaten']);
});

it('leaves any other entry without kabupatens', function () {
    makeMenuKabupaten();
    makeMenuEntry(['main']);

    expect($this->getJson('/api/menus?group=main')->assertOk()->json('0.navigation.0.subs'))->toBe([]);
});
