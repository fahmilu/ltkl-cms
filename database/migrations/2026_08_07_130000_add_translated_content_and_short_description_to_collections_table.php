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
        Schema::table('collections', function (Blueprint $table) {
            // content already exists as the English copy; this is its translation.
            $table->text('content_id')->nullable()->after('content');
            $table->text('short_description')->nullable()->after('content_id');
            $table->text('short_description_id')->nullable()->after('short_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['content_id', 'short_description', 'short_description_id']);
        });
    }
};
