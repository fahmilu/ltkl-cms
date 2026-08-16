<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
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

    public function navigations()
    {
        // Get all active menu pages grouped by menu_group
        $pages = Page::where('menu_is_active', true)
            ->whereNotNull('menu_group')
            ->with(['subpages' => function ($query) {
                $query->where('menu_is_active', true)
                    ->orderBy('sorted_at', 'asc');
            }])
            ->orderBy('sorted_at', 'asc')
            ->get();

        // Group by menu_group
        $grouped = $pages->groupBy('menu_group');

        $result = [];
        foreach ($grouped as $menuGroup => $groupPages) {
            // Get only top-level pages (no parent) for this group
            $topLevelPages = $groupPages->where('menu_parent_id', null);

            $navigation = [];
            foreach ($topLevelPages as $page) {
                $navItem = [
                    'is_external' => (bool) $page->menu_is_external,
                    'title' => $page->title,
                    'title_id' => $page->title_id ?? '',
                    'slug' => $page->slug,
                    'slug_id' => $page->slug_id ?? '',
                    'url' => $page->menu_url ?? '',
                    'url_target' => $page->menu_url_target ?? '',
                    'subs' => [],
                ];

                // Add subpages
                foreach ($page->subpages as $subpage) {
                    $navItem['subs'][] = [
                        'is_external' => (bool) $subpage->menu_is_external,
                        'title' => $subpage->title,
                        'title_id' => $subpage->title_id ?? '',
                        'slug' => $subpage->slug,
                        'slug_id' => $subpage->slug_id ?? '',
                        'url' => $subpage->menu_url ?? '',
                        'url_target' => $subpage->menu_url_target ?? '',
                    ];
                }

                $navigation[] = $navItem;
            }

            if (!empty($navigation)) {
                $result[] = [
                    'group' => $menuGroup,
                    'navigation' => $navigation,
                ];
            }
        }

        return response()->json($result);
    }
}
