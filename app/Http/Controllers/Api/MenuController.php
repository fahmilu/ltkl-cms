<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\MenuBuilder;

class MenuController extends Controller
{
    public function __construct(private readonly MenuBuilder $menus) {}

    /**
     * @OA\Get(
     *     path="/api/menus",
     *     tags={"Menus"},
     *     operationId="getMenus",
     *     summary="Get the menus, grouped by menu group",
     *      @OA\Parameter(
     *          name="group",
     *          in="query",
     *          required=false,
     *          description="Filter one menu group, e.g. main, header, footer. A page set to several groups is listed in each of them.",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Menus listed. Each submenu entry carries a `resource` of `page`, `anchor` or `kabupaten`; a top-level entry whose slug is one of config('menu.kabupaten_children_slugs') — `anggota` / `members` — also lists the active kabupatens below it.",
     *     )
     * )
     */
    public function index()
    {
        return response()->json($this->menus->groups(request('group')));
    }
}
