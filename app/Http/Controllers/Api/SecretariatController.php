<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SecretariatResource;
use App\Models\Secretariat;

/**
 * @OA\Schema(
 *     schema="Secretariat",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="image", type="string", nullable=true, description="Full URL"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="role", type="string", nullable=true),
 *     @OA\Property(property="role_id", type="string", nullable=true),
 *     @OA\Property(property="level", type="integer", enum={1, 2, 3}, description="1 = Top Level, 2 = Manager, 3 = Staff"),
 *     @OA\Property(property="level_label", type="string", enum={"Top Level", "Manager", "Staff"}),
 *     @OA\Property(property="sorted_at", type="integer", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SecretariatController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/secretariats",
     *     tags={"Secretariat"},
     *     operationId="getSecretariatsList",
     *     summary="Get all published secretariat members",
     *     description="Returns every published secretariat member, ordered by sorted_at. Not paginated.",
     *     @OA\Response(
     *         response=200,
     *         description="Secretariat members listed",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Secretariat"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $results = Secretariat::where('is_active', true)
            ->orderBy('sorted_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return SecretariatResource::collection($results);
    }
}
