<?php

// config for Irabbi360/LaravelApiInspector
return [
    'enabled' => true,

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

    'only_route_uri_start_with' => 'api/',
];
