<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;

class CollectionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/collections",
     *     tags={"Collections"},
     *     operationId="getCollectionsList",
     *     summary="Get all collections | to see the `type` please execute all the collections first",
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          required=false,
     *          description="Filter type",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Collection listed",
     *     )
     * )
     */
    public function index()
    {
        return CollectionResource::collection(Collection::when(request('type'), function ($query) {
            return $query->where('type', request('type'));
        })->orderBy('sorted_at', 'asc')->get());
    }

    /**
     * @OA\Get(
     *     path="/api/collection/{slug}",
     *     tags={"Collections"},
     *     operationId="getCollectionSlug",
     *     summary="Get collection in slug",
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
     *         description="Collection data shown",
     *     )
     * )
     */
    public function show($slug)
    {
        return new CollectionResource(Collection::where('slug', $slug)->firstOrFail());
    }
}
