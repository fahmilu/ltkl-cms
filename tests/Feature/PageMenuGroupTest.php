<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeGroupedPage(array $groups, array $attributes = []): Page
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

it('lists a page in every group it belongs to', function () {
    makeGroupedPage(['header', 'footer']);

    $response = $this->getJson('/api/navigations')->assertOk()->json();

    expect(array_column($response, 'group'))->toBe(['header', 'footer']);
    expect($response[0]['navigation'][0]['title'])->toBe('About')
        ->and($response[1]['navigation'][0]['title'])->toBe('About');
});

it('keeps single group pages working', function () {
    makeGroupedPage(['main']);
    makeGroupedPage(['footer'], ['title' => 'Contact', 'slug' => 'contact', 'slug_id' => 'kontak']);

    $response = $this->getJson('/api/navigations')->assertOk()->json();

    expect($response)->toHaveCount(2);
    expect($response[0])->toMatchArray(['group' => 'main']);
    expect($response[1]['navigation'][0]['slug'])->toBe('contact');
});

it('skips pages with no group at all', function () {
    makeGroupedPage([]);
    makeGroupedPage(['main'], ['title' => 'Contact', 'slug' => 'contact', 'slug_id' => 'kontak']);

    $response = $this->getJson('/api/navigations')->assertOk()->json();

    expect($response)->toHaveCount(1);
    expect($response[0]['navigation'])->toHaveCount(1)
        ->and($response[0]['navigation'][0]['title'])->toBe('Contact');
});

it('still reads a row left holding a bare group name', function () {
    $page = makeGroupedPage(['main']);

    // Simulate a row the migration never touched.
    DB::table('pages')->where('id', $page->id)->update(['menu_group' => 'main']);

    $response = $this->getJson('/api/navigations')->assertOk()->json();

    expect($response)->toHaveCount(1);
    expect($response[0]['group'])->toBe('main');
});

it('repeats subpages and anchors under the page in each group', function () {
    $parent = makeGroupedPage(['header', 'footer'], [
        'components' => [
            ['type' => 'paragraph', 'data' => ['title' => 'Our Mission', 'add_as_submenu' => true]],
        ],
        'components_id' => [],
    ]);

    makeGroupedPage(['header'], [
        'title' => 'Team',
        'slug' => 'team',
        'slug_id' => 'tim',
        'menu_parent_id' => $parent->id,
    ]);

    $response = $this->getJson('/api/navigations')->assertOk()->json();

    foreach ([0, 1] as $group) {
        expect(array_column($response[$group]['navigation'][0]['subs'], 'title'))
            ->toBe(['Team', 'Our Mission']);
    }
});
