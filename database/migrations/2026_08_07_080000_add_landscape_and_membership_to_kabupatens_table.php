<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            // Lanskap
            $table->decimal('forest_cover_ha', 14, 2)->nullable()->after('content_id');
            $table->decimal('peatland_ha', 14, 2)->nullable()->after('forest_cover_ha');
            $table->decimal('area_km2', 14, 2)->nullable()->after('peatland_ha');
            $table->string('city')->nullable()->after('area_km2');
            $table->string('province')->nullable()->after('city');

            // Membership
            $table->boolean('is_founding_member')->default(false)->after('province');
            $table->unsignedSmallInteger('joined_year')->nullable()->after('is_founding_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn([
                'forest_cover_ha',
                'peatland_ha',
                'area_km2',
                'city',
                'province',
                'is_founding_member',
                'joined_year',
            ]);
        });
    }
};
