<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wide header image for the kabupaten page, kept apart from the card image.
     */
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->string('banner')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn('banner');
        });
    }
};
