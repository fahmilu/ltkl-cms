<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Settings the admin panel stores but the API never publishes, per group.
     *
     * They are only read server side — publishing them would hand the value to
     * anyone hitting /api/settings.
     *
     * @var array<string, array<int, string>>
     */
    private const PRIVATE_KEYS = [
        'website' => [
            'join_us_email',
        ],
    ];

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

        $setting = $setting
            ->concat($this->missingDefaults($setting))
            ->reject(fn (Setting $item): bool => $this->isPrivate($item->group, $item->key))
            ->values();

        return SettingResource::collection($setting);
    }

    /**
     * Whether a setting is kept out of the API response.
     */
    private function isPrivate(?string $group, ?string $key): bool
    {
        return in_array($key, self::PRIVATE_KEYS[$group] ?? [], true);
    }

    /**
     * Settings that have no row yet, as unsaved records carrying their default.
     *
     * A key only gets a row once an editor saves the page it lives on, so
     * without this a fresh environment simply omits it. The filters on the
     * request are applied here too, so a default never widens the response.
     *
     * @param  \Illuminate\Support\Collection<int, Setting>  $existing
     * @return \Illuminate\Support\Collection<int, Setting>
     */
    private function missingDefaults($existing)
    {
        $group = request('group');
        $key = request('key');

        $defaults = collect(config('settings.defaults', []))
            ->when($group, fn($groups) => $groups->only([$group]));

        $stored = $existing->groupBy('group')->map->pluck('key');

        return $defaults->flatMap(function (array $keys, string $groupName) use ($key, $stored) {
            return collect($keys)
                ->when($key, fn($values) => $values->only([$key]))
                ->reject(fn($value, string $keyName) => $stored->get($groupName, collect())->contains($keyName))
                ->map(function ($value, string $keyName) use ($groupName): Setting {
                    // Assigned rather than mass filled: the model guards
                    // everything, since only the settings package writes rows.
                    $setting = new Setting;
                    $setting->group = $groupName;
                    $setting->key = $keyName;
                    $setting->settings = json_encode($value);

                    return $setting;
                })
                ->values();
        })->values();
    }
}
