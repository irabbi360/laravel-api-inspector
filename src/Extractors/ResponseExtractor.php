<?php

namespace Irabbi360\LaravelApiInspector\Extractors;

use Illuminate\Http\Resources\Json\JsonResource;
use Irabbi360\LaravelApiInspector\Support\ReflectionHelper;

class ResponseExtractor
{
    /**
     * Extract response structure from controller method
     */
    public static function extract(string $controller): ?array
    {
        $parsed = ReflectionHelper::parseControllerString($controller);

        if (! $parsed) {
            return null;
        }

        return self::extractFromMethod($parsed['class'], $parsed['method']);
    }

    /**
     * Extract response from a controller method
     */
    public static function extractFromMethod(string $class, string $method): ?array
    {
        $reflectionMethod = ReflectionHelper::getMethod($class, $method);

        if (! $reflectionMethod) {
            return null;
        }

        // Generate a default response structure
        return [
            'status' => 200,
            'description' => 'Successful response',
            'schema' => self::generateDefaultSchema($method),
            'example' => self::generateDefaultExample($method),
        ];
    }

    /**
     * Generate default schema based on method name
     */
    public static function generateDefaultSchema(string $method): array
    {
        return match (true) {
            str_contains($method, 'index') || str_contains($method, 'list') => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                    'meta' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                            'per_page' => ['type' => 'integer'],
                            'current_page' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
            str_contains($method, 'show') || str_contains($method, 'get') => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'data' => ['type' => 'object'],
                ],
            ],
            str_contains($method, 'store') || str_contains($method, 'create') => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                    'data' => ['type' => 'object'],
                ],
            ],
            str_contains($method, 'update') => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                    'data' => ['type' => 'object'],
                ],
            ],
            str_contains($method, 'delete') || str_contains($method, 'destroy') => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                ],
            ],
            default => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                ],
            ],
        };
    }

    /**
     * Generate example response
     */
    public static function generateDefaultExample(string $method): array
    {
        return match (true) {
            str_contains($method, 'index') || str_contains($method, 'list') => [
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Item 1'],
                    ['id' => 2, 'name' => 'Item 2'],
                ],
                'meta' => [
                    'total' => 2,
                    'per_page' => 15,
                    'current_page' => 1,
                ],
            ],
            str_contains($method, 'show') || str_contains($method, 'get') => [
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => 'Item',
                    'created_at' => now()->toIso8601String(),
                ],
            ],
            str_contains($method, 'store') || str_contains($method, 'create') => [
                'success' => true,
                'message' => 'Resource created successfully',
                'data' => [
                    'id' => 1,
                    'name' => 'New Item',
                    'created_at' => now()->toIso8601String(),
                ],
            ],
            str_contains($method, 'update') => [
                'success' => true,
                'message' => 'Resource updated successfully',
                'data' => [
                    'id' => 1,
                    'name' => 'Updated Item',
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
            str_contains($method, 'delete') || str_contains($method, 'destroy') => [
                'success' => true,
                'message' => 'Resource deleted successfully',
            ],
            default => [
                'success' => true,
                'message' => 'Operation successful',
            ],
        };
    }

    /**
     * Extract response from a JsonResource
     */
    public static function extractFromResource(string $resourceClass): ?array
    {
        if (! class_exists($resourceClass) || ! is_subclass_of($resourceClass, JsonResource::class)) {
            return null;
        }

        try {
            // Create a dummy model instance to pass to resource
            $dummyData = self::createDummyData();
            $resource = new $resourceClass($dummyData);

            $response = $resource->resolve();

            return [
                'status' => 200,
                'description' => 'Resource response',
                'example' => $response,
            ];
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Create dummy data for resource extraction
     */
    protected static function createDummyData(): object
    {
        return (object) [
            'id' => 1,
            'name' => 'Sample Item',
            'email' => 'sample@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
