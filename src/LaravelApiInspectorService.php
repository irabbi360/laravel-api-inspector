<?php

namespace Irabbi360\LaravelApiInspector;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LaravelApiInspectorService
{

    /**
     * Extract route parameters from URI
     */
    public function extractRouteParameters(string $uri): array
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
    public function generateExampleRequestRules(string $controllerName): array
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
    public function shouldSkipRoute($route): bool
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
    public function getRouteDescription($route): string
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
    public function requiresAuth($route): bool
    {
        $middleware = $route->middleware() ?? [];

        return in_array('auth', $middleware) || in_array('auth:api', $middleware) || in_array('auth:sanctum', $middleware);
    }

    /**
     * Save response to JSON file
     */
    public function saveResponseToJson(string $routeUri, string $routeMethod, array $responseData, string $timestamp): JsonResponse
    {
        $responsePath = config('api-inspector.response_path');
        $storagePath = config('api-inspector.storage_path', 'storage');
        $fileName = md5($routeMethod.':'.$routeUri).'.json';
        if ($storagePath === 'local') {
            // Save to public folder (root public)
            $responsePath = public_path($responsePath);
        } else {
            // Save to storage/public folder (default: 'storage')
            $responsePath = storage_path("app/public/{$responsePath}");
        }
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
     * Save response to cache
     */
    public function saveResponseToCache(string $routeUri, string $routeMethod, array $responseData, string $timestamp): JsonResponse
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
     * Extract response schema from Resource
     */
    public function extractResponseSchema($route): ?array
    {
        try {
            $controllerName = $route->getActionName();
            if (! $controllerName || $controllerName === 'Closure') {
                return null;
            }

            // Parse controller@method format
            if (! str_contains($controllerName, '@')) {
                return null;
            }

            [$controller, $method] = explode('@', $controllerName);

            if (! class_exists($controller)) {
                return null;
            }

            $reflection = new \ReflectionClass($controller);
            if (! $reflection->hasMethod($method)) {
                return null;
            }

            $controllerMethod = $reflection->getMethod($method);

            // First, check for @LAPIresponsesSchema annotation
            $resourceClass = $this->extractResourceFromDocBlock($controllerMethod);

            if ($resourceClass) {
                return $this->extractResourceSchemaRecursively($resourceClass);
            }

            // Fallback: check return type
            $returnType = $controllerMethod->getReturnType();

            if (! $returnType) {
                return null;
            }

            // Get the return type name safely
            $returnTypeName = (string) $returnType;

            // Check if it's an Illuminate\Http\Resources\Json\JsonResource
            if (class_exists($returnTypeName) && is_subclass_of($returnTypeName, \Illuminate\Http\Resources\Json\JsonResource::class)) {
                // Extract resource schema recursively
                return $this->extractResourceSchemaRecursively($returnTypeName);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractResourceFromDocBlock(ReflectionMethod $method): ?string
    {
        $docComment = $method->getDocComment();

        if (! $docComment) {
            return null;
        }

        if (! preg_match('/@LAPIresponsesSchema\s+([^\s\*]+)/', $docComment, $matches)) {
            return null;
        }

        $resourceClass = trim($matches[1]);

        // Remove trailing symbols
        $resourceClass = trim($resourceClass, " \t\n\r\0\x0B*/");

        // Fully-qualified class name (starts with \)
        if (\Str::startsWith($resourceClass, '\\')) {
            $resourceClass = ltrim($resourceClass, '\\');

            if (class_exists($resourceClass)) {
                return $resourceClass;
            }
        }

        // Check if class exists as-is (already fully qualified without leading \)
        if (class_exists($resourceClass)) {
            return $resourceClass;
        }

        $declaringClass = new ReflectionClass($method->getDeclaringClass()->getName());

        // Check imported "use" statements in the declaring class file
        $fileName = $declaringClass->getFileName();
        if ($fileName && file_exists($fileName)) {
            $fileContent = file_get_contents($fileName);

            // Extract use statements
            preg_match_all('/^use\s+(.+?);/m', $fileContent, $useMatches);

            foreach ($useMatches[1] as $useStatement) {
                $useStatement = trim($useStatement);

                // Check for aliased imports: use Foo\Bar as Baz
                if (preg_match('/(.+)\s+as\s+(\w+)$/', $useStatement, $aliasMatch)) {
                    if ($aliasMatch[2] === $resourceClass) {
                        return trim($aliasMatch[1]);
                    }
                }

                // Check for direct match
                if (\Str::endsWith($useStatement, '\\'.$resourceClass)) {
                    return $useStatement;
                }
            }
        }

        // Try common namespace patterns
        $appNamespace = app()->getNamespace();

        $guesses = [
            $appNamespace.'Http\\Resources\\'.$resourceClass,
            $declaringClass->getNamespaceName().'\\'.$resourceClass,
            $appNamespace.'Resources\\'.$resourceClass,
        ];

        foreach ($guesses as $guess) {
            if (class_exists($guess)) {
                return $guess;
            }
        }

        return null;
    }

    /**
     * Recursively extract resource schema including nested resources
     */
    public function extractResourceSchemaRecursively(string $resourceClass, int $depth = 0): ?array
    {
        // Prevent infinite recursion
        if ($depth > 5) {
            return null;
        }

        $schema = \Irabbi360\LaravelApiInspector\Extractors\ResourceExtractor::extract($resourceClass);

        if (! $schema) {
            return null;
        }

        // Format the schema to show proper types
        $formattedSchema = [];
        foreach ($schema as $fieldName => $field) {
            $formattedSchema[$fieldName] = $this->formatFieldType($field, $resourceClass, $depth);
        }

        return [
            'resource_class' => $resourceClass,
            'schema' => $formattedSchema,
        ];
    }

    /**
     * Format field type information
     */
    private function formatFieldType($field, string $parentResourceClass, int $depth)
    {
        // Handle nested resources detected by ResourceExtractor
        if (isset($field['type']) && $field['type'] === 'nested_resource' && isset($field['resource_class'])) {
            $nestedResourceClass = $field['resource_class'];
            $resourceType = $field['resource_type'] ?? 'object'; // 'object' or 'collection'

            // Resolve the full namespace if not already fully qualified
            $resolvedClass = $this->resolveResourceNamespace($nestedResourceClass, $parentResourceClass);

            // Recursively extract the nested resource schema
            $nestedSchema = $this->extractResourceSchemaRecursively($resolvedClass, $depth + 1);
            if ($nestedSchema) {
                // Add resource type information
                $nestedSchema['resource_type'] = $resourceType;

                return $nestedSchema;
            }
        }

        // If it's a nested array/collection, check if it's a resource
        if (isset($field['nested']) && is_array($field['nested'])) {
            // Try to determine the nested resource class
            $nestedSchema = $this->detectNestedResourceSchema($field['nested'], $parentResourceClass, $depth);
            if ($nestedSchema) {
                return $nestedSchema;
            }

            return 'object';
        }

        // Return the type or 'mixed' as default
        $type = $field['type'] ?? 'mixed';

        // Check if it can be null
        if (isset($field['nullable']) && $field['nullable']) {
            return $type.' | null';
        }

        return $type;
    }

    /**
     * Detect nested resource schema
     */
    private function detectNestedResourceSchema(array $example, string $parentResourceClass, int $depth): ?array
    {
        // If depth is too high, just return object
        if ($depth >= 4) {
            return null;
        }

        $formattedExample = [];
        foreach ($example as $key => $value) {
            if (is_array($value)) {
                $formattedExample[$key] = 'object | null';
            } else {
                $formattedExample[$key] = 'mixed';
            }
        }

        return $formattedExample;
    }

    /**
     * Resolve the full namespace of a nested resource class
     */
    private function resolveResourceNamespace(string $resourceClass, string $parentResourceClass): string
    {
        // If already fully qualified (starts with \ or has namespace), return as-is
        if (strpos($resourceClass, '\\') === 0 || strpos($resourceClass, '\\') !== false) {
            $fullyQualified = ltrim($resourceClass, '\\');
            if (class_exists($fullyQualified)) {
                return $fullyQualified;
            }
        }

        // Get the namespace from the parent resource class
        $parentNamespace = '';
        if (strpos($parentResourceClass, '\\') !== false) {
            $parentNamespace = substr($parentResourceClass, 0, strrpos($parentResourceClass, '\\'));
        }

        // Try to find the resource in the same namespace as parent
        if ($parentNamespace) {
            $candidateClass = $parentNamespace.'\\'.$resourceClass;
            if (class_exists($candidateClass)) {
                return $candidateClass;
            }
        }

        // Try common resource namespace patterns with multi-level folder support
        $commonNamespaces = [
            'App\\Http\\Resources',
            'App\\Resources',
        ];

        foreach ($commonNamespaces as $baseNamespace) {
            // Try direct match: App\Http\Resources\ProfileResource
            $candidateClass = $baseNamespace.'\\'.$resourceClass;
            if (class_exists($candidateClass)) {
                return $candidateClass;
            }

            // Try searching in subdirectories recursively
            $candidateClass = $this->searchInSubdirectories($baseNamespace, $resourceClass);
            if ($candidateClass) {
                return $candidateClass;
            }
        }

        // If all resolution attempts fail, return original class name
        // ResourceExtractor::extract() will handle the error gracefully
        return $resourceClass;
    }

    /**
     * Search for a class in subdirectories of a base namespace
     */
    private function searchInSubdirectories(string $baseNamespace, string $className): ?string
    {
        // Convert namespace to directory path
        $basePath = base_path(str_replace('\\', '/', str_replace('App\\', 'app/', $baseNamespace)));

        if (! is_dir($basePath)) {
            return null;
        }

        // Get all PHP files recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filename = $file->getBasename('.php');

                // Check if filename matches the class name
                if ($filename === $className) {
                    // Build the namespace from the file path
                    $relativePath = str_replace($basePath, '', $file->getPath());
                    $subNamespace = str_replace('/', '\\', trim($relativePath, '/'));

                    $candidateClass = $baseNamespace;
                    if ($subNamespace) {
                        $candidateClass .= '\\'.$subNamespace;
                    }
                    $candidateClass .= '\\'.$className;

                    if (class_exists($candidateClass)) {
                        return $candidateClass;
                    }
                }
            }
        }

        return null;
    }
}