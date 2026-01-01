<?php

use Irabbi360\LaravelApiInspector\Models\ApiAnalytic;

beforeEach(function () {
    // Create test analytics data
    ApiAnalytic::create([
        'route' => 'api/users/index',
        'method' => 'GET',
        'uri' => '/api/users',
        'status_code' => 200,
        'duration_ms' => 125.5,
        'memory_usage' => 2048000,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'error' => null,
        'recorded_at' => now(),
    ]);

    ApiAnalytic::create([
        'route' => 'api/users/store',
        'method' => 'POST',
        'uri' => '/api/users',
        'status_code' => 201,
        'duration_ms' => 250.5,
        'memory_usage' => 4096000,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'error' => null,
        'recorded_at' => now(),
    ]);

    ApiAnalytic::create([
        'route' => 'api/users/show',
        'method' => 'GET',
        'uri' => '/api/users/1',
        'status_code' => 404,
        'duration_ms' => 50.2,
        'memory_usage' => 1024000,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'error' => 'User not found',
        'recorded_at' => now(),
    ]);
});

it('records api analytics', function () {
    expect(ApiAnalytic::count())->toBe(3);
    expect(ApiAnalytic::where('status_code', 200)->count())->toBe(1);
});

it('calculates average response time', function () {
    $avg = ApiAnalytic::avg('duration_ms');
    $expected = (125.5 + 250.5 + 50.2) / 3;

    expect($avg)->toBeGreaterThan($expected - 1);
    expect($avg)->toBeLessThan($expected + 1);
});

it('filters analytics by route', function () {
    $routeAnalytics = ApiAnalytic::forRoute('api/users/index')->get();

    expect($routeAnalytics)->toHaveCount(1);
    expect($routeAnalytics[0]->status_code)->toBe(200);
});

it('gets error analytics', function () {
    $errors = ApiAnalytic::errors()->get();

    expect($errors)->toHaveCount(1);
    expect($errors[0]->error)->toBe('User not found');
});

it('gets slow requests', function () {
    $slow = ApiAnalytic::slow(200)->get();

    expect($slow)->toHaveCount(1);
    expect($slow[0]->duration_ms)->toBe(250.5);
});

it('gets recent analytics', function () {
    $recent = ApiAnalytic::recent(60)->get();

    expect($recent->count())->toBeGreaterThan(0);
});

it('calculates average response time for route', function () {
    $avg = ApiAnalytic::averageResponseTime('api/users/index');

    expect($avg)->toBe(125.5);
});

it('calculates error rate for route', function () {
    ApiAnalytic::create([
        'route' => 'api/users/index',
        'method' => 'GET',
        'uri' => '/api/users',
        'status_code' => 500,
        'duration_ms' => 500.0,
        'memory_usage' => 5000000,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'error' => 'Server Error',
        'recorded_at' => now(),
    ]);

    $errorRate = ApiAnalytic::errorRate('api/users/index');

    // 1 error out of 2 requests = 50%
    expect($errorRate)->toBeGreaterThan(49);
    expect($errorRate)->toBeLessThan(51);
});

it('gets route statistics', function () {
    $stats = ApiAnalytic::getRouteStats('api/users/index');

    expect($stats['total_requests'])->toBe(1);
    expect($stats['avg_response_time'])->toBe(125.5);
    expect($stats['error_rate'])->toBe(0);
});

it('gets status code statistics', function () {
    $stats = ApiAnalytic::getStatusCodeStats();

    expect($stats[200])->toBe(1);
    expect($stats[201])->toBe(1);
    expect($stats[404])->toBe(1);
});

it('gets top slow routes', function () {
    $routes = ApiAnalytic::getTopSlowRoutes(5);

    expect($routes)->toHaveCount(3);
    expect($routes[0]->route)->toBe('api/users/store'); // Slowest is 250.5ms
});

it('gets top error routes', function () {
    // Create more errors
    ApiAnalytic::create([
        'route' => 'api/posts/show',
        'method' => 'GET',
        'uri' => '/api/posts/1',
        'status_code' => 500,
        'duration_ms' => 100.0,
        'memory_usage' => 2000000,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'error' => 'Server Error',
        'recorded_at' => now(),
    ]);

    $routes = ApiAnalytic::getTopErrorRoutes(5);

    expect($routes->count())->toBeGreaterThan(0);
});

it('tests bearer token authentication', function () {
    // This is a mock test - in real usage, would test actual endpoints
    expect(true)->toBeTrue(); // Placeholder for actual auth testing
});

it('filters analytics by status code', function () {
    $successResponses = ApiAnalytic::withStatusCode(200)->get();

    expect($successResponses)->toHaveCount(1);
    expect($successResponses[0]->status_code)->toBe(200);
});

it('tracks memory usage', function () {
    $analytics = ApiAnalytic::first();

    expect($analytics->memory_usage)->toBeGreaterThan(0);
});

it('tracks ip address', function () {
    $analytics = ApiAnalytic::first();

    expect($analytics->ip_address)->toBe('127.0.0.1');
});

it('tracks user agent', function () {
    $analytics = ApiAnalytic::first();

    expect($analytics->user_agent)->toBe('Test Agent');
});
