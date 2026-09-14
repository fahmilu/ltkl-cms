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
        Schema::table('kabupatens', function (Blueprint $table) {
            // Replaces "Founding Member" on the admin form. is_founding_member
            // stays on the table and keeps serving the API; it is just no
            // longer edited here.
            $table->string('dashboard_url')->nullable()->after('is_founding_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn('dashboard_url');
        });
    }
};
