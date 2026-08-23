<?php

namespace App\Http\Controllers\Api;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobOpportunityResource;
use App\Models\JobOpportunity;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="JobOpportunity",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="status", type="string", enum={"open", "closed"}),
 *     @OA\Property(property="is_open", type="boolean"),
 *     @OA\Property(property="employment_type", type="string", nullable=true, enum={"full_time", "part_time", "contract", "consultant", "internship", "volunteer"}),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="title_id", type="string", nullable=true),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="slug_id", type="string", nullable=true),
 *     @OA\Property(property="location", type="string", nullable=true),
 *     @OA\Property(property="location_id", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="description_id", type="string", nullable=true),
 *     @OA\Property(property="how_to_apply", type="string", nullable=true),
 *     @OA\Property(property="how_to_apply_id", type="string", nullable=true),
 *     @OA\Property(property="contact_email", type="string", nullable=true),
 *     @OA\Property(property="apply_url", type="string", nullable=true),
 *     @OA\Property(property="attachment", type="string", nullable=true, description="URL of the terms of reference file"),
 *     @OA\Property(property="posted_at", type="string", format="date", nullable=true),
 *     @OA\Property(property="deadline_at", type="string", format="date", nullable=true),
 *     @OA\Property(property="sorted_at", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class JobOpportunityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/job-opportunities",
     *     tags={"Job Opportunities"},
     *     operationId="getJobOpportunitiesList",
     *     summary="Get all published job opportunities",
     *     description="Returns every published vacancy, newest posting first. Closed vacancies are listed too, so they stay readable; filter them out with status.",
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          required=false,
     *          description="Keep only open or only closed vacancies",
     *          @OA\Schema(
     *              type="string",
     *              enum={"open", "closed"}
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Job opportunities listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/JobOpportunity"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = JobOpportunity::where('is_active', true);

        // An unknown status is no filter at all, so a typo lists everything
        // rather than an empty page.
        $status = JobStatus::tryFrom((string) $request->query('status'));

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        $results = $query->orderBy('sorted_at', 'asc')
            ->orderByDesc('posted_at')
            ->orderByDesc('id')
            ->get();

        return JobOpportunityResource::collection($results);
    }

    /**
     * @OA\Get(
     *     path="/api/job-opportunity/{slug}",
     *     tags={"Job Opportunities"},
     *     operationId="getJobOpportunitySlug",
     *     summary="Get job opportunity in slug",
     *     description="Matches either the English or the Indonesian slug. A closed vacancy is still served, with status closed.",
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
     *         description="Job opportunity data shown",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/JobOpportunity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No published job opportunity matches the slug"
     *     )
     * )
     */
    public function show(string $slug)
    {
        return new JobOpportunityResource(
            JobOpportunity::where('is_active', true)
                ->where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                        ->orWhere('slug_id', $slug);
                })
                ->firstOrFail()
        );
    }
}
