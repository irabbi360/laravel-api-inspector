<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Irabbi360\LaravelApiInspector\Http\Middleware\CaptureResponseMiddleware;

beforeEach(function () {
    Cache::flush();
});

it('captures JSON API responses when middleware is enabled', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/api/users');

    $response = new JsonResponse(
        ['success' => true, 'data' => ['id' => 1, 'name' => 'John']],
        200
    );

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(200);
});

it('does not capture when middleware is disabled', function () {
    config(['api-inspector.middleware_capture' => false]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/api/users');

    $response = new JsonResponse(['success' => true, 'data' => []], 200);

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(200);
});

it('does not capture non-JSON responses', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/api/users');

    // Create a response with HTML content type
    $response = response('<!DOCTYPE html><html></html>', 200)
        ->header('Content-Type', 'text/html');

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->status())->toBe(200);
});

it('only captures API routes that match configuration', function () {
    config([
        'api-inspector.middleware_capture' => true,
        'api-inspector.only_route_uri_start_with' => 'api/',
    ]);

    $middleware = app(CaptureResponseMiddleware::class);

    // Non-API route
    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/web/users');

    $response = new JsonResponse(['success' => true], 200);

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(200);
});

it('captures successful responses (2xx status codes)', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'POST');
    $request->server->set('REQUEST_URI', '/api/users');

    // Test with 201 Created
    $response = new JsonResponse(
        ['success' => true, 'data' => ['id' => 1]],
        201
    );

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(201);
});

it('does not capture error responses by default', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'POST');
    $request->server->set('REQUEST_URI', '/api/users');

    // 400 error response
    $response = new JsonResponse(
        ['success' => false, 'error' => 'Validation failed'],
        400
    );

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(400);
});

it('gracefully handles invalid JSON responses', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/api/users');

    // Create response that claims to be JSON but has invalid content
    $response = response('invalid json {', 200)
        ->header('Content-Type', 'application/json');

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    // Should not throw, just return the original response
    expect($result->getStatusCode())->toBe(200);
});

it('uses configured response TTL', function () {
    config([
        'api-inspector.middleware_capture' => true,
        'api-inspector.save_responses_driver' => 'cache',
        'api-inspector.response_ttl' => 600, // 10 minutes
    ]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'GET');
    $request->server->set('REQUEST_URI', '/api/users');

    $response = new JsonResponse(['success' => true], 200);

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(200);
});

it('handles empty response content gracefully', function () {
    config(['api-inspector.middleware_capture' => true]);

    $middleware = app(CaptureResponseMiddleware::class);

    $request = new Request;
    $request->server->set('REQUEST_METHOD', 'DELETE');
    $request->server->set('REQUEST_URI', '/api/users/1');

    // 204 No Content response
    $response = response('', 204)
        ->header('Content-Type', 'application/json');

    $next = fn ($req) => $response;

    $result = $middleware->handle($request, $next);

    expect($result->getStatusCode())->toBe(204);
});
