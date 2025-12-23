<?php

namespace Irabbi360\LaravelApiInspector\Extractors;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

class RouteExtractor
{
    /**
     * Extract all API routes from Laravel router
     *
     * @return array<int, array>
     */
    public static function extract(): array
    {
        $routes = [];
        $router = app(Router::class);

        foreach ($router->getRoutes()->getRoutes() as $route) {
            $extracted = self::extractRoute($route);
            if ($extracted) {
                $routes[] = $extracted;
            }
        }

        return $routes;
    }

    /**
     * Extract a single route
     */
    public static function extractRoute(Route $route): ?array
    {
        // Only extract API routes (those with 'api' middleware)
        if (! self::isApiRoute($route)) {
            return null;
        }

        $controller = $route->getActionName();

        return [
            'method' => self::getMethod($route),
            'uri' => $route->uri,
            'name' => $route->getName() ?? '',
            'controller' => $controller,
            'middleware' => self::getMiddleware($route),
            'requires_auth' => self::requiresAuth($route),
            'parameters' => self::extractParameters($route),
            'description' => self::generateDescription($route->uri, self::getMethod($route)),
        ];
    }

    /**
     * Check if route is an API route
     */
    public static function isApiRoute(Route $route): bool
    {
        $middleware = $route->gatherMiddleware();

        return in_array('api', $middleware) || str_starts_with($route->uri, 'api/');
    }

    /**
     * Get HTTP method
     */
    public static function getMethod(Route $route): string
    {
        $methods = $route->methods;

        // Filter out HEAD method
        $methods = array_filter($methods, fn ($m) => $m !== 'HEAD');

        return strtoupper(current($methods) ?: 'GET');
    }

    /**
     * Get route middleware
     *
     * @return array<string>
     */
    public static function getMiddleware(Route $route): array
    {
        return $route->gatherMiddleware();
    }

    /**
     * Check if route requires authentication
     */
    public static function requiresAuth(Route $route): bool
    {
        $middleware = self::getMiddleware($route);

        return in_array('auth', $middleware) || in_array('auth:api', $middleware) || in_array('auth:sanctum', $middleware);
    }

    /**
     * Extract path parameters from URI
     *
     * @return array<string, array<string, mixed>>
     */
    public static function extractParameters(Route $route): array
    {
        $parameters = [];

        // Extract from route parameters
        foreach ($route->parameterNames() as $param) {
            $parameters[$param] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'type' => 'string',
                'example' => '1',
                'description' => ucfirst($param).' ID',
            ];
        }

        return $parameters;
    }

    /**
     * Generate description for a route
     */
    public static function generateDescription(string $uri, string $method): string
    {
        $parts = explode('/', trim($uri, '/'));
        $action = match ($method) {
            'GET' => 'Retrieve',
            'POST' => 'Create',
            'PUT', 'PATCH' => 'Update',
            'DELETE' => 'Delete',
            default => 'Manage',
        };

        $resource = ucfirst(end($parts));

        return "$action $resource";
    }
}
