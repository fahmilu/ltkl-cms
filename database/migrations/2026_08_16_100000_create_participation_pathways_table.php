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
        Schema::create('participation_pathways', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();

            $table->string('title');
            $table->string('title_id')->nullable();
            $table->string('slug');
            $table->string('slug_id')->nullable();

            $table->text('description')->nullable();
            $table->text('description_id')->nullable();

            // Page builder content, one list per language, as on pages and posts.
            $table->json('components')->nullable();
            $table->json('components_id')->nullable();

            $table->integer('sorted_at')->default(0)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participation_pathways');
    }
};
