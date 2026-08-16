<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PillarResource;
use App\Models\Pillar;
use Illuminate\Database\Eloquent\Collection;

/**
 * @OA\Schema(
 *     schema="Pillar",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="number", type="integer", nullable=true, description="Position in the pillar list, the \"Pilar 01\" numbering", example=1),
 *     @OA\Property(property="image", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="technical_term", type="string", nullable=true, example="Multi-stakeholder governance"),
 *     @OA\Property(property="technical_term_id", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="description_id", type="string", nullable=true),
 *     @OA\Property(
 *         property="kabupatens_count",
 *         type="integer",
 *         description="Live count of active kabupatens. Not an editable statistic, so it can never go stale.",
 *         example=9
 *     ),
 *     @OA\Property(
 *         property="statistics",
 *         type="array",
 *         description="English header figures, excluding the kabupaten count",
 *         @OA\Items(ref="#/components/schemas/PillarStatistic")
 *     ),
 *     @OA\Property(
 *         property="statistics_id",
 *         type="array",
 *         description="Indonesian header figures. Maintained separately, so its length may differ from the English list.",
 *         @OA\Items(ref="#/components/schemas/PillarStatistic")
 *     ),
 *     @OA\Property(
 *         property="results",
 *         type="array",
 *         description="English \"Hasil pilar ini\" rows",
 *         @OA\Items(ref="#/components/schemas/PillarResult")
 *     ),
 *     @OA\Property(
 *         property="results_id",
 *         type="array",
 *         description="Indonesian \"Hasil pilar ini\" rows",
 *         @OA\Items(ref="#/components/schemas/PillarResult")
 *     ),
 *     @OA\Property(
 *         property="practices",
 *         type="array",
 *         description="\"Bagaimana ini terlihat di lapangan\" examples. Returned on the detail endpoint only.",
 *         @OA\Items(ref="#/components/schemas/PillarPractice")
 *     ),
 *     @OA\Property(property="sorted_at", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PillarStatistic",
 *     type="object",
 *     @OA\Property(property="value", type="string", nullable=true, example="38"),
 *     @OA\Property(property="label", type="string", nullable=true, example="Lembaga di forum")
 * )
 *
 * @OA\Schema(
 *     schema="PillarResult",
 *     type="object",
 *     @OA\Property(property="value", type="string", nullable=true, example="12rb ha"),
 *     @OA\Property(property="title", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="source", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PillarPractice",
 *     type="object",
 *     description="Both languages sit on one row because the row is anchored to a single kabupaten.",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="since_year", type="integer", nullable=true, example=2019),
 *     @OA\Property(property="title", type="string", nullable=true),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="description_id", type="string", nullable=true),
 *     @OA\Property(property="image", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(
 *         property="kabupaten",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="slug", type="string"),
 *         @OA\Property(property="slug_id", type="string", nullable=true),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="title_id", type="string", nullable=true)
 *     )
 * )
 */
class PillarController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pillars",
     *     tags={"Pillars"},
     *     operationId="getPillarsList",
     *     summary="Get all active pillars",
     *     description="Returns every active pillar ordered by sorted_at. The active filter is always applied and the response is not paginated.",
     *     @OA\Response(
     *         response=200,
     *         description="Pillars listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Pillar"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $results = Pillar::where('is_active', true)
            ->orderBy('sorted_at', 'asc')
            ->get();

        return PillarResource::collection($this->withNumbers($results));
    }

    /**
     * @OA\Get(
     *     path="/api/pillar/{slug}",
     *     tags={"Pillars"},
     *     operationId="getPillarSlug",
     *     summary="Get pillar in slug",
     *     description="Matches either the English or the Indonesian slug. Includes the in-practice examples.",
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
     *         description="Pillar data shown",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Pillar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active pillar matches the slug"
     *     )
     * )
     */
    public function show(string $slug)
    {
        $pillar = Pillar::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_id', $slug);
            })
            ->with('practices.kabupaten')
            ->firstOrFail();

        // Numbering comes from the full ordered list, not from this record alone.
        $position = Pillar::where('is_active', true)
            ->where('sorted_at', '<', $pillar->sorted_at)
            ->count();

        $pillar->setAttribute('number', $position + 1);

        return new PillarResource($pillar);
    }

    /**
     * Stamp each pillar with its position, so "Pilar 01" always agrees with the
     * order the API returns rather than being typed in by hand.
     */
    private function withNumbers(Collection $pillars): Collection
    {
        return $pillars->values()->each(
            fn(Pillar $pillar, int $index) => $pillar->setAttribute('number', $index + 1)
        );
    }
}
