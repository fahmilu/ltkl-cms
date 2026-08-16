<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="type", type="string", enum={"article", "video", "event", "library", "media_coverage"}),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="image", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="components", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="components_id", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="is_featured", type="boolean"),
 *     @OA\Property(property="is_external_url", type="boolean"),
 *     @OA\Property(property="external_type", type="string", nullable=true),
 *     @OA\Property(property="external_url", type="string", nullable=true),
 *     @OA\Property(property="external_file", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="post_tags", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="post_topics", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="post_kabupatens", type="array", @OA\Items(type="object")),
 *     @OA\Property(
 *         property="type_data",
 *         type="object",
 *         description="Type specific payload. The keys present depend on the `type` field, one of the schemas below.",
 *         oneOf={
 *             @OA\Schema(ref="#/components/schemas/PostTypeDataArticle"),
 *             @OA\Schema(ref="#/components/schemas/PostTypeDataVideo"),
 *             @OA\Schema(ref="#/components/schemas/PostTypeDataEvent"),
 *             @OA\Schema(ref="#/components/schemas/PostTypeDataLibrary"),
 *             @OA\Schema(ref="#/components/schemas/PostTypeDataMediaCoverage")
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PostTypeDataArticle",
 *     type="object",
 *     title="type_data (article)",
 *     @OA\Property(property="author", type="string", nullable=true),
 *     @OA\Property(property="read_time", type="integer", nullable=true, description="Minutes. Falls back to an estimate from the content when not set in the CMS.")
 * )
 *
 * @OA\Schema(
 *     schema="PostTypeDataVideo",
 *     type="object",
 *     title="type_data (video)",
 *     @OA\Property(property="video_url", type="string", nullable=true, description="URL as entered in the CMS"),
 *     @OA\Property(property="embed_url", type="string", nullable=true, description="Player URL derived from video_url, ready for an iframe"),
 *     @OA\Property(property="duration", type="string", nullable=true, example="9:12"),
 *     @OA\Property(property="subtitles", type="array", @OA\Items(type="string", enum={"id", "en"}))
 * )
 *
 * @OA\Schema(
 *     schema="PostTypeDataEvent",
 *     type="object",
 *     title="type_data (event)",
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="is_multi_day", type="boolean"),
 *     @OA\Property(property="start_time", type="string", nullable=true, example="09:00"),
 *     @OA\Property(property="end_time", type="string", nullable=true, example="21:00"),
 *     @OA\Property(property="timezone", type="string", nullable=true, enum={"WIB", "WITA", "WIT"}),
 *     @OA\Property(property="register_url", type="string", nullable=true),
 *     @OA\Property(property="is_public", type="boolean"),
 *     @OA\Property(property="is_registration_open", type="boolean"),
 *     @OA\Property(property="location", type="string", nullable=true),
 *     @OA\Property(property="location_id", type="string", nullable=true),
 *     @OA\Property(property="cost", type="string", nullable=true),
 *     @OA\Property(property="cost_id", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PostTypeDataLibrary",
 *     type="object",
 *     title="type_data (library)",
 *     @OA\Property(property="pages", type="integer", nullable=true),
 *     @OA\Property(property="license", type="string", nullable=true, example="CC BY 4.0"),
 *     @OA\Property(property="cover", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="file", type="string", nullable=true, description="Full URL of the English document"),
 *     @OA\Property(property="file_meta", ref="#/components/schemas/PostFileMeta"),
 *     @OA\Property(property="file_id", type="string", nullable=true, description="Full URL of the Indonesian document"),
 *     @OA\Property(property="file_id_meta", ref="#/components/schemas/PostFileMeta"),
 *     @OA\Property(property="access_note", type="string", nullable=true),
 *     @OA\Property(property="access_note_id", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PostTypeDataMediaCoverage",
 *     type="object",
 *     title="type_data (media_coverage)",
 *     @OA\Property(property="publisher_name", type="string", nullable=true),
 *     @OA\Property(property="publisher_logo", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="journalist", type="string", nullable=true),
 *     @OA\Property(property="source_published_at", type="string", format="date", nullable=true),
 *     @OA\Property(property="source_url", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PostFileMeta",
 *     type="object",
 *     nullable=true,
 *     description="Null when no file is uploaded or the file is missing from disk.",
 *     @OA\Property(property="extension", type="string", example="PDF"),
 *     @OA\Property(property="size", type="integer", description="Bytes"),
 *     @OA\Property(property="size_label", type="string", example="4.2 MB")
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     tags={"Posts"},
     *     operationId="getPostsList",
     *     summary="Get all active posts",
     *     description="Returns only active (published) posts. The active filter is always applied.",
     *       @OA\Parameter(
     *           name="featured",
     *           in="query",
     *           required=false,
     *           description="Filter featured post",
     *           @OA\Schema(
     *               type="boolean"
     *           )
     *       ),
     *        @OA\Parameter(
     *            name="search",
     *            in="query",
     *            required=false,
     *            description="Search post by title",
     *            @OA\Schema(
     *                type="string"
     *            )
     *        ),
     *        @OA\Parameter(
     *            name="sort",
     *            in="query",
     *            required=false,
     *            description="Sort column (id, title, published_at, created_at, updated_at, is_featured, is_active)",
     *            @OA\Schema(
     *                type="string",
     *                default="published_at"
     *            )
     *        ),
     *        @OA\Parameter(
     *            name="order",
     *            in="query",
     *            required=false,
     *            description="Sort order (asc or desc)",
     *            @OA\Schema(
     *                type="string",
     *                enum={"asc", "desc"},
     *                default="desc"
     *            )
     *        ),
     *        @OA\Parameter(
     *            name="type",
     *            in="query",
     *            required=false,
     *            description="Filter by post type (article, video, event, library, media_coverage)",
     *            @OA\Schema(
     *                type="array",
     *                @OA\Items(
     *                    type="string",
     *                    enum={"article", "video", "event", "library", "media_coverage"}
     *                )
     *            ),
     *            style="form",
     *            explode=true
     *        ),
     *        @OA\Parameter(
     *            name="post_tags",
     *            in="query",
     *            required=false,
     *            description="Filter by tag IDs (comma-separated or array)",
     *            @OA\Schema(
     *                type="array",
     *                @OA\Items(
     *                    type="integer"
     *                )
     *            ),
     *            style="form",
     *            explode=true
     *        ),
     *        @OA\Parameter(
     *            name="post_topics",
     *            in="query",
     *            required=false,
     *            description="Filter by topic IDs (comma-separated or array)",
     *            @OA\Schema(
     *                type="array",
     *                @OA\Items(
     *                    type="integer"
     *                )
     *            ),
     *            style="form",
     *            explode=true
     *        ),
     *        @OA\Parameter(
     *            name="post_kabupatens",
     *            in="query",
     *            required=false,
     *            description="Filter by kabupaten IDs (comma-separated or array)",
     *            @OA\Schema(
     *                type="array",
     *                @OA\Items(
     *                    type="integer"
     *                )
     *            ),
     *            style="form",
     *            explode=true
     *        ),
     *        @OA\Parameter(
     *            name="page",
     *            in="query",
     *            required=false,
     *            description="Page number",
     *            @OA\Schema(
     *                type="integer",
     *                default=1
     *            )
     *        ),
     *        @OA\Parameter(
     *            name="per_page",
     *            in="query",
     *            required=false,
     *            description="Number of items per page",
     *            @OA\Schema(
     *                type="integer",
     *                default=15,
     *                minimum=1,
     *                maximum=100
     *            )
     *        ),
     *     @OA\Response(
     *         response=200,
     *          description="Paginated list of posts",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Post")),
     *              @OA\Property(
     *                  property="links",
     *                  type="object",
     *                  @OA\Property(property="first", type="string", nullable=true),
     *                  @OA\Property(property="last", type="string", nullable=true),
     *                  @OA\Property(property="prev", type="string", nullable=true),
     *                  @OA\Property(property="next", type="string", nullable=true)
     *              ),
     *              @OA\Property(
     *                  property="meta",
     *                  type="object",
     *                  @OA\Property(property="current_page", type="integer"),
     *                  @OA\Property(property="last_page", type="integer"),
     *                  @OA\Property(property="per_page", type="integer"),
     *                  @OA\Property(property="total", type="integer"),
     *                  @OA\Property(property="from", type="integer", nullable=true),
     *                  @OA\Property(property="to", type="integer", nullable=true),
     *                  @OA\Property(property="path", type="string")
     *              )
     *          )
     *     )
     * )
     */
    public function index()
    {
        $perPage = request('per_page', 15);
        $perPage = min(max((int) $perPage, 1), 100); // Limit between 1 and 100

        $results = Post::filter(request()->all())
            ->with('post_tags', 'post_topics', 'post_kabupatens')
            ->paginateFilter($perPage);

        return PostResource::collection($results);
    }


    /**
     * @OA\Get(
     *     path="/api/post/{slug}",
     *     tags={"Posts"},
     *     operationId="getPostSlug",
     *     summary="Get post in slug",
     *      @OA\Parameter(
     *          name="slug",
     *          in="path",
     *          required=true,
     *          description="Slug",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Post data shown",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No post matches the slug"
     *     )
     * )
     */
    public function show(string $slug)
    {
        return new PostResource(
            Post::where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_id', $slug);
            })->with('post_tags', 'post_topics', 'post_kabupatens')->firstOrFail()
        );
    }
}
