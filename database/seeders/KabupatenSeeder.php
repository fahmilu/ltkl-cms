<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use Illuminate\Database\Seeder;

/**
 * The nine member kabupatens listed on the LTKL platform.
 *
 * Names and logos come from https://ltkl-platform.vercel.app (the "Kabupaten
 * Anggota" list). Province, seat and coordinates are not published there, so
 * they are filled in from the regency seat of government — accurate enough for
 * a map pin, but worth reviewing before launch. Membership fields
 * (is_founding_member, joined_year) are left untouched because the platform
 * does not state them.
 *
 * Rows are matched on slug, so re-running updates in place. Existing rows
 * created before this seeder are matched through their legacy slug.
 */
class KabupatenSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const MEMBERS = [
        [
            'title' => 'Aceh Tamiang',
            'slug' => 'aceh-tamiang',
            'legacy_slug' => 'aceh-tamiang',
            'city' => 'Karang Baru',
            'province' => 'Aceh',
            'latitude' => 4.2122625,
            'longitude' => 97.9146797,
            'image' => 'kabupatens/acehtamiang.png',
            'sorted_at' => 1,
        ],
        [
            'title' => 'Musi Banyuasin',
            'slug' => 'musi-banyuasin',
            'city' => 'Sekayu',
            'province' => 'Sumatera Selatan',
            'latitude' => -2.8666667,
            'longitude' => 103.8500000,
            'image' => 'kabupatens/musibanyuasin.png',
            'sorted_at' => 2,
        ],
        [
            'title' => 'Siak',
            'slug' => 'siak',
            'city' => 'Siak Sri Indrapura',
            'province' => 'Riau',
            'latitude' => 0.7940000,
            'longitude' => 102.0500000,
            'image' => 'kabupatens/siak.webp',
            'sorted_at' => 3,
        ],
        [
            'title' => 'Sanggau',
            'slug' => 'sanggau',
            'city' => 'Sanggau',
            'province' => 'Kalimantan Barat',
            'latitude' => 0.1219000,
            'longitude' => 110.5960000,
            'image' => 'kabupatens/sanggau.png',
            'sorted_at' => 4,
        ],
        [
            'title' => 'Sintang',
            'slug' => 'sintang',
            'city' => 'Sintang',
            'province' => 'Kalimantan Barat',
            'latitude' => 0.0833000,
            'longitude' => 111.4833000,
            'image' => 'kabupatens/sintang.png',
            'sorted_at' => 5,
        ],
        [
            'title' => 'Kapuas Hulu',
            'slug' => 'kapuas-hulu',
            'city' => 'Putussibau',
            'province' => 'Kalimantan Barat',
            'latitude' => 0.8333000,
            'longitude' => 112.9333000,
            'image' => 'kabupatens/kapuashulu.png',
            'sorted_at' => 6,
        ],
        [
            'title' => 'Sigi',
            'slug' => 'sigi',
            'legacy_slug' => 'kab-sigi',
            'city' => 'Sigi Biromaru',
            'province' => 'Sulawesi Tengah',
            'latitude' => -1.3211588,
            'longitude' => 119.9987265,
            'image' => 'kabupatens/sigi.png',
            'sorted_at' => 7,
        ],
        [
            'title' => 'Gorontalo',
            'slug' => 'gorontalo',
            'city' => 'Limboto',
            'province' => 'Gorontalo',
            'latitude' => 0.6236000,
            'longitude' => 122.9986000,
            'image' => 'kabupatens/gorontalo.png',
            'sorted_at' => 8,
        ],
        [
            'title' => 'Bone Bolango',
            'slug' => 'bone-bolango',
            'city' => 'Suwawa',
            'province' => 'Gorontalo',
            'latitude' => 0.5586000,
            'longitude' => 123.1360000,
            'image' => 'kabupatens/bonelango.png',
            'sorted_at' => 9,
        ],
    ];

    public function run(): void
    {
        $created = 0;
        $updated = 0;

        foreach (self::MEMBERS as $member) {
            $slugs = array_unique([$member['slug'], $member['legacy_slug'] ?? $member['slug']]);

            $kabupaten = Kabupaten::withTrashed()
                ->where(function ($query) use ($slugs) {
                    $query->whereIn('slug', $slugs)->orWhereIn('slug_id', $slugs);
                })
                ->first();

            $attributes = [
                'is_active' => true,
                'image' => $member['image'],
                'title' => $member['title'],
                'title_id' => $member['title'],
                'slug' => $member['slug'],
                'slug_id' => $member['slug'],
                'city' => $member['city'],
                'province' => $member['province'],
                'latitude' => $member['latitude'],
                'longitude' => $member['longitude'],
                'sorted_at' => $member['sorted_at'],
            ];

            if ($kabupaten) {
                $kabupaten->restore();
                $kabupaten->fill($attributes)->save();
                $updated++;

                continue;
            }

            Kabupaten::create($attributes);
            $created++;
        }

        $this->command?->info("Kabupatens: {$created} created, {$updated} updated.");
    }
}
