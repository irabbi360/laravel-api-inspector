<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiDocumentationController;

Route::middleware('web')->group(function () {
    Route::get('/api/docs', [ApiDocumentationController::class, 'index'])->name('api.docs');
    Route::get('/api/docs/fetch', [ApiDocumentationController::class, 'fetchApiInfo'])->name('api.docs.fetch');
    Route::get('/api/docs/postman', [ApiDocumentationController::class, 'postman'])->name('api.docs.postman');
    Route::get('/api/docs/openapi', [ApiDocumentationController::class, 'openapi'])->name('api.docs.openapi');
    Route::get('/api/docs/realtime', [ApiDocumentationController::class, 'realtimeDocs'])->name('api.docs.realtime');
});

Route::middleware('api')->group(function () {
    Route::post('/api/test-request', [ApiDocumentationController::class, 'testRequest'])->name('api.test-request');
    Route::post('/api/save-response', [ApiDocumentationController::class, 'saveResponse'])->name('api.save-response');
    Route::get('/api/saved-responses', [ApiDocumentationController::class, 'savedResponses'])->name('api.saved-responses');
});
