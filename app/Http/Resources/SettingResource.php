<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SettingResource extends JsonResource
{
    /**
     * Settings holding an upload path, served as a full URL.
     *
     * @var array<int, string>
     */
    private const UPLOAD_KEYS = [
        'favicon',
        'main_logo',
        'footer_logo',
        'meta_image',
    ];

    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $value = json_decode($this->settings);

        if (in_array($this->key, self::UPLOAD_KEYS, true)) {
            // An empty path would otherwise come back as the bare site URL.
            $value = filled($value) ? Storage::disk('public')->url($value) : null;
        }

        return [
            'id' => $this->id,
            'group' => $this->group,
            'key' => $this->key,
            'settings' => $value,
        ];
    }
}
