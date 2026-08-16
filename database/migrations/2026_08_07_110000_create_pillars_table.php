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
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();

            $table->string('title');
            $table->string('title_id')->nullable();
            $table->string('slug');
            $table->string('slug_id')->nullable();

            // "Istilah teknis" on the pillar header.
            $table->string('technical_term')->nullable();
            $table->string('technical_term_id')->nullable();

            $table->text('description')->nullable();
            $table->text('description_id')->nullable();

            // Header statistics and "Hasil pilar ini" rows. One list per language,
            // matching how kabupaten commodities and achievements are stored.
            $table->json('statistics')->nullable();
            $table->json('statistics_id')->nullable();
            $table->json('results')->nullable();
            $table->json('results_id')->nullable();

            // Drives the "Pilar 01" numbering, which is the list position.
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
        Schema::dropIfExists('pillars');
    }
};
