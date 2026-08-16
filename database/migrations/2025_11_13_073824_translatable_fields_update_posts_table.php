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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_id')->after('title')->nullable();
            $table->string('slug_id')->after('slug')->nullable();
            $table->json('components_id')->after('components')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('title_id');
            $table->dropColumn('slug_id');
            $table->dropColumn('components_id');
        });
    }
};
