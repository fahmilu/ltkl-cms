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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_default')->default(0);
            $table->boolean('is_active')->default(0);
            $table->string('template')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->json('components')->nullable();
            $table->boolean('meta_is_hidden')->default(true)->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->tinyInteger('menu_parent_id')->default(null)->nullable();
            $table->boolean('menu_is_active')->default(false)->nullable();
            $table->boolean('menu_is_external')->default(false)->nullable();
            $table->string('menu_title')->nullable();
            $table->text('menu_url')->nullable();
            $table->string('menu_url_target')->nullable();
            $table->integer('sorted_at')->default(0)->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
