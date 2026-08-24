<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Setting defaults
    |--------------------------------------------------------------------------
    |
    | Settings only get a row once an editor saves the page they live on, so a
    | key nobody has touched yet is simply absent from /api/settings. Keys
    | listed here are always published, falling back to the value below, so the
    | frontend can read them without special-casing a fresh environment.
    |
    | Grouped by setting group, matching the `group` column.
    |
    */

    'defaults' => [

        'website' => [
            'footer_cta' => [
                'title' => null,
                'title_id' => null,
                'description' => null,
                'description_id' => null,
                'button_text' => null,
                'button_text_id' => null,
                'button_url' => null,
            ],
        ],

    ],

];
