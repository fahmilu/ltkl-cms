<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Cerita Gerakan": an intro block plus the posts picked as the stories of
     * this kabupaten. It is copy, so like the commodities and the impacts each
     * language keeps its own.
     */
    public function up(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->string('story_label')->nullable()->after('achievements_id');
            $table->string('story_label_id')->nullable()->after('story_label');
            $table->string('story_title')->nullable()->after('story_label_id');
            $table->string('story_title_id')->nullable()->after('story_title');
            $table->text('story_description')->nullable()->after('story_title_id');
            $table->text('story_description_id')->nullable()->after('story_description');
            $table->string('story_image')->nullable()->after('story_description_id');
            $table->string('story_image_id')->nullable()->after('story_image');
            // The picked posts, as an ordered list of ids.
            $table->json('story_posts')->nullable()->after('story_image_id');
            $table->json('story_posts_id')->nullable()->after('story_posts');
        });
    }

    public function down(): void
    {
        Schema::table('kabupatens', function (Blueprint $table) {
            $table->dropColumn([
                'story_label',
                'story_label_id',
                'story_title',
                'story_title_id',
                'story_description',
                'story_description_id',
                'story_image',
                'story_image_id',
                'story_posts',
                'story_posts_id',
            ]);
        });
    }
};
