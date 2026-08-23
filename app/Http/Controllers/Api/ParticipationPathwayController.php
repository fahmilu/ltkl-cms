<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParticipationPathwayResource;
use App\Models\ParticipationPathway;

/**
 * @OA\Schema(
 *     schema="ParticipationPathway",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="description_id", type="string", nullable=true),
 *     @OA\Property(property="sorted_at", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ParticipationPathwayController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/participation-pathways",
     *     tags={"Participation Pathways"},
     *     operationId="getParticipationPathwaysList",
     *     summary="Get all active participation pathways",
     *     description="Returns every active pathway ordered by sorted_at. The active filter is always applied and the response is not paginated.",
     *     @OA\Response(
     *         response=200,
     *         description="Participation pathways listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ParticipationPathway"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $results = ParticipationPathway::where('is_active', true)
            ->orderBy('sorted_at', 'asc')
            ->get();

        return ParticipationPathwayResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/participation-pathway/{slug}",
     *     tags={"Participation Pathways"},
     *     operationId="getParticipationPathwaySlug",
     *     summary="Get participation pathway in slug",
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
     *         description="Participation pathway data shown",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ParticipationPathway")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active participation pathway matches the slug"
     *     )
     * )
     */
    public function show(string $slug)
    {
        return new ParticipationPathwayResource(
            ParticipationPathway::where('is_active', true)
                ->where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->orWhere('slug_id', $slug);
                })
                ->firstOrFail()
        );
    }
}
