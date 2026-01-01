<?php

namespace Irabbi360\LaravelApiInspector\Support;

use Illuminate\Support\Facades\Cache;
use Irabbi360\LaravelApiInspector\Writers\CacheWriter;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

class ResponseCache
{
    protected CacheWriter $cacheWriter;

    protected JsonFileWriter $jsonFileWriter;

    protected string $cachePrefix = 'api-response:';

    public function __construct(CacheWriter $cacheWriter, JsonFileWriter $jsonFileWriter)
    {
        $this->cacheWriter = $cacheWriter;
        $this->jsonFileWriter = $jsonFileWriter;
    }

    /**
     * Store a response in cache/file
     */
    public function store(string $route, int $statusCode, array $data, ?int $ttl = null): bool
    {
        $key = $this->generateKey($route, $statusCode);
        $data['cached_at'] = now()->toIso8601String();
        $data['status_code'] = $statusCode;

        $driver = config('api-inspector.save_responses_driver', 'cache');
        $ttl = $ttl ?? config('api-inspector.response_ttl', 3600);

        try {
            if ($driver === 'json') {
                return $this->storeToJson($key, $data);
            } else {
                $this->cacheWriter->save($key, $data, $ttl);

                return true;
            }
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Retrieve a cached response
     */
    public function get(string $route, int $statusCode): ?array
    {
        $key = $this->generateKey($route, $statusCode);
        $driver = config('api-inspector.save_responses_driver', 'cache');

        if ($driver === 'json') {
            return $this->getFromJson($key);
        }

        return $this->cacheWriter->get($key);
    }

    /**
     * Check if a response is cached
     */
    public function has(string $route, int $statusCode): bool
    {
        $key = $this->generateKey($route, $statusCode);
        $driver = config('api-inspector.save_responses_driver', 'cache');

        if ($driver === 'json') {
            return $this->hasJson($key);
        }

        return $this->cacheWriter->has($key);
    }

    /**
     * Get all cached responses for a route
     */
    public function getForRoute(string $route): array
    {
        $results = [];
        $driver = config('api-inspector.save_responses_driver', 'cache');

        if ($driver === 'json') {
            $results = $this->getAllJsonResponses($route);
        } else {
            // For cache driver, we'd need a different approach
            // For now, iterate common status codes
            foreach ([200, 201, 400, 401, 403, 404, 422, 500] as $statusCode) {
                if ($this->has($route, $statusCode)) {
                    $results[$statusCode] = $this->get($route, $statusCode);
                }
            }
        }

        return $results;
    }

    /**
     * Clear cached responses for a route
     */
    public function clearForRoute(string $route): bool
    {
        $driver = config('api-inspector.save_responses_driver', 'cache');

        if ($driver === 'json') {
            return $this->clearJsonForRoute($route);
        }

        // For cache driver, clear common status codes
        foreach ([200, 201, 400, 401, 403, 404, 422, 500] as $statusCode) {
            $key = $this->generateKey($route, $statusCode);
            $this->cacheWriter->delete($key);
        }

        return true;
    }

    /**
     * Clear all cached responses
     */
    public function clearAll(): bool
    {
        $driver = config('api-inspector.save_responses_driver', 'cache');

        if ($driver === 'json') {
            return $this->clearAllJson();
        }

        try {
            $this->cacheWriter->clearAll();

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Generate a cache key
     */
    protected function generateKey(string $route, int $statusCode): string
    {
        $route = str_replace('/', '_', $route);

        return "{$this->cachePrefix}{$route}:{$statusCode}";
    }

    /**
     * Store response to JSON file
     */
    protected function storeToJson(string $key, array $data): bool
    {
        $filename = str_replace(':', '_', $key);
        $path = storage_path("api-docs/cached-responses/{$filename}.json");

        return $this->jsonFileWriter->save($path, $data);
    }

    /**
     * Get response from JSON file
     */
    protected function getFromJson(string $key): ?array
    {
        $filename = str_replace(':', '_', $key);
        $path = storage_path("api-docs/cached-responses/{$filename}.json");

        if (! file_exists($path)) {
            return null;
        }

        try {
            $content = file_get_contents($path);

            return json_decode($content, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if JSON response file exists
     */
    protected function hasJson(string $key): bool
    {
        $filename = str_replace(':', '_', $key);
        $path = storage_path("api-docs/cached-responses/{$filename}.json");

        return file_exists($path);
    }

    /**
     * Get all cached responses for a route from JSON files
     */
    protected function getAllJsonResponses(string $route): array
    {
        $results = [];
        $route = str_replace('/', '_', $route);
        $directory = storage_path("api-docs/cached-responses");

        if (! is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);

        foreach ($files as $file) {
            if (str_starts_with($file, "api_response:{$route}:") && str_ends_with($file, '.json')) {
                try {
                    $content = file_get_contents("{$directory}/{$file}");
                    $data = json_decode($content, true);

                    if (isset($data['status_code'])) {
                        $results[$data['status_code']] = $data;
                    }
                } catch (\Exception $e) {
                    // Skip invalid files
                }
            }
        }

        return $results;
    }

    /**
     * Clear cached responses for route from JSON files
     */
    protected function clearJsonForRoute(string $route): bool
    {
        $route = str_replace('/', '_', $route);
        $directory = storage_path("api-docs/cached-responses");

        if (! is_dir($directory)) {
            return true;
        }

        $files = scandir($directory);

        foreach ($files as $file) {
            if (str_starts_with($file, "api_response:{$route}:") && str_ends_with($file, '.json')) {
                try {
                    unlink("{$directory}/{$file}");
                } catch (\Exception $e) {
                    // Continue on error
                }
            }
        }

        return true;
    }

    /**
     * Clear all cached responses from JSON files
     */
    protected function clearAllJson(): bool
    {
        $directory = storage_path("api-docs/cached-responses");

        if (! is_dir($directory)) {
            return true;
        }

        try {
            $files = scandir($directory);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && str_ends_with($file, '.json')) {
                    unlink("{$directory}/{$file}");
                }
            }

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }
}
