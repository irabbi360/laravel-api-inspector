<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;

Route::middleware('web')->group(function () {
    // API Inspector SPA routes - more specific first
    Route::get('/api-docs/{view?}', [ApiInspectorController::class, 'index'])
        ->where('view', '(.*)')
        ->name('api-inspector.index');
});
