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
        Schema::create('job_opportunities', function (Blueprint $table) {
            $table->id();
            // Publishing and taking applications are separate: a vacancy can be
            // published and closed, so it stays readable after the hiring ends.
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('open');
            $table->string('employment_type')->nullable();

            $table->string('title');
            $table->string('title_id')->nullable();
            $table->string('slug');
            $table->string('slug_id')->nullable();

            $table->string('location')->nullable();
            $table->string('location_id')->nullable();

            $table->text('description')->nullable();
            $table->text('description_id')->nullable();

            $table->text('how_to_apply')->nullable();
            $table->text('how_to_apply_id')->nullable();

            $table->string('contact_email')->nullable();
            $table->string('apply_url')->nullable();
            // The full terms of reference, as the site publishes them today.
            $table->string('attachment')->nullable();

            $table->date('posted_at')->nullable();
            $table->date('deadline_at')->nullable();

            $table->integer('sorted_at')->default(0)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('slug_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_opportunities');
    }
};
