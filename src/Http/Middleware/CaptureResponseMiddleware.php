<?php

namespace Irabbi360\LaravelApiInspector\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Irabbi360\LaravelApiInspector\Writers\CacheWriter;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

class CaptureResponseMiddleware
{
    protected CacheWriter $cacheWriter;

    protected JsonFileWriter $jsonFileWriter;

    public function __construct(CacheWriter $cacheWriter, JsonFileWriter $jsonFileWriter)
    {
        $this->cacheWriter = $cacheWriter;
        $this->jsonFileWriter = $jsonFileWriter;
    }

    /**
     * Handle the incoming request
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $response = $next($request);

        // Only capture if middleware capture is enabled and response is JSON
        if (config('api-inspector.middleware_capture') && $this->shouldCapture($request, $response)) {
            $this->captureResponse($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the response should be captured
     */
    protected function shouldCapture(Request $request, Response|JsonResponse $response): bool
    {
        // Check if response is JSON
        if (! $this->isJsonResponse($response)) {
            return false;
        }

        // Check if route matches API pattern
        if (! $this->isApiRoute($request)) {
            return false;
        }

        // Only capture successful responses (2xx status codes)
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }

    /**
     * Check if response is JSON
     */
    protected function isJsonResponse(Response|JsonResponse $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'application/json');
    }

    /**
     * Check if the request is to an API route
     */
    protected function isApiRoute(Request $request): bool
    {
        $onlyRouteUriStartWith = config('api-inspector.only_route_uri_start_with', 'api/');
        $path = $request->getPathInfo() ?: $request->path();

        return str_starts_with($path, $onlyRouteUriStartWith);
    }

    /**
     * Capture the API response
     */
    protected function captureResponse(Request $request, Response|JsonResponse $response): void
    {
        try {
            $responseData = $this->parseResponse($response);
            $cacheKey = $this->generateCacheKey($request, $response);
            $driver = config('api-inspector.save_responses_driver', 'cache');

            if ($driver === 'json') {
                $this->saveToJsonFile($cacheKey, $responseData);
            } else {
                $this->saveToCache($cacheKey, $responseData);
            }
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting the actual API response
            report($e);
        }
    }

    /**
     * Parse response content to array
     */
    protected function parseResponse(Response|JsonResponse $response): array
    {
        $content = $response->getContent();

        if (! $content) {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Generate a unique cache key for the response
     */
    protected function generateCacheKey(Request $request, Response|JsonResponse $response): string
    {
        $route = $request->route()?->getName() ?? str_replace('/', '_', $request->path());
        $statusCode = $response->getStatusCode();
        $timestamp = now()->timestamp;

        return "response:{$route}:{$statusCode}:{$timestamp}";
    }

    /**
     * Save response to cache
     */
    protected function saveToCache(string $key, array $data): void
    {
        $data['captured_at'] = now()->toIso8601String();
        $this->cacheWriter->save($key, $data, config('api-inspector.response_ttl', 3600));
    }

    /**
     * Save response to JSON file
     */
    protected function saveToJsonFile(string $key, array $data): void
    {
        $data['captured_at'] = now()->toIso8601String();
        $filename = str_replace(':', '_', $key);
        $path = storage_path("public/app/api-docs/responses/{$filename}.json");
        $this->jsonFileWriter->save($path, $data);
    }
}
