<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Support\MenuBuilder;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function __construct(private readonly MenuBuilder $menus) {}

    /**
     * @OA\Get(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     operationId="getPagesList",
     *     summary="Get all pages",
     *      @OA\Parameter(
     *          name="default",
     *          in="query",
     *          required=false,
     *          description="Filter default page",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *       @OA\Parameter(
     *           name="active",
     *           in="query",
     *           required=false,
     *           description="Filter publish/draft page",
     *           @OA\Schema(
     *               type="boolean"
     *           )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Page listed",
     *     )
     * )
     */
    public function index()
    {
        $pages = Page::query();

        if (request('default') == true) {
            $pages->where('is_default', true);
        }

        $pages->where('is_active', true);

        $results = $pages->get();

        return PageResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/page/{slug}",
     *     tags={"Pages"},
     *     operationId="getPageSlug",
     *     summary="Get page in slug",
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
     *         description="Page data shown",
     *     )
     * )
     */
    public function show(?string $slug = null)
    {
        $data = $slug ?
            Page::where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_id', $slug);
            })->where('is_active', true)->firstOrFail() :
            Page::where('is_default', true)->where('is_active', true)->firstOrFail();
        if (!$data) {
            return response()->json([
                'message' => 'Page not found',
            ], 404);
        }

        return new PageResource($data);
    }

    /**
     * Kept for the frontends already pointing here; /api/menus is the same
     * payload and takes an optional group filter.
     */
    public function navigations()
    {
        return response()->json($this->menus->groups());
    }
}
