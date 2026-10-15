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
        Schema::table('job_opportunities', function (Blueprint $table) {
            // A vacancy reads About, Description, Qualifications, How to Apply.
            $table->text('about')->nullable()->after('location_id');
            $table->text('about_id')->nullable()->after('about');
            $table->text('qualifications')->nullable()->after('description_id');
            $table->text('qualifications_id')->nullable()->after('qualifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_opportunities', function (Blueprint $table) {
            $table->dropColumn(['about', 'about_id', 'qualifications', 'qualifications_id']);
        });
    }
};
