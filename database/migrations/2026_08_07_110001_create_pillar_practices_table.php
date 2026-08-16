<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * "Bagaimana ini terlihat di lapangan" — worked examples of a pillar.
     *
     * These live in their own table rather than a json column because each row
     * points at a real Kabupaten. A per-language json list would let the two
     * languages drift onto different kabupatens, so a row carries both languages.
     */
    public function up(): void
    {
        Schema::create('pillar_practices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pillar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedSmallInteger('since_year')->nullable();
            $table->string('image')->nullable();

            $table->string('title')->nullable();
            $table->string('title_id')->nullable();
            $table->text('description')->nullable();
            $table->text('description_id')->nullable();

            $table->integer('sorted_at')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pillar_practices');
    }
};
