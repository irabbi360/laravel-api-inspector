<?php

// config for Irabbi360/LaravelApiInspector
return [
    // changes doc title
    'title' => 'LAPI - Laravel API Inspector',
    'enabled' => true,
    /*
    * Route where request docs will be served from laravel app.
    * localhost:8080/api-docs
    */
    'route_prefix' => 'api-docs',
    
    /*
    |--------------------------------------------------------------------------
    | API Inspector Assets Path
    |--------------------------------------------------------------------------
    | The path to the API Inspector assets.
    |
    */

    'assets_path' => 'vendor/api-inspector',

    'output' => [
        'openapi' => true,
        'postman' => true,
        'html' => true,
    ],

    'save_responses' => true,

    'save_responses_driver' => 'json', // 'cache' or 'json'

    'middleware_capture' => true,

    'auth' => [
        'type' => 'bearer',
        'header' => 'Authorization',
    ],

    'response_path' => 'api-docs', // Subfolder name

    'storage_path' => 'storage', // 'storage' or 'local'

    // Use only routes where ->uri start with next string Using Str::startWith( . e.g. - /api/mobile
    'only_route_uri_start_with' => 'api/',
];
