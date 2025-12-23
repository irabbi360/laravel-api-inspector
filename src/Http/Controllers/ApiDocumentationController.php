<?php

namespace Irabbi360\LaravelApiInspector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class ApiDocumentationController extends Controller
{
    /**
     * Display API documentation (lightweight HTML)
     */
    public function index()
    {
        return view('api-inspector::api-docs-spa', []);
    }

    /**
     * Fetch real-time API routes and documentation
     */
    public function fetchApiInfo(): JsonResponse
    {
        try {
            $routes = [];
            $requestRuleExtractor = new \Irabbi360\LaravelApiInspector\Extractors\RequestRuleExtractor;
            $uriPrefix = config('api-inspector.only_route_uri_start_with', '');

            // Extract all API routes from the router
            foreach (\Route::getRoutes()->getRoutes() as $route) {
                // Skip documentation routes and internal routes
                if ($this->shouldSkipRoute($route)) {
                    continue;
                }

                // Filter by URI prefix if configured
                if ($uriPrefix && ! \Illuminate\Support\Str::startsWith($route->uri, $uriPrefix)) {
                    continue;
                }

                $methods = $route->methods;
                $methods = array_filter($methods, fn ($m) => ! in_array($m, ['HEAD', 'OPTIONS']));

                foreach ($methods as $method) {
                    $requestRules = [];
                    $parameters = [];
                    $controllerName = '';

                    try {
                        // Extract request rules if there's a FormRequest
                        $controllerName = $route->getActionName();
                        if ($controllerName && $controllerName !== 'Closure') {
                            $requestRules = $requestRuleExtractor->extract($controllerName);

                            // If no rules found from FormRequest, try to extract from Request class parameter
                            if (empty($requestRules)) {
                                $requestRules = $this->generateExampleRequestRules($controllerName);
                            }
                        }
                    } catch (\Exception $e) {
                        // Silent fail, FormRequest extraction is optional
                        // This can happen with custom Rule objects or other extraction issues
                    }

                    try {
                        // Extract parameters from route URI
                        $parameters = $this->extractRouteParameters($route->uri);
                    } catch (\Exception $e) {
                        // Silent fail
                    }

                    $routes[] = [
                        'method' => strtoupper($method),
                        'uri' => $route->uri,
                        'name' => $route->getName() ?? '',
                        'description' => $this->getRouteDescription($route),
                        'requires_auth' => $this->requiresAuth($route),
                        'parameters' => $parameters,
                        'request_rules' => $requestRules,
                        'response_example' => ['success' => true, 'message' => 'Success'],
                    ];
                }
            }

            return response()->json([
                'title' => config('api.title') ?? 'Laravel API',
                'version' => config('api.version') ?? '1.0.0',
                'routes' => $routes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extract route parameters from URI
     */
    private function extractRouteParameters(string $uri): array
    {
        $parameters = [];
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);

        if (! empty($matches[1])) {
            foreach ($matches[1] as $param) {
                $param = str_replace('?', '', $param);
                $parameters[$param] = [
                    'name' => $param,
                    'type' => 'string',
                    'description' => 'Path parameter',
                ];
            }
        }

        return $parameters;
    }

    /**
     * Generate request rules from controller Request class parameter
     *
     * @return array<string, array>
     */
    private function generateExampleRequestRules(string $controllerName): array
    {
        try {
            $parsed = \Irabbi360\LaravelApiInspector\Support\ReflectionHelper::parseControllerString($controllerName);

            if (! $parsed) {
                return [];
            }

            $reflectionMethod = \Irabbi360\LaravelApiInspector\Support\ReflectionHelper::getMethod($parsed['class'], $parsed['method']);

            if (! $reflectionMethod) {
                return [];
            }

            // Get all parameters of the controller method
            $parameters = $reflectionMethod->getParameters();

            foreach ($parameters as $param) {
                $paramType = $param->getType();

                // Check if parameter is a Request class
                if ($paramType instanceof \ReflectionNamedType && ! $paramType->isBuiltin()) {
                    $paramClass = $paramType->getName();

                    // Check if it has a rules() method
                    if (class_exists($paramClass) && method_exists($paramClass, 'rules')) {
                        try {
                            $instance = new $paramClass;
                            $rules = $instance->rules();

                            // Parse the rules using RuleParser
                            return \Irabbi360\LaravelApiInspector\Support\RuleParser::parse($rules);
                        } catch (\Exception $e) {
                            // Silent fail if instantiation fails
                            continue;
                        }
                    }
                }
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if route should be skipped
     */
    private function shouldSkipRoute($route): bool
    {
        $uri = $route->uri;
        $skipped = ['api/docs', 'api/test-request', 'api/save-response', 'api/saved-responses', 'sanctum'];

        foreach ($skipped as $skip) {
            if (str_contains($uri, $skip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get route description
     */
    private function getRouteDescription($route): string
    {
        // Try to get from route action
        if ($route->action && isset($route->action['description'])) {
            return $route->action['description'];
        }

        return ucfirst(str_replace('_', ' ', last(explode('/', $route->uri))));
    }

    /**
     * Check if route requires authentication
     */
    private function requiresAuth($route): bool
    {
        $middleware = $route->middleware() ?? [];

        return in_array('auth', $middleware) || in_array('auth:api', $middleware) || in_array('auth:sanctum', $middleware);
    }

    /**
     * Display Postman collection JSON
     */
    public function postman()
    {
        $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
        $file = "$docsPath/postman_collection.json";

        if (! file_exists($file)) {
            return response()->json(['error' => 'Postman collection not found'], 404);
        }

        return response()->file($file, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="postman_collection.json"',
        ]);
    }

    /**
     * Display OpenAPI specification
     */
    public function openapi()
    {
        $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
        $file = "$docsPath/openapi.json";

        if (! file_exists($file)) {
            return response()->json(['error' => 'OpenAPI specification not found'], 404);
        }

        return response()->file($file, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="openapi.json"',
        ]);
    }

    /**
     * Send a test request to an API endpoint
     */
    public function testRequest(Request $request): JsonResponse
    {
        try {
            $method = strtoupper($request->input('method', 'GET'));
            $uri = $request->input('uri', '');
            $body = $request->input('body', []);

            if (! $uri) {
                return response()->json(['error' => 'URI is required'], 400);
            }

            // Make HTTP request to the endpoint
            $httpClient = new \GuzzleHttp\Client(['verify' => false]);
            $requestOptions = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ];

            if (in_array($method, ['POST', 'PUT', 'PATCH']) && ! empty($body)) {
                $requestOptions['json'] = $body;
            }

            // Add query string for GET requests if body is provided
            if ($method === 'GET' && ! empty($body)) {
                $requestOptions['query'] = $body;
            }

            $baseUrl = config('app.url') ?? 'http://localhost';
            $fullUrl = rtrim($baseUrl, '/').'/'.ltrim($uri, '/');

            $response = $httpClient->request($method, $fullUrl, $requestOptions);

            $responseBody = json_decode((string) $response->getBody(), true);

            return response()->json($responseBody, $response->getStatusCode());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'failed',
            ], 500);
        }
    }

    /**
     * Save API response for later reference
     */
    public function saveResponse(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->input('route_uri');
            $routeMethod = $request->input('route_method');
            $responseData = $request->input('response', []);
            $timestamp = $request->input('timestamp', now()->toIso8601String());

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'route_uri and route_method are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');

            if ($driver === 'json') {
                return $this->saveResponseToJson($routeUri, $routeMethod, $responseData, $timestamp);
            } else {
                return $this->saveResponseToCache($routeUri, $routeMethod, $responseData, $timestamp);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save response to cache
     */
    private function saveResponseToCache(string $routeUri, string $routeMethod, array $responseData, string $timestamp): JsonResponse
    {
        $cacheKey = 'api-inspector-response:'.md5($routeMethod.':'.$routeUri);
        $responses = Cache::get($cacheKey, []);

        // Keep only last 20 responses
        $responses = array_slice($responses, -19, 19, true);

        $responses[] = [
            'timestamp' => $timestamp,
            'method' => $routeMethod,
            'uri' => $routeUri,
            'data' => $responseData,
        ];

        Cache::put($cacheKey, $responses, now()->addDays(7));

        return response()->json([
            'message' => 'Response saved successfully',
            'count' => count($responses),
        ], 201);
    }

    /**
     * Save response to JSON file
     */
    private function saveResponseToJson(string $routeUri, string $routeMethod, array $responseData, string $timestamp): JsonResponse
    {
        $responsePath = config('api-inspector.response_path') ?? storage_path('api-docs');
        $fileName = md5($routeMethod.':'.$routeUri).'.json';
        $filePath = $responsePath.'/responses/'.$fileName;

        // Create directory if it doesn't exist
        if (! is_dir($responsePath.'/responses')) {
            mkdir($responsePath.'/responses', 0755, true);
        }

        // Load existing responses
        $responses = [];
        if (file_exists($filePath)) {
            $responses = json_decode(file_get_contents($filePath), true) ?? [];
        }

        // Keep only last 20 responses
        $responses = array_slice($responses, -19, 19, true);

        $responses[] = [
            'timestamp' => $timestamp,
            'method' => $routeMethod,
            'uri' => $routeUri,
            'data' => $responseData,
        ];

        file_put_contents($filePath, json_encode($responses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'message' => 'Response saved successfully',
            'count' => count($responses),
        ], 201);
    }

    /**
     * Get saved responses for a specific route
     */
    public function savedResponses(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->query('uri');
            $routeMethod = $request->query('method');

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'uri and method query parameters are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');
            $responses = [];

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path') ?? storage_path('api-docs');
                $fileName = md5($routeMethod.':'.$routeUri).'.json';
                $filePath = $responsePath.'/responses/'.$fileName;

                if (file_exists($filePath)) {
                    $responses = json_decode(file_get_contents($filePath), true) ?? [];
                }
            } else {
                $cacheKey = 'api-inspector-response:'.md5($routeMethod.':'.$routeUri);
                $responses = Cache::get($cacheKey, []);
            }

            return response()->json([
                'uri' => $routeUri,
                'method' => $routeMethod,
                'responses' => array_values($responses),
                'count' => count($responses),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch real-time API documentation
     */
    public function realtimeDocs(): JsonResponse
    {
        try {
            $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
            $docsFile = "$docsPath/api_routes.json";

            if (! file_exists($docsFile)) {
                return response()->json(['error' => 'API documentation not generated yet. Run "php artisan api-inspector:generate"'], 404);
            }

            $docs = json_decode(file_get_contents($docsFile), true);

            return response()->json($docs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
