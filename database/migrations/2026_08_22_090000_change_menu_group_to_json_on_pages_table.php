<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Wrap the single group in a list first: MySQL validates the existing
        // contents when the column becomes json, so it has to be valid JSON
        // before the type changes.
        DB::table('pages')
            ->whereNotNull('menu_group')
            ->orderBy('id')
            ->each(function ($page) {
                $group = $page->menu_group;

                // Already a list, so a re-run leaves it alone.
                if (is_array(json_decode((string) $group, true))) {
                    return;
                }

                DB::table('pages')
                    ->where('id', $page->id)
                    ->update(['menu_group' => json_encode([$group])]);
            });

        Schema::table('pages', function (Blueprint $table) {
            $table->json('menu_group')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('menu_group')->nullable()->change();
        });

        // Only the first group survives: the old column holds one.
        DB::table('pages')
            ->whereNotNull('menu_group')
            ->orderBy('id')
            ->each(function ($page) {
                $groups = json_decode((string) $page->menu_group, true);

                if (!is_array($groups)) {
                    return;
                }

                DB::table('pages')
                    ->where('id', $page->id)
                    ->update(['menu_group' => $groups[0] ?? null]);
            });
    }
};
