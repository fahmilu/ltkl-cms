<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/settings",
     *     tags={"Settings"},
     *     operationId="getSettingsList",
     *     summary="Get all settings | to see the `group` and `key` please execute all the settings first",
     *      @OA\Parameter(
     *          name="group",
     *          in="query",
     *          required=false,
     *          description="Filter group",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *       @OA\Parameter(
     *           name="key",
     *           in="query",
     *           required=false,
     *           description="Filter key",
     *           @OA\Schema(
     *               type="string"
     *           )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Collection listed",
     *     )
     * )
     */
    public function index()
    {

        $setting = Setting::when(request('group'), function ($query) {
            return $query->where('group', request('group'));
        })->when(request('key'), function ($query) {
            return $query->where('key', request('key'));
        })->get();

        return SettingResource::collection($setting);
    }
}
