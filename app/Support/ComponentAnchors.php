<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Anchors for the page components flagged as "Add as submenu".
 *
 * The menus endpoint lists those components as anchor links under their page,
 * and the page endpoint publishes the same anchor on the component itself, so
 * the frontend can render a section the menu can scroll to. Both read the
 * anchors from here, so the two endpoints cannot drift apart.
 */
class ComponentAnchors
{
    /**
     * The anchor of every flagged component, keyed by its position in the list.
     *
     * A component with nothing to label it is left out: the menu has no entry
     * to show for it, so there is nothing to scroll to either.
     *
     * @param  array<int, mixed>  $components  One language's builder content.
     * @return array<int, string>
     */
    public static function map($components): array
    {
        $components = is_array($components) ? $components : [];

        $anchors = [];
        $taken = [];

        foreach ($components as $key => $component) {
            $data = is_array($component) ? ($component['data'] ?? []) : [];

            if (empty($data['add_as_submenu'])) {
                continue;
            }

            $title = self::title($data);

            if ($title === null) {
                continue;
            }

            $anchor = Str::slug($title) ?: 'section';

            // Two sections can carry the same title; keep the anchors unique.
            if (isset($taken[$anchor])) {
                $taken[$anchor]++;
                $anchor .= '-' . $taken[$anchor];
            } else {
                $taken[$anchor] = 1;
            }

            $anchors[$key] = $anchor;
        }

        return $anchors;
    }

    /**
     * Resolve the label of a submenu entry: the explicit override first, then
     * whichever title-ish field the block happens to carry.
     *
     * @param  array<string, mixed>  $data
     */
    public static function title($data): ?string
    {
        if (! is_array($data)) {
            return null;
        }

        $keys = ['submenu_title', 'title', 'banner_title', 'label', 'banner_label', 'caption'];

        foreach ($keys as $key) {
            $value = trim(strip_tags((string) ($data[$key] ?? '')));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
