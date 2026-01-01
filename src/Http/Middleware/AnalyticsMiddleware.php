<?php

namespace Irabbi360\LaravelApiInspector\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Irabbi360\LaravelApiInspector\Models\ApiAnalytic;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsMiddleware
{
    /**
     * Track API requests and responses
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('api-inspector.analytics.enabled', false)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        // Only track API routes
        if (! $this->isApiRoute($request)) {
            return $response;
        }

        $this->recordAnalytics($request, $response, $startTime, $startMemory);

        return $response;
    }

    /**
     * Check if the request is to an API route
     */
    protected function isApiRoute(Request $request): bool
    {
        $onlyRouteUriStartWith = config('api-inspector.only_route_uri_start_with', 'api/');
        $excludeRoutes = config('api-inspector.analytics.exclude_routes', []);
        $path = $request->path();

        // Check if path starts with the configured prefix
        if (! str_starts_with($path, $onlyRouteUriStartWith)) {
            return false;
        }

        // Check if path should be excluded
        foreach ($excludeRoutes as $excludeRoute) {
            if (str_contains($path, $excludeRoute)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record API analytics
     */
    protected function recordAnalytics(Request $request, Response $response, float $startTime, int $startMemory): void
    {
        try {
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $memory = memory_get_usage() - $startMemory;

            ApiAnalytic::create([
                'route' => $request->route()?->getName() ?? $request->path(),
                'method' => $request->getMethod(),
                'uri' => $request->getPathInfo(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'memory_usage' => $memory,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $this->getErrorMessage($response),
                'recorded_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting the API
            report($e);
        }
    }

    /**
     * Extract error message from response
     */
    protected function getErrorMessage(Response $response): ?string
    {
        if ($response->getStatusCode() < 400) {
            return null;
        }

        try {
            $content = $response->getContent();

            if (! $content) {
                return null;
            }

            $data = json_decode($content, true);

            return $data['message'] ?? $data['error'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
