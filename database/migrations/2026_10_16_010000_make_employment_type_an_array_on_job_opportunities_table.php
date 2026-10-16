<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // A vacancy can be offered under more than one engagement, so the
        // single type becomes a list. Existing types carry over as a list of one.
        DB::table('job_opportunities')->whereNotNull('employment_type')->orderBy('id')
            ->each(function ($row) {
                DB::table('job_opportunities')->where('id', $row->id)
                    ->update(['employment_type' => json_encode([$row->employment_type])]);
            });

        Schema::table('job_opportunities', function (Blueprint $table) {
            $table->json('employment_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_opportunities', function (Blueprint $table) {
            $table->string('employment_type')->nullable()->change();
        });

        // Only the first type survives the way back.
        DB::table('job_opportunities')->whereNotNull('employment_type')->orderBy('id')
            ->each(function ($row) {
                $types = json_decode($row->employment_type, true);

                DB::table('job_opportunities')->where('id', $row->id)
                    ->update(['employment_type' => is_array($types) ? ($types[0] ?? null) : null]);
            });
    }
};
