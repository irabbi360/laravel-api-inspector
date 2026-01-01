<?php

namespace Irabbi360\LaravelApiInspector\Models;

use Illuminate\Database\Eloquent\Model;

class ApiAnalytic extends Model
{
    protected $table = 'api_inspector_analytics';

    protected $fillable = [
        'route',
        'method',
        'uri',
        'status_code',
        'duration_ms',
        'memory_usage',
        'ip_address',
        'user_agent',
        'error',
        'recorded_at',
    ];

    protected $casts = [
        'duration_ms' => 'float',
        'memory_usage' => 'integer',
        'status_code' => 'integer',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get analytics for a specific route
     */
    public static function forRoute(string $route)
    {
        return static::where('route', $route);
    }

    /**
     * Get analytics for a specific status code
     */
    public static function withStatusCode(int $code)
    {
        return static::where('status_code', $code);
    }

    /**
     * Get error analytics
     */
    public static function errors()
    {
        return static::whereNotNull('error')
            ->where('status_code', '>=', 400);
    }

    /**
     * Get slow requests (over threshold ms)
     */
    public static function slow(int $thresholdMs = 1000)
    {
        return static::where('duration_ms', '>', $thresholdMs);
    }

    /**
     * Get recent analytics
     */
    public static function recent(int $minutes = 60)
    {
        return static::where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Calculate average response time for route
     */
    public static function averageResponseTime(string $route)
    {
        return static::forRoute($route)->avg('duration_ms');
    }

    /**
     * Get error rate for route
     */
    public static function errorRate(string $route)
    {
        $total = static::forRoute($route)->count();

        if ($total === 0) {
            return 0;
        }

        $errors = static::forRoute($route)->whereNotNull('error')->where('status_code', '>=', 400)->count();

        return ($errors / $total) * 100;
    }

    /**
     * Get statistics for route
     */
    public static function getRouteStats(string $route)
    {
        $query = static::forRoute($route);

        return [
            'total_requests' => $query->count(),
            'avg_response_time' => $query->avg('duration_ms'),
            'min_response_time' => $query->min('duration_ms'),
            'max_response_time' => $query->max('duration_ms'),
            'total_errors' => static::forRoute($route)->whereNotNull('error')->where('status_code', '>=', 400)->count(),
            'error_rate' => static::errorRate($route),
            'avg_memory' => $query->avg('memory_usage'),
        ];
    }

    /**
     * Get statistics by status code
     */
    public static function getStatusCodeStats()
    {
        return static::selectRaw('status_code, COUNT(*) as count, AVG(duration_ms) as avg_duration')
            ->groupBy('status_code')
            ->get()
            ->pluck('count', 'status_code')
            ->toArray();
    }

    /**
     * Get top slow routes
     */
    public static function getTopSlowRoutes(int $limit = 10)
    {
        return static::selectRaw('route, AVG(duration_ms) as avg_duration, COUNT(*) as count')
            ->groupBy('route')
            ->orderBy('avg_duration', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top error routes
     */
    public static function getTopErrorRoutes(int $limit = 10)
    {
        return static::errors()
            ->selectRaw('route, COUNT(*) as error_count')
            ->groupBy('route')
            ->orderBy('error_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
