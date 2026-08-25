<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KabupatenMapResource;
use App\Http\Resources\KabupatenResource;
use App\Models\Kabupaten;

/**
 * @OA\Schema(
 *     schema="Kabupaten",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="image", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="title", type="string", description="English title"),
 *     @OA\Property(property="title_id", type="string", nullable=true, description="Indonesian title"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", nullable=true, description="English role", example="Founding member"),
 *     @OA\Property(property="role_id", type="string", nullable=true, description="Indonesian role", example="Anggota pendiri"),
 *     @OA\Property(property="content", type="string", nullable=true),
 *     @OA\Property(property="content_id", type="string", nullable=true),
 *     @OA\Property(property="forest_cover_ha", type="number", nullable=true, description="Tutupan Hutan, in hectares", example=312000),
 *     @OA\Property(property="protected_area_ha", type="number", nullable=true, description="Kawasan Lindung, in hectares", example=57000),
 *     @OA\Property(property="social_forestry_tora_ha", type="number", nullable=true, description="Perhutanan Sosial & TORA, in hectares", example=21000),
 *     @OA\Property(property="area_km2", type="number", nullable=true, description="Luas Wilayah, in square kilometres", example=8556),
 *     @OA\Property(property="city", type="string", nullable=true, description="Kota", example="Siak"),
 *     @OA\Property(property="province", type="string", nullable=true, description="Provinsi", example="Riau"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=0.8118),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=101.8),
 *     @OA\Property(property="is_founding_member", type="boolean", description="Anggota Pendiri"),
 *     @OA\Property(property="joined_year", type="integer", nullable=true, example=2017),
 *     @OA\Property(property="sorted_at", type="integer", nullable=true),
 *     @OA\Property(
 *         property="pillars",
 *         type="array",
 *         description="\"Pilar Terkait\". Slim references — the full pillar lives at /api/pillar/{slug}.",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="slug", type="string"),
 *             @OA\Property(property="slug_id", type="string", nullable=true),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="title_id", type="string", nullable=true),
 *             @OA\Property(property="technical_term", type="string", nullable=true),
 *             @OA\Property(property="technical_term_id", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Property(
 *         property="commodities",
 *         type="array",
 *         description="English commodity list",
 *         @OA\Items(ref="#/components/schemas/KabupatenCommodity")
 *     ),
 *     @OA\Property(
 *         property="commodities_id",
 *         type="array",
 *         description="Indonesian commodity list. Maintained separately, so its length may differ from the English list.",
 *         @OA\Items(ref="#/components/schemas/KabupatenCommodity")
 *     ),
 *     @OA\Property(
 *         property="achievements",
 *         type="array",
 *         description="English achievement list",
 *         @OA\Items(ref="#/components/schemas/KabupatenAchievement")
 *     ),
 *     @OA\Property(
 *         property="achievements_id",
 *         type="array",
 *         description="Indonesian achievement list. Maintained separately, so its length may differ from the English list.",
 *         @OA\Items(ref="#/components/schemas/KabupatenAchievement")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="KabupatenMapPoint",
 *     type="object",
 *     description="Slim map pin. Only kabupatens with both coordinates set are returned.",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="role", type="string", nullable=true),
 *     @OA\Property(property="role_id", type="string", nullable=true),
 *     @OA\Property(property="city", type="string", nullable=true),
 *     @OA\Property(property="province", type="string", nullable=true),
 *     @OA\Property(property="is_founding_member", type="boolean"),
 *     @OA\Property(property="latitude", type="number", format="float", example=0.8118),
 *     @OA\Property(property="longitude", type="number", format="float", example=101.8)
 * )
 *
 * @OA\Schema(
 *     schema="KabupatenCommodity",
 *     type="object",
 *     @OA\Property(property="name", type="string", nullable=true, example="Nanas gambut"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="KabupatenAchievement",
 *     type="object",
 *     description="One impact row. The keys present depend on the type: data carries value, title, description and source; quote carries quote, name and image; text carries title and description.",
 *     @OA\Property(property="type", type="string", enum={"data", "quote", "text"}, example="data"),
 *     @OA\Property(property="value", type="string", nullable=true, example="12rb ha", description="data only"),
 *     @OA\Property(property="title", type="string", nullable=true, description="data and text"),
 *     @OA\Property(property="description", type="string", nullable=true, description="data and text"),
 *     @OA\Property(property="source", type="string", nullable=true, example="Sumber: SK Bupati 2024 · diperbarui Mar 2026", description="data only"),
 *     @OA\Property(property="quote", type="string", nullable=true, description="quote only"),
 *     @OA\Property(property="name", type="string", nullable=true, description="quote only"),
 *     @OA\Property(property="image", type="string", nullable=true, description="quote only, full URL")
 * )
 */
class KabupatenController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/kabupatens",
     *     tags={"Kabupatens"},
     *     operationId="getKabupatensList",
     *     summary="Get all active kabupatens",
     *     description="Returns every active kabupaten ordered by sorted_at. The active filter is always applied and the response is not paginated.",
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          required=false,
     *          description="Search by title, in either language",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Kabupatens listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Kabupaten"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $results = Kabupaten::where('is_active', true)
            ->when(request('search'), function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('title_id', 'LIKE', '%' . $search . '%');
                });
            })
            ->with('pillars')
            ->orderBy('sorted_at', 'asc')
            ->get();

        return KabupatenResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/kabupatens/map",
     *     tags={"Kabupatens"},
     *     operationId="getKabupatensMap",
     *     summary="Get map pins for all active kabupatens",
     *     description="Slim payload for the member map. Kabupatens missing either coordinate are skipped, so every entry is drawable.",
     *     @OA\Response(
     *         response=200,
     *         description="Map pins listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/KabupatenMapPoint"))
     *         )
     *     )
     * )
     */
    public function map()
    {
        $results = Kabupaten::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('sorted_at', 'asc')
            ->get();

        return KabupatenMapResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/kabupaten/{slug}",
     *     tags={"Kabupatens"},
     *     operationId="getKabupatenSlug",
     *     summary="Get kabupaten in slug",
     *     description="Matches either the English or the Indonesian slug.",
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
     *         description="Kabupaten data shown",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Kabupaten")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active kabupaten matches the slug"
     *     )
     * )
     */
    public function show(string $slug)
    {
        return new KabupatenResource(
            Kabupaten::where('is_active', true)
                ->where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->orWhere('slug_id', $slug);
                })
                ->with('pillars')
                ->firstOrFail()
        );
    }
}
