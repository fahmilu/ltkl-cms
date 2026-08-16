<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Place name lookup for the map picker, backed by OpenStreetMap Nominatim.
 */
class LocationSearch
{
    /**
     * Search for a place and return the matches as a list of coordinates.
     *
     * A failed lookup returns an empty list rather than throwing: the editor can
     * always fall back to clicking the map or typing coordinates by hand.
     *
     * @return array<int, array{label: string, latitude: float, longitude: float}>
     */
    public function search(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 3) {
            return [];
        }

        $cacheKey = 'geocoding:' . md5(mb_strtolower($term));

        return Cache::remember(
            $cacheKey,
            (int) config('geocoding.cache_ttl'),
            fn(): array => $this->fetch($term)
        );
    }

    /**
     * @return array<int, array{label: string, latitude: float, longitude: float}>
     */
    private function fetch(string $term): array
    {
        try {
            $response = Http::timeout((int) config('geocoding.timeout'))
                // Nominatim's usage policy requires an identifying User-Agent.
                ->withHeaders(['User-Agent' => (string) config('geocoding.user_agent')])
                ->get((string) config('geocoding.url'), array_filter([
                    'q' => $term,
                    'format' => 'jsonv2',
                    'addressdetails' => 0,
                    'limit' => (int) config('geocoding.limit'),
                    'countrycodes' => config('geocoding.country_codes') ?: null,
                ]));

            if (! $response->successful()) {
                return [];
            }

            return $this->transform($response->json() ?? []);
        } catch (Throwable $exception) {
            Log::warning('Location search failed.', [
                'term' => $term,
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<int, array{label: string, latitude: float, longitude: float}>
     */
    private function transform(array $results): array
    {
        $places = [];

        foreach ($results as $result) {
            if (! isset($result['lat'], $result['lon'])) {
                continue;
            }

            $places[] = [
                'label' => $result['display_name'] ?? $result['name'] ?? 'Unknown place',
                'latitude' => round((float) $result['lat'], 7),
                'longitude' => round((float) $result['lon'], 7),
            ];
        }

        return $places;
    }
}
