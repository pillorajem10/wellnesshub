<?php

return [

    'host' => env('TYPESENSE_HOST', 'localhost'),

    'port' => env('TYPESENSE_PORT', 8108),

    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),

    'api_key' => env('TYPESENSE_API_KEY', ''),

    'search_only_api_key' => env('TYPESENSE_SEARCH_ONLY_API_KEY', ''),

    'collections' => [
        'protocols' => 'protocols',
        'threads' => 'threads',
    ],

];
