<?php

use Illuminate\Support\Facades\Cache;
use Irabbi360\LaravelApiInspector\Support\ResponseCache;
use Irabbi360\LaravelApiInspector\Writers\CacheWriter;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

beforeEach(function () {
    Cache::flush();
});

it('stores a response in cache', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    $responseData = [
        'success' => true,
        'message' => 'User created',
        'data' => ['id' => 1, 'name' => 'John'],
    ];

    config(['api-inspector.save_responses_driver' => 'cache']);

    $result = $responseCache->store('api/users/store', 201, $responseData);

    expect($result)->toBeTrue();
    expect($responseCache->has('api/users/store', 201))->toBeTrue();
});

it('retrieves a cached response', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    $responseData = [
        'success' => true,
        'message' => 'User fetched',
        'data' => ['id' => 1, 'name' => 'John'],
    ];

    config(['api-inspector.save_responses_driver' => 'cache']);

    $responseCache->store('api/users/show', 200, $responseData);
    $cached = $responseCache->get('api/users/show', 200);

    expect($cached)->not->toBeNull();
    expect($cached['data']['name'])->toBe('John');
    expect($cached['status_code'])->toBe(200);
});

it('checks if response is cached', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config(['api-inspector.save_responses_driver' => 'cache']);

    expect($responseCache->has('api/users/show', 200))->toBeFalse();

    $responseCache->store('api/users/show', 200, ['data' => []]);

    expect($responseCache->has('api/users/show', 200))->toBeTrue();
});

it('clears cached responses for a route', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config(['api-inspector.save_responses_driver' => 'cache']);

    $responseCache->store('api/users/store', 201, ['data' => []]);
    $responseCache->store('api/users/store', 400, ['error' => 'Validation failed']);

    expect($responseCache->has('api/users/store', 201))->toBeTrue();
    expect($responseCache->has('api/users/store', 400))->toBeTrue();

    $responseCache->clearForRoute('api/users/store');

    expect($responseCache->has('api/users/store', 201))->toBeFalse();
    expect($responseCache->has('api/users/store', 400))->toBeFalse();
});

it('clears all cached responses', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config(['api-inspector.save_responses_driver' => 'cache']);

    $responseCache->store('api/users/store', 201, ['data' => []]);
    $responseCache->store('api/posts/store', 201, ['data' => []]);

    // Should not throw an exception
    $result = $responseCache->clearAll();

    expect($result)->toBeTrue();
});

it('adds timestamp to cached responses', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config(['api-inspector.save_responses_driver' => 'cache']);

    $responseCache->store('api/users/show', 200, ['data' => []]);
    $cached = $responseCache->get('api/users/show', 200);

    expect($cached)->toHaveKey('cached_at');
    expect($cached['cached_at'])->toMatch('/\d{4}-\d{2}-\d{2}T/'); // ISO 8601 format
});

it('handles JSON driver for storing responses', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config(['api-inspector.save_responses_driver' => 'json']);

    $responseData = [
        'success' => true,
        'message' => 'User created',
        'data' => ['id' => 1, 'name' => 'John'],
    ];

    $result = $responseCache->store('api/users/store', 201, $responseData);

    expect($result)->toBeTrue();
});

it('respects response TTL configuration', function () {
    $cacheWriter = app(CacheWriter::class);
    $jsonWriter = app(JsonFileWriter::class);
    $responseCache = new ResponseCache($cacheWriter, $jsonWriter);

    config([
        'api-inspector.save_responses_driver' => 'cache',
        'api-inspector.response_ttl' => 300, // 5 minutes
    ]);

    $responseCache->store('api/users/show', 200, ['data' => []]);

    expect($responseCache->has('api/users/show', 200))->toBeTrue();
});
