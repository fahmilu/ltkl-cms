<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fold the flat footer_cta_* settings into a single footer_cta setting.
 *
 * The API publishes one row per setting, so seven keys meant seven entries the
 * frontend had to stitch back together. They now live in one row, which the
 * settings page writes as a nested field group.
 */
return new class extends Migration {
    /**
     * Sub-key on the grouped setting, keyed by the flat setting it replaces.
     *
     * @var array<string, string>
     */
    private const KEYS = [
        'footer_cta_title' => 'title',
        'footer_cta_title_id' => 'title_id',
        'footer_cta_description' => 'description',
        'footer_cta_description_id' => 'description_id',
        'footer_cta_button_text' => 'button_text',
        'footer_cta_button_text_id' => 'button_text_id',
        'footer_cta_button_url' => 'button_url',
    ];

    public function up(): void
    {
        $table = config('db-config.table_name', 'settings');

        $rows = DB::table($table)
            ->where('group', 'website')
            ->whereIn('key', array_keys(self::KEYS))
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[self::KEYS[$row->key]] = json_decode($row->settings, true);
        }

        $this->write($table, 'footer_cta', $grouped);

        DB::table($table)
            ->where('group', 'website')
            ->whereIn('key', array_keys(self::KEYS))
            ->delete();

        $this->forget(array_keys(self::KEYS));
    }

    public function down(): void
    {
        $table = config('db-config.table_name', 'settings');

        $row = DB::table($table)
            ->where('group', 'website')
            ->where('key', 'footer_cta')
            ->first();

        if (! $row) {
            return;
        }

        $grouped = json_decode($row->settings, true) ?: [];

        foreach (self::KEYS as $flat => $subKey) {
            if (array_key_exists($subKey, $grouped)) {
                $this->write($table, $flat, $grouped[$subKey]);
            }
        }

        DB::table($table)
            ->where('group', 'website')
            ->where('key', 'footer_cta')
            ->delete();

        $this->forget(['footer_cta']);
    }

    private function write(string $table, string $key, mixed $value): void
    {
        DB::table($table)->updateOrInsert(
            ['group' => 'website', 'key' => $key],
            ['settings' => json_encode($value), 'updated_at' => now(), 'created_at' => now()],
        );

        $this->forget([$key]);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function forget(array $keys): void
    {
        $prefix = config('db-config.cache.prefix', 'db-config');

        foreach ($keys as $key) {
            Cache::forget("{$prefix}.website.{$key}");
        }
    }
};
