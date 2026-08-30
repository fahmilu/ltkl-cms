<?php

namespace App\Support;

use App\Models\Kabupaten;
use App\Models\Page;

/**
 * Builds the frontend menus out of the pages.
 *
 * A page can sit in several menu groups at once, so the same entry is repeated
 * under every group it names. Its children are the pages parented to it plus
 * any of its own components flagged as "Add as submenu", which become anchor
 * links pointing back at the page.
 */
class MenuBuilder
{
    /**
     * The kabupaten entries, built once per request and only when a menu entry
     * actually asks for them.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $kabupatenChildren = null;

    /**
     * Every menu group, or just the one named.
     *
     * @return array<int, array{group: string, navigation: array<int, array<string, mixed>>}>
     */
    public function groups(?string $only = null): array
    {
        $only = is_string($only) ? trim($only) : null;

        $pages = Page::where('menu_is_active', true)
            ->whereNotNull('menu_group')
            ->whereNull('menu_parent_id')
            ->with(['subpages' => function ($query) {
                $query->where('menu_is_active', true)
                    ->orderBy('sorted_at', 'asc');
            }])
            ->orderBy('sorted_at', 'asc')
            ->get();

        $grouped = [];
        foreach ($pages as $page) {
            $groups = $this->menuGroups($page);

            if ($only !== null && $only !== '') {
                $groups = array_values(array_filter($groups, fn(string $group): bool => $group === $only));
            }

            if ($groups === []) {
                continue;
            }

            // Built once, then repeated under each group the page belongs to.
            $item = $this->item($page);

            foreach ($groups as $group) {
                $grouped[$group][] = $item;
            }
        }

        $result = [];
        foreach ($grouped as $group => $navigation) {
            if (!empty($navigation)) {
                $result[] = [
                    'group' => $group,
                    'navigation' => $navigation,
                ];
            }
        }

        return $result;
    }

    /**
     * One top-level entry, with its subpages and component anchors below it.
     *
     * The anchors are per language: each language has its own builder, with its
     * own blocks in its own order, so `subs` follows the English components and
     * `subs_id` the Indonesian ones. Subpages are real pages carrying both
     * titles, so they head up both lists.
     *
     * @return array<string, mixed>
     */
    private function item(Page $page): array
    {
        $subpages = [];
        foreach ($page->subpages as $subpage) {
            $subpages[] = [
                'resource' => 'page',
                'is_external' => (bool) $subpage->menu_is_external,
                'is_anchor' => false,
                'anchor' => '',
                'title' => $subpage->title,
                'title_id' => $subpage->title_id ?? '',
                'slug' => $subpage->slug,
                'slug_id' => $subpage->slug_id ?? '',
                'url' => $subpage->menu_url ?? '',
                'url_target' => $subpage->menu_url_target ?? '',
            ];
        }

        $kabupatens = $this->kabupatenChildren($page);

        return [
            'is_external' => (bool) $page->menu_is_external,
            'title' => $page->title,
            'title_id' => $page->title_id ?? '',
            'slug' => $page->slug,
            'slug_id' => $page->slug_id ?? '',
            'url' => $page->menu_url ?? '',
            'url_target' => $page->menu_url_target ?? '',
            'subs' => [...$subpages, ...$this->componentAnchors($page, 'components'), ...$kabupatens],
            'subs_id' => [...$subpages, ...$this->componentAnchors($page, 'components_id'), ...$kabupatens],
        ];
    }

    /**
     * The kabupaten entries below a member menu entry.
     *
     * The members page has no subpage per kabupaten — the kabupatens are their
     * own records — so the menu for that one entry is filled from them instead.
     * Any other entry gets an empty list and is built as before.
     *
     * @return array<int, array<string, mixed>>
     */
    private function kabupatenChildren(Page $page): array
    {
        if (! $this->wantsKabupatenChildren($page)) {
            return [];
        }

        return $this->kabupatenChildren ??= Kabupaten::where('is_active', true)
            ->orderBy('sorted_at', 'asc')
            ->get()
            ->map(fn(Kabupaten $kabupaten): array => [
                'resource' => 'kabupaten',
                'is_external' => false,
                'is_anchor' => false,
                'anchor' => '',
                'title' => $kabupaten->title,
                'title_id' => $kabupaten->title_id ?? '',
                'slug' => $kabupaten->slug,
                'slug_id' => $kabupaten->slug_id ?? '',
                'url' => '',
                'url_target' => '',
            ])
            ->all();
    }

    /**
     * Whether this entry is the members one, by either of its slugs.
     */
    private function wantsKabupatenChildren(Page $page): bool
    {
        $slugs = array_map(
            fn($slug): string => mb_strtolower(trim((string) $slug)),
            (array) config('menu.kabupaten_children_slugs', []),
        );

        return in_array(mb_strtolower((string) $page->slug), $slugs, true)
            || in_array(mb_strtolower((string) $page->slug_id), $slugs, true);
    }

    /**
     * The menu groups a page belongs to, as a clean list of names.
     *
     * @return array<int, string>
     */
    private function menuGroups(Page $page): array
    {
        $groups = $page->menu_group;

        // A row saved before the column held a list still reads as one name.
        if (is_string($groups)) {
            $decoded = json_decode($groups, true);
            $groups = is_array($decoded) ? $decoded : [$groups];
        }

        if (!is_array($groups)) {
            return [];
        }

        $names = [];
        foreach ($groups as $group) {
            $group = is_string($group) ? trim($group) : '';

            if ($group !== '' && !in_array($group, $names, true)) {
                $names[] = $group;
            }
        }

        return $names;
    }

    /**
     * Build the anchor submenu entries for a page from the components that
     * have "Add as submenu" switched on. They point at the page itself, with
     * an anchor slug the frontend can scroll to.
     *
     * @return array<int, array<string, mixed>>
     */
    private function componentAnchors(Page $page, string $column): array
    {
        $components = is_array($page->{$column}) ? $page->{$column} : [];

        $anchors = [];

        foreach (ComponentAnchors::map($components) as $key => $anchor) {
            $title = ComponentAnchors::title($components[$key]['data'] ?? []);

            // The list is already the one language's, so both title fields
            // carry its label and the entry stays shaped like a subpage.
            $anchors[] = [
                'resource' => 'anchor',
                'is_external' => false,
                'is_anchor' => true,
                'anchor' => $anchor,
                'title' => $title,
                'title_id' => $title,
                'slug' => $page->slug,
                'slug_id' => $page->slug_id ?? '',
                'url' => $page->menu_url ?? '',
                'url_target' => $page->menu_url_target ?? '',
            ];
        }

        return $anchors;
    }
}
