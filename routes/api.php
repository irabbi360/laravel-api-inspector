<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;
use Irabbi360\LaravelApiInspector\Http\Controllers\DashboardController;
use Irabbi360\LaravelApiInspector\Http\Middleware\ApiInspectorMiddleware;

Route::middleware(['api', ApiInspectorMiddleware::class])->prefix('api')->group(function () {
    Route::get('api-inspector-docs', [ApiInspectorController::class, 'fetchApiInfo'])->name('api-inspector.docs.fetch');
    Route::get('api-inspector-docs/postman', [ApiInspectorController::class, 'postman'])->name('api-inspector.docs.postman');
    Route::get('api-inspector-docs/openapi', [ApiInspectorController::class, 'openapi'])->name('api-inspector.docs.openapi');
    Route::get('api-inspector-docs/realtime', [ApiInspectorController::class, 'realtimeDocs'])->name('api-inspector.docs.realtime');
    Route::post('api-inspector/test-request', [ApiInspectorController::class, 'testRequest'])->name('api-inspector.test-request');
    Route::post('api-inspector-docs/save-response', [ApiInspectorController::class, 'saveResponse'])->name('api-inspector.save-response');
    Route::get('api-inspector-docs/get-saved-responses', [ApiInspectorController::class, 'savedResponses'])->name('api-inspector.saved-responses');
    Route::delete('api-inspector-docs/delete-response', [ApiInspectorController::class, 'deleteResponse'])->name('api-inspector.delete-response');

    // Request example routes
    Route::post('api-inspector-docs/save-request-example', [ApiInspectorController::class, 'saveRequestExample'])->name('api-inspector.save-request-example');
    Route::get('api-inspector-docs/get-request-examples', [ApiInspectorController::class, 'getRequestExamples'])->name('api-inspector.get-request-examples');
    Route::post('api-inspector-docs/delete-request-example', [ApiInspectorController::class, 'deleteRequestExample'])->name('api-inspector.delete-request-example');

    // Dashboard analytics routes
    Route::get('api-inspector-docs/analytics', [DashboardController::class, 'getDashboardData'])->name('api-inspector.analytics');
});
