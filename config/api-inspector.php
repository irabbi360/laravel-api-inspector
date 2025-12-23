<?php

// config for Irabbi360/LaravelApiInspector
return [
    'enabled' => (bool) config('api-inspector.enabled', true),

    'output' => [
        'openapi' => (bool) config('api-inspector.output.openapi', true),
        'postman' => (bool) config('api-inspector.output.postman', true),
        'html' => (bool) config('api-inspector.output.html', true),
    ],

    'save_responses' => (bool) config('api-inspector.save_responses', true),

    'save_responses_driver' => config('api-inspector.save_responses_driver', 'cache'), // 'cache' or 'json'

    'middleware_capture' => (bool) config('api-inspector.middleware_capture', true),

    'auth' => [
        'type' => config('api-inspector.auth.type', 'bearer'),
        'header' => config('api-inspector.auth.header', 'Authorization'),
    ],

    'response_path' => config('api-inspector.response_path', storage_path('api-docs')),

    'only_route_uri_start_with' => config('api-inspector.only_route_uri_start_with', ''),
];
