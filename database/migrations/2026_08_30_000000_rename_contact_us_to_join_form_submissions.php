<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contact us becomes the join form: the subject line goes away, the free
     * text affiliation becomes the organisation, and a submission now carries
     * the region and the participation pathway the visitor picked.
     */
    public function up(): void
    {
        Schema::rename('contact_us', 'join_form_submissions');

        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->renameColumn('affiliation', 'organization');
        });

        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->dropColumn('subject');
        });

        Schema::table('join_form_submissions', function (Blueprint $table) {
            // Nullable so the rows submitted before this migration stay valid;
            // the API requires both on every new submission.
            $table->string('region')->nullable()->after('organization');
            $table->foreignId('participation_pathway_id')
                ->nullable()
                ->after('region')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('participation_pathway_id');
            $table->dropColumn('region');
        });

        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->string('subject')->after('organization');
        });

        Schema::table('join_form_submissions', function (Blueprint $table) {
            $table->renameColumn('organization', 'affiliation');
        });

        Schema::rename('join_form_submissions', 'contact_us');
    }
};
