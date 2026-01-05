<?php

namespace Irabbi360\LaravelApiInspector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Irabbi360\LaravelApiInspector\Models\ApiAnalytic;
use Irabbi360\LaravelApiInspector\Services\DashboardStatsService;

class DashboardController
{

    /**
     * Constructor
     * @param DashboardStatsService $service
     */
    public function __construct(private DashboardStatsService $service) {}

    /**
     * Get dashboard data
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $range = $request->get('range', '24h');
        $minutes = $this->service->getRangeInMinutes($range);

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
            'statusCodeStats' => $this->service->getStatusCodeStats(),
            'topRoutes' => $this->service->getTopRoutes(),
            'recentErrors' => $this->service->getRecentErrors($minutes),
        ]);
    }
}
