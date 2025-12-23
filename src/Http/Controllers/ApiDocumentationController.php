<?php

namespace Irabbi360\LaravelApiInspector\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class ApiDocumentationController extends Controller
{
    /**
     * Display API documentation
     */
    public function index()
    {
        $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
        $htmlFile = "$docsPath/index.html";

        if (! file_exists($htmlFile)) {
            return response()->view('laravel-api-inspector::missing-docs', [], 404);
        }

        return response()->file($htmlFile);
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
            $responseData = $request->input('response_data', []);
            $timestamp = $request->input('timestamp', now()->toIso8601String());

            if (! $routeUri || ! $routeMethod) {
                return response()->json(['error' => 'route_uri and route_method are required'], 400);
            }

            // Store in cache with a key based on route
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

            $cacheKey = 'api-inspector-response:'.md5($routeMethod.':'.$routeUri);
            $responses = Cache::get($cacheKey, []);

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
