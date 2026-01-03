<?php

namespace Irabbi360\LaravelApiInspector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Irabbi360\LaravelApiInspector\Extractors\RouteExtractor;
use Irabbi360\LaravelApiInspector\Facades\LaravelApiInspector;
use Irabbi360\LaravelApiInspector\Generators\OpenApiGenerator;
use Irabbi360\LaravelApiInspector\Generators\PostmanGenerator;
use Irabbi360\LaravelApiInspector\LaravelApiInspectorService;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

class ApiInspectorController extends Controller
{
    public function __construct(private LaravelApiInspectorService $service) {}

    /**
     * Display API documentation (lightweight SPA)
     */
    public function index()
    {
        return view('api-inspector::index', [
            'lapiScriptVariables' => [
                'title' => config('api-inspector.title', 'Laravel API Inspector'),
                'version' => LaravelApiInspector::version(),
                'app_name' => config('app.name'),
                'path' => config('api-inspector.route_path', '/api-docs'),
                'route_path' => config('api-inspector.route_path', 'api-docs'),
                'api_path' => 'api',
            ],
        ]);
    }

    /**
     * Fetch real-time API routes and documentation
     */
    public function fetchApiInfo(Request $request): JsonResponse
    {
        try {
            [$routes, $groupBy] = $this->service->apiListData($request);

            return response()->json([
                'title' => config('api-inspector.title') ?? 'Laravel API Inspector',
                'version' => LaravelApiInspector::version(),
                'routes' => $routes,
                'groupBy' => $groupBy,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display Postman collection JSON
     */
    public function postman(Request $request)
    {
        try {
            $docsPath = config('api-inspector.response_path');
            $storagePath = config('api-inspector.storage_path', 'storage');
            $file = "$docsPath/postman_collection.json";

            // If file doesn't exist, generate it
            // if (! file_exists($file)) {
            $this->generatePostmanCollection($docsPath, $storagePath, $request);
            // }

            if (! file_exists($file)) {
                return response()->json(['error' => 'Failed to generate Postman collection'], 500);
            }

            return response()->file($file, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="postman_collection.json"',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate Postman collection
     */
    protected function generatePostmanCollection(string $outputPath, string $storagePath, Request $request): void
    {
        try {
            // Create directory if it doesn't exist
            if (! is_dir($outputPath)) {
                mkdir($outputPath, 0755, true);
            }

            // Extract routes
            // $routes = RouteExtractor::extract();
            [$routes] = $this->service->apiListData($request);

            // Generate Postman collection
            $generator = new PostmanGenerator(
                $routes,
                config('app.name').' API',
                config('app.url') ?? 'https://api.example.com'
            );

            $collection = $generator->generate();

            // Save to file
            $fileWriter = new JsonFileWriter;

            if ($storagePath === 'local') {
                $outputPath = public_path($outputPath);
            } else {
                $outputPath = storage_path("app/public/{$outputPath}");
            }
            $filePath = "$outputPath/postman_collection.json";
            file_put_contents($filePath, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            \Log::error('Failed to generate Postman collection: '.$e->getMessage());
        }
    }

    /**
     * Display OpenAPI specification
     */
    public function openapi()
    {
        try {
            $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
            $file = "$docsPath/openapi.json";

            // If file doesn't exist, generate it
            if (! file_exists($file)) {
                $this->generateOpenApiSpec($docsPath);
            }

            if (! file_exists($file)) {
                return response()->json(['error' => 'Failed to generate OpenAPI specification'], 500);
            }

            return response()->file($file, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="openapi.json"',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate OpenAPI specification
     */
    protected function generateOpenApiSpec(string $outputPath): void
    {
        try {
            // Create directory if it doesn't exist
            if (! is_dir($outputPath)) {
                mkdir($outputPath, 0755, true);
            }

            // Extract routes
            $routes = RouteExtractor::extract();

            // Generate OpenAPI spec
            $generator = new OpenApiGenerator(
                $routes,
                config('app.name').' API',
                config('app.version', '1.0.0'),
                config('app.url') ?? 'https://api.example.com'
            );

            $spec = $generator->generate();

            // Save to file
            $filePath = "$outputPath/openapi.json";
            file_put_contents($filePath, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            \Log::error('Failed to generate OpenAPI specification: '.$e->getMessage());
        }
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
            $responseStatus = $request->input('status');
            $timestamp = $request->input('timestamp', now()->toIso8601String());

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'route_uri and route_method are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');

            if ($driver === 'json') {
                return $this->service->saveResponseToJson($routeUri, $routeMethod, $responseData, $responseStatus, $timestamp);
            } else {
                return $this->service->saveResponseToCache($routeUri, $routeMethod, $responseData, $responseStatus, $timestamp);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            $storagePath = config('api-inspector.storage_path', 'storage');
            $responses = [];

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path');
                $fileName = md5($routeMethod.':'.$routeUri).'.json';

                if ($storagePath === 'local') {
                    // Save to public folder (root public)
                    $responsePath = public_path($responsePath);
                } else {
                    // Save to storage/public folder (default: 'storage')
                    $responsePath = storage_path("app/public/{$responsePath}");
                }

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

    /**
     * Delete a saved API response
     */
    public function deleteResponse(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->input('route_uri');
            $routeMethod = $request->input('route_method');
            $index = $request->input('index');

            if (! $routeUri || ! $routeMethod || $index === null) {
                return response()->json(['error' => 'route_uri, route_method, and index are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');
            $storagePath = config('api-inspector.storage_path', 'storage');

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path');

                if ($storagePath === 'local') {
                    // Save to public folder (root public)
                    $responsePath = public_path($responsePath);
                } else {
                    // Save to storage/public folder (default: 'storage')
                    $responsePath = storage_path("app/public/{$responsePath}");
                }

                $fileName = md5($routeMethod.':'.$routeUri).'.json';
                $filePath = $responsePath.'/responses/'.$fileName;

                if (file_exists($filePath)) {
                    $responses = json_decode(file_get_contents($filePath), true) ?? [];

                    // Remove response at the given index
                    if (isset($responses[$index])) {
                        unset($responses[$index]);
                        // Re-index array
                        $responses = array_values($responses);

                        // Save updated responses back to file
                        file_put_contents($filePath, json_encode($responses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                        return response()->json([
                            'message' => 'Response deleted successfully',
                            'uri' => $routeUri,
                            'method' => $routeMethod,
                            'index' => $index,
                        ]);
                    }

                    return response()->json(['error' => 'Response index not found'], 404);
                }

                return response()->json(['error' => 'No saved responses found for this route'], 404);
            } else {
                // Delete from cache
                $cacheKey = 'api-inspector-response:'.md5($routeMethod.':'.$routeUri);
                $responses = Cache::get($cacheKey, []);

                if (isset($responses[$index])) {
                    unset($responses[$index]);
                    // Re-index array
                    $responses = array_values($responses);

                    // Update cache
                    Cache::put($cacheKey, $responses, now()->addDays(7));

                    return response()->json([
                        'message' => 'Response deleted successfully',
                        'uri' => $routeUri,
                        'method' => $routeMethod,
                        'index' => $index,
                    ]);
                }

                return response()->json(['error' => 'Response index not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save request body example
     */
    public function saveRequestExample(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->input('route_uri');
            $routeMethod = $request->input('route_method');
            $body = $request->input('body');
            $name = $request->input('name', 'Example');
            $timestamp = now()->toIso8601String();

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'route_uri and route_method are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');
            $storagePath = config('api-inspector.storage_path', 'storage');

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path');

                if ($storagePath === 'local') {
                    $responsePath = public_path($responsePath);
                } else {
                    $responsePath = storage_path("app/public/{$responsePath}");
                }

                @mkdir($responsePath.'/examples', 0755, true);
                $fileName = md5($routeMethod.':'.$routeUri).'.json';
                $filePath = $responsePath.'/examples/'.$fileName;

                $examples = [];
                if (file_exists($filePath)) {
                    $examples = json_decode(file_get_contents($filePath), true) ?? [];
                }

                $examples[] = [
                    'name' => $name,
                    'body' => $body,
                    'timestamp' => $timestamp,
                ];

                file_put_contents($filePath, json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return response()->json([
                    'message' => 'Example saved successfully',
                    'uri' => $routeUri,
                    'method' => $routeMethod,
                ]);
            } else {
                $cacheKey = 'api-inspector-example:'.md5($routeMethod.':'.$routeUri);
                $examples = Cache::get($cacheKey, []);

                $examples[] = [
                    'name' => $name,
                    'body' => $body,
                    'timestamp' => $timestamp,
                ];

                Cache::put($cacheKey, $examples, now()->addDays(30));

                return response()->json([
                    'message' => 'Example saved successfully',
                    'uri' => $routeUri,
                    'method' => $routeMethod,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get saved request examples
     */
    public function getRequestExamples(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->query('uri');
            $routeMethod = $request->query('method');

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'uri and method query parameters are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');
            $storagePath = config('api-inspector.storage_path', 'storage');
            $examples = [];

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path');

                if ($storagePath === 'local') {
                    $responsePath = public_path($responsePath);
                } else {
                    $responsePath = storage_path("app/public/{$responsePath}");
                }

                $fileName = md5($routeMethod.':'.$routeUri).'.json';
                $filePath = $responsePath.'/examples/'.$fileName;

                if (file_exists($filePath)) {
                    $examples = json_decode(file_get_contents($filePath), true) ?? [];
                }
            } else {
                $cacheKey = 'api-inspector-example:'.md5($routeMethod.':'.$routeUri);
                $examples = Cache::get($cacheKey, []);
            }

            return response()->json([
                'uri' => $routeUri,
                'method' => $routeMethod,
                'examples' => $examples,
                'count' => count($examples),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a saved request example
     */
    public function deleteRequestExample(Request $request): JsonResponse
    {
        try {
            $routeUri = $request->input('route_uri');
            $routeMethod = $request->input('route_method');
            $index = $request->input('index');

            if (! $routeUri || ! $routeMethod || $index === null) {
                return response()->json(['error' => 'route_uri, route_method, and index are required'], 400);
            }

            $driver = config('api-inspector.save_responses_driver', 'cache');
            $storagePath = config('api-inspector.storage_path', 'storage');

            if ($driver === 'json') {
                $responsePath = config('api-inspector.response_path');

                if ($storagePath === 'local') {
                    $responsePath = public_path($responsePath);
                } else {
                    $responsePath = storage_path("app/public/{$responsePath}");
                }

                $fileName = md5($routeMethod.':'.$routeUri).'.json';
                $filePath = $responsePath.'/examples/'.$fileName;

                if (file_exists($filePath)) {
                    $examples = json_decode(file_get_contents($filePath), true) ?? [];

                    if (isset($examples[$index])) {
                        unset($examples[$index]);
                        $examples = array_values($examples);

                        file_put_contents($filePath, json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                        return response()->json([
                            'message' => 'Example deleted successfully',
                            'uri' => $routeUri,
                            'method' => $routeMethod,
                            'index' => $index,
                        ]);
                    }

                    return response()->json(['error' => 'Example index not found'], 404);
                }

                return response()->json(['error' => 'No saved examples found for this route'], 404);
            } else {
                $cacheKey = 'api-inspector-example:'.md5($routeMethod.':'.$routeUri);
                $examples = Cache::get($cacheKey, []);

                if (isset($examples[$index])) {
                    unset($examples[$index]);
                    $examples = array_values($examples);

                    Cache::put($cacheKey, $examples, now()->addDays(30));

                    return response()->json([
                        'message' => 'Example deleted successfully',
                        'uri' => $routeUri,
                        'method' => $routeMethod,
                        'index' => $index,
                    ]);
                }

                return response()->json(['error' => 'Example index not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
