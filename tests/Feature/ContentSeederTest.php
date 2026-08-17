<?php

use App\Models\Kabupaten;
use App\Models\ParticipationPathway;
use App\Models\Pillar;
use App\Models\Post;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * These run against a freshly migrated, empty database, so they prove the
 * snapshot can rebuild the site from nothing.
 */
beforeEach(function () {
    $this->seed(ContentSeeder::class);
});

it('rebuilds every content table from the snapshot', function () {
    expect(DB::table('pages')->count())->toBeGreaterThan(0)
        ->and(DB::table('posts')->count())->toBeGreaterThan(0)
        ->and(DB::table('collections')->count())->toBeGreaterThan(0)
        ->and(DB::table('kabupatens')->count())->toBeGreaterThan(0)
        ->and(DB::table('pillars')->count())->toBeGreaterThan(0)
        ->and(DB::table('participation_pathways')->count())->toBeGreaterThan(0)
        ->and(DB::table('settings')->count())->toBeGreaterThan(0);
});

it('relinks pillar practices to the right kabupaten', function () {
    $practices = DB::table('pillar_practices')->get();

    expect($practices)->not->toBeEmpty();

    foreach ($practices as $practice) {
        // Every practice must resolve to a pillar, and any kabupaten it names
        // must exist rather than pointing at a stale id from the source database.
        expect(Pillar::find($practice->pillar_id))->not->toBeNull();

        if ($practice->kabupaten_id !== null) {
            expect(Kabupaten::find($practice->kabupaten_id))->not->toBeNull();
        }
    }
});

it('relinks post relations to existing records', function () {
    foreach (['post_tags', 'post_topics'] as $table) {
        foreach (DB::table($table)->get() as $pivot) {
            expect(Post::find($pivot->post_id))->not->toBeNull()
                ->and(DB::table('collections')->find($pivot->collection_id))->not->toBeNull();
        }
    }

    foreach (DB::table('post_kabupatens')->get() as $pivot) {
        expect(Kabupaten::find($pivot->kabupaten_id))->not->toBeNull();
    }
});

it('relinks kabupaten to pillar pivots', function () {
    $pivots = DB::table('kabupaten_pillars')->get();

    expect($pivots)->not->toBeEmpty();

    foreach ($pivots as $pivot) {
        expect(Kabupaten::find($pivot->kabupaten_id))->not->toBeNull()
            ->and(Pillar::find($pivot->pillar_id))->not->toBeNull();
    }
});

it('keeps json columns usable as arrays', function () {
    $pillar = Pillar::whereNotNull('statistics_id')->first();

    expect($pillar)->not->toBeNull()
        ->and($pillar->statistics_id)->toBeArray()
        ->and($pillar->statistics_id[0])->toHaveKeys(['value', 'label']);

    $post = Post::whereNotNull('type_data')->first();

    expect($post->type_data)->toBeArray();
});

it('leaves the retired participation pathway collection type out', function () {
    expect(DB::table('collections')->where('type', 'participation_pathway')->count())->toBe(0)
        // The pathway content itself is restored to its own table.
        ->and(ParticipationPathway::count())->toBeGreaterThan(0);
});

it('serves the rebuilt content through the api', function () {
    $this->getJson('/api/posts')->assertOk()->assertJsonPath('meta.total', Post::count());
    $this->getJson('/api/pillars')->assertOk();
    $this->getJson('/api/kabupatens')->assertOk();
    $this->getJson('/api/participation-pathways')->assertOk();
});

it('can be run twice without duplicating anything', function () {
    $before = [
        'posts' => DB::table('posts')->count(),
        'collections' => DB::table('collections')->count(),
        'pillars' => DB::table('pillars')->count(),
        'pillar_practices' => DB::table('pillar_practices')->count(),
        'post_tags' => DB::table('post_tags')->count(),
        'kabupaten_pillars' => DB::table('kabupaten_pillars')->count(),
    ];

    $this->seed(ContentSeeder::class);

    foreach ($before as $table => $count) {
        expect(DB::table($table)->count())->toBe($count, $table . ' changed on a second run');
    }
});
