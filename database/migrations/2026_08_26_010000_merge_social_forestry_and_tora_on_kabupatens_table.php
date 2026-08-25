<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social forestry and TORA are reported as one figure, not two.
 *
 * The two columns were added in the migration before this one and never
 * carried a value, so they are replaced outright rather than summed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn(['social_forestry_ha', 'tora_ha']);
        });

        Schema::table('kabupatens', function (Blueprint $table) {
            $table->decimal('social_forestry_tora_ha', 14, 2)->nullable()->after('protected_area_ha');
        });
    }

    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn('social_forestry_tora_ha');
        });

        Schema::table('kabupatens', function (Blueprint $table) {
            $table->decimal('social_forestry_ha', 14, 2)->nullable()->after('protected_area_ha');
            $table->decimal('tora_ha', 14, 2)->nullable()->after('social_forestry_ha');
        });
    }
};
