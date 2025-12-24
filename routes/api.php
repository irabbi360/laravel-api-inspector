<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;
use Irabbi360\LaravelApiInspector\Http\Middleware\ApiInspectorMiddleware;

Route::middleware(['api', ApiInspectorMiddleware::class])->prefix('api')->group(function () {
    Route::get('api-inspector-docs', [ApiInspectorController::class, 'fetchApiInfo'])->name('api-inspector.docs.fetch');
    Route::get('api-inspector-docs/postman', [ApiInspectorController::class, 'postman'])->name('api-inspector.docs.postman');
    Route::get('api-inspector-docs/openapi', [ApiInspectorController::class, 'openapi'])->name('api-inspector.docs.openapi');
    Route::get('api-inspector-docs/realtime', [ApiInspectorController::class, 'realtimeDocs'])->name('api-inspector.docs.realtime');
    Route::post('api-inspector/test-request', [ApiInspectorController::class, 'testRequest'])->name('api-inspector.test-request');
    Route::post('api-inspector-docs/save-response', [ApiInspectorController::class, 'saveResponse'])->name('api-inspector.save-response');
    Route::get('api-inspector-docs/get-saved-responses', [ApiInspectorController::class, 'savedResponses'])->name('api-inspector.saved-responses');
});
