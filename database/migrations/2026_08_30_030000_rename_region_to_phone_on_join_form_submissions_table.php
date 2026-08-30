<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The join form asks for a phone number instead of a kabupaten / region.
     */
    public function up(): void
    {
        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->renameColumn('region', 'phone');
        });
    }

    public function down(): void
    {
        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->renameColumn('phone', 'region');
        });
    }
};
