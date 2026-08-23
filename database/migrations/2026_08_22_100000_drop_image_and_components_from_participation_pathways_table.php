<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participation_pathways', function (Blueprint $table) {
            $table->dropColumn(['image', 'components', 'components_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * The columns come back empty: dropping them discards whatever they held.
     */
    public function down(): void
    {
        Schema::table('participation_pathways', function (Blueprint $table) {
            $table->string('image')->nullable()->after('is_active');
            $table->json('components')->nullable()->after('description_id');
            $table->json('components_id')->nullable()->after('components');
        });
    }
};
