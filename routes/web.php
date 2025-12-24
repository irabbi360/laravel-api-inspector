<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;
use Irabbi360\LaravelApiInspector\Http\Middleware\ApiInspectorMiddleware;

// Get the configured route path
$routePath = config('api-inspector.route_path', 'api-docs');

Route::middleware(['web', ApiInspectorMiddleware::class])->group(function () use ($routePath) {
    // API Inspector SPA routes - dynamic based on config
    Route::get("/{$routePath}/{view?}", [ApiInspectorController::class, 'index'])
        ->where('view', '(.*)')
        ->name('api-inspector.index');
});
