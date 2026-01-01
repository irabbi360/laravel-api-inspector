<?php

namespace Irabbi360\LaravelApiInspector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Irabbi360\LaravelApiInspector\Models\ApiAnalytic;

class DashboardController
{
    /**
     * Get dashboard data
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $range = $request->get('range', '24h');
        $minutes = $this->getRangeInMinutes($range);

        $query = ApiAnalytic::recent($minutes);

        $totalRequests = $query->count();
        $avgResponseTime = $query->avg('duration_ms') ?? 0;
        $errorCount = $query->whereNotNull('error')->where('status_code', '>=', 400)->count();
        $errorRate = $totalRequests > 0 ? ($errorCount / $totalRequests) * 100 : 0;
        $avgMemory = $query->avg('memory_usage') ?? 0;

        // Get slowest route
        $slowestRoute = ApiAnalytic::recent($minutes)
            ->selectRaw('route, AVG(duration_ms) as avg_time')
            ->groupBy('route')
            ->orderByDesc('avg_time')
            ->first();

        // Get trends
        $previousQuery = ApiAnalytic::whereDate('recorded_at', '>=', now()->subMinutes($minutes * 2))
            ->whereDate('recorded_at', '<', now()->subMinutes($minutes));
        $previousCount = $previousQuery->count();
        $requestsTrend = $previousCount > 0 ? (($totalRequests - $previousCount) / $previousCount) * 100 : 0;

        return response()->json([
            'totalRequests' => $totalRequests,
            'requestsTrend' => round($requestsTrend, 2),
            'avgResponseTime' => $avgResponseTime,
            'slowestRoute' => $slowestRoute->route ?? 'N/A',
            'errorRate' => $errorRate,
            'errorCount' => $errorCount,
            'avgMemory' => $avgMemory,
            'statusCodeStats' => $this->getStatusCodeStats(),
            'topRoutes' => $this->getTopRoutes(),
            'recentErrors' => $this->getRecentErrors($minutes),
        ]);
    }

    /**
     * Get status code statistics
     */
    protected function getStatusCodeStats()
    {
        $stats = ApiAnalytic::selectRaw('status_code, COUNT(*) as count, AVG(duration_ms) as avg_duration')
            ->groupBy('status_code')
            ->orderBy('status_code')
            ->get();

        $result = [];

        foreach ($stats as $stat) {
            $result[$stat->status_code] = $stat->count;
        }

        return $result;
    }

    /**
     * Get top routes by response time
     */
    protected function getTopRoutes()
    {
        return ApiAnalytic::selectRaw('route, method, COUNT(*) as count, AVG(duration_ms) as avg_duration, MIN(duration_ms) as min, MAX(duration_ms) as max, status_code')
            ->groupBy('route', 'method', 'status_code')
            ->orderByDesc('avg_duration')
            ->limit(10)
            ->get()
            ->map(function ($route) {
                // Calculate error rate for this route
                $totalForRoute = ApiAnalytic::where('route', $route->route)->count();
                $errorsForRoute = ApiAnalytic::where('route', $route->route)
                    ->whereNotNull('error')
                    ->where('status_code', '>=', 400)
                    ->count();
                $errorRate = $totalForRoute > 0 ? ($errorsForRoute / $totalForRoute) * 100 : 0;

                return [
                    'route' => $route->route,
                    'method' => $route->method,
                    'count' => $route->count,
                    'avg_duration' => $route->avg_duration,
                    'min' => $route->min,
                    'max' => $route->max,
                    'status_code' => $route->status_code,
                    'errorRate' => $errorRate,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent errors
     */
    protected function getRecentErrors(int $minutes)
    {
        return ApiAnalytic::recent($minutes)
            ->whereNotNull('error')
            ->where('status_code', '>=', 400)
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Convert time range to minutes
     */
    protected function getRangeInMinutes(string $range): int
    {
        return match ($range) {
            '1h' => 60,
            '24h' => 1440,
            '7d' => 10080,
            '30d' => 43200,
            default => 1440,
        };
    }

    /**
     * Get webhook documentation
     */
    public function getWebhooks(): JsonResponse
    {
        $webhooksConfig = config('api-inspector.webhooks', []);

        $webhooks = array_map(fn ($name, $config) => [
            'name' => $name,
            'event' => $config['event'] ?? $name,
            'description' => $config['description'] ?? '',
            'payload' => $config['payload'] ?? [],
            'examples' => $config['examples'] ?? [],
        ], array_keys($webhooksConfig), $webhooksConfig);

        return response()->json($webhooks);
    }

    /**
     * Test authentication
     */
    public function testAuthentication(Request $request): JsonResponse
    {
        $type = $request->get('type', 'bearer');
        $endpoint = $request->get('endpoint', '');
        $token = $request->get('token', '');

        if (! $endpoint) {
            return response()->json([
                'success' => false,
                'error' => 'Endpoint is required',
            ], 400);
        }

        try {
            $tester = app('auth-tester');

            $result = match ($type) {
                'bearer' => $tester->testBearerAuth($endpoint, $token),
                'api-key' => $tester->testApiKeyAuth($endpoint, $token),
                'basic' => $tester->testBasicAuth($endpoint, $request->get('username', ''), $request->get('password', '')),
                'none' => $tester->testNoAuth($endpoint),
                default => ['success' => false, 'error' => 'Unknown auth type'],
            };

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get analytics for a specific route
     */
    public function getRouteAnalytics(Request $request): JsonResponse
    {
        $route = $request->get('route', '');

        if (! $route) {
            return response()->json([
                'success' => false,
                'error' => 'Route is required',
            ], 400);
        }

        $stats = ApiAnalytic::getRouteStats($route);

        return response()->json($stats);
    }

    /**
     * Clear old analytics
     */
    public function clearAnalytics(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);

        $count = ApiAnalytic::where('recorded_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} old analytics records",
        ]);
    }
}
