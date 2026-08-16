<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private const LEGACY_TYPE = 'participation_pathway';

    /**
     * Copy participation pathways out of the shared collections table.
     *
     * The source rows are left in place on purpose: they become invisible in the
     * CMS once the collection type is retired, but nothing is destroyed, so the
     * copy can be verified before anyone deletes them.
     */
    public function up(): void
    {
        $collections = DB::table('collections')
            ->where('type', self::LEGACY_TYPE)
            ->orderBy('id')
            ->get();

        foreach ($collections as $collection) {
            $exists = DB::table('participation_pathways')
                ->where('slug', $collection->slug)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('participation_pathways')->insert([
                'is_active' => true,
                'image' => $collection->image,
                'title' => $collection->title,
                'title_id' => $collection->title_id,
                'slug' => $collection->slug,
                'slug_id' => $collection->slug_id,
                // content held the long copy; short_description is the fallback.
                'description' => $collection->content ?: $collection->short_description,
                'description_id' => $collection->content_id ?: $collection->short_description_id,
                'components' => null,
                'components_id' => null,
                'sorted_at' => $collection->sorted_at ?? 0,
                'deleted_at' => $collection->deleted_at,
                'created_at' => $collection->created_at,
                'updated_at' => $collection->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * The collection rows were never removed, so rolling back only discards the
     * copies made above.
     */
    public function down(): void
    {
        $slugs = DB::table('collections')
            ->where('type', self::LEGACY_TYPE)
            ->pluck('slug');

        DB::table('participation_pathways')->whereIn('slug', $slugs)->delete();
    }
};
