<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;

Route::middleware('web')->group(function () {
    Route::get(config('api-inspector.route_prefix', '/api-docs'), [ApiInspectorController::class, 'index'])->name('api-inspector.docs');
    Route::get('/api-docs/fetch', [ApiInspectorController::class, 'fetchApiInfo'])->name('api-inspector.docs.fetch');
    Route::get('/api-docs/postman', [ApiInspectorController::class, 'postman'])->name('api-inspector.docs.postman');
    Route::get('/api-docs/openapi', [ApiInspectorController::class, 'openapi'])->name('api-inspector.docs.openapi');
    Route::get('/api-docs/realtime', [ApiInspectorController::class, 'realtimeDocs'])->name('api-inspector.docs.realtime');
});

Route::middleware('api')->group(function () {
    Route::post('/api/test-request', [ApiInspectorController::class, 'testRequest'])->name('api-inspector.test-request');
    Route::post('/api-docs/save-response', [ApiInspectorController::class, 'saveResponse'])->name('api-inspector.save-response');
    Route::get('/api-docs/saved-responses', [ApiInspectorController::class, 'savedResponses'])->name('api-inspector.saved-responses');
});
