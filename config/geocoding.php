<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geocoding provider
    |--------------------------------------------------------------------------
    |
    | Place name lookup for the map picker. Defaults to OpenStreetMap Nominatim,
    | which needs no API key but does ask for an identifying User-Agent and a
    | maximum of one request per second. Results are cached to stay well inside
    | that limit. See https://operations.osmfoundation.org/policies/nominatim/
    |
    */

    'url' => env('GEOCODING_URL', 'https://nominatim.openstreetmap.org/search'),

    'user_agent' => env('GEOCODING_USER_AGENT', config('app.name') . ' CMS'),

    'timeout' => (int) env('GEOCODING_TIMEOUT', 8),

    /*
    | Restrict results to a country, as an ISO 3166-1 alpha-2 code. Empty
    | searches worldwide.
    */
    'country_codes' => env('GEOCODING_COUNTRY_CODES', 'id'),

    'limit' => (int) env('GEOCODING_LIMIT', 8),

    'cache_ttl' => (int) env('GEOCODING_CACHE_TTL', 86400),

];
