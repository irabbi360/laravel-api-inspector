<?php

// config for Irabbi360/LaravelApiInspector
return [
    'enabled' => env('API_INSPECTOR_ENABLED', true),

    'output' => [
        'openapi' => true,
        'postman' => true,
        'html' => true,
    ],

    'save_responses' => true,

    'middleware_capture' => true,

    'auth' => [
        'type' => 'bearer',
        'header' => 'Authorization'
    ],
];

