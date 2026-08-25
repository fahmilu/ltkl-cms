<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peatland gives way to protected area, and the two land programmes join it.
 *
 * Social forestry (perhutanan sosial) and TORA (Tanah Objek Reforma Agraria)
 * are reported in hectares like the rest of the landscape figures.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->renameColumn('peatland_ha', 'protected_area_ha');
        });

        Schema::table('kabupatens', function (Blueprint $table) {
            $table->decimal('social_forestry_ha', 14, 2)->nullable()->after('protected_area_ha');
            $table->decimal('tora_ha', 14, 2)->nullable()->after('social_forestry_ha');
        });
    }

    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn(['social_forestry_ha', 'tora_ha']);
        });

        Schema::table('kabupatens', function (Blueprint $table) {
            $table->renameColumn('protected_area_ha', 'peatland_ha');
        });
    }
};
