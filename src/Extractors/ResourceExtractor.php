<?php

namespace Irabbi360\LaravelApiInspector\Extractors;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use ReflectionClass;
use ReflectionMethod;

class ResourceExtractor
{
    /**
     * Extract structure from a JsonResource class
     */
    public static function extract(string $resourceClass): ?array
    {
        if (! class_exists($resourceClass)) {
            return null;
        }

        if (! is_subclass_of($resourceClass, JsonResource::class)) {
            return null;
        }

        try {
            return self::extractFromClass($resourceClass);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Extract resource structure via reflection
     */
    public static function extractFromClass(string $resourceClass): ?array
    {
        $reflection = new ReflectionClass($resourceClass);

        // Check if toArray method exists
        if (! $reflection->hasMethod('toArray')) {
            return null;
        }

        $method = $reflection->getMethod('toArray');

        return self::extractFromToArrayMethod($method, $resourceClass);
    }

    /**
     * Extract properties from toArray method
     */
    public static function extractFromToArrayMethod(ReflectionMethod $method, string $resourceClass): ?array
    {
        try {
            // Try to get the method source code
            $filename = $method->getFileName();
            $startLine = $method->getStartLine() - 1;
            $endLine = $method->getEndLine();

            if (! file_exists($filename)) {
                return self::generateDefaultResourceExample($resourceClass);
            }

            $source = file($filename);
            $code = implode('', array_slice($source, $startLine, $endLine - $startLine));

            // Parse array return statement
            return self::parseArrayFromCode($code);
        } catch (\Exception) {
            return self::generateDefaultResourceExample($resourceClass);
        }
    }

    /**
     * Parse array structure from code
     */
    public static function parseArrayFromCode(string $code): ?array
    {
        // Simple regex to find return [ ... ] patterns
        if (preg_match('/return\s+\[(.*?)\];/s', $code, $matches)) {
            $arrayContent = trim($matches[1]);

            // More robust parsing that handles nested parentheses
            $structure = self::parseArrayWithNestedResources($arrayContent);
            
            return $structure ?: null;
        }

        return null;
    }

    /**
     * Parse array content with support for nested resources
     */
    public static function parseArrayWithNestedResources(string $arrayContent): ?array
    {
        $structure = [];
        $lines = explode("\n", $arrayContent);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line === ',') {
                continue;
            }

            // Remove trailing comma
            $line = rtrim($line, ',');

            // Match: 'fieldName' => value or "fieldName" => value or fieldName => value
            if (preg_match('/^[\'"]?(\w+)[\'"]?\s*=>\s*(.+)$/', $line, $matches)) {
                $fieldName = $matches[1];
                $value = trim($matches[2]);

                // Check if this field contains a resource instantiation
                $nestedResource = self::detectNestedResource($value);

                if ($nestedResource) {
                    $structure[$fieldName] = [
                        'type' => 'nested_resource',
                        'resource_class' => $nestedResource['class'],
                        'resource_type' => $nestedResource['type'], // 'object' or 'collection'
                    ];
                } else {
                    $structure[$fieldName] = [
                        'type' => 'string',
                        'example' => ucfirst($fieldName),
                    ];
                }
            }
        }

        return ! empty($structure) ? $structure : null;
    }

    /**
     * Detect if a value is a resource instantiation
     * Returns array with 'class' and 'type' keys, or null
     */
    public static function detectNestedResource(string $value): ?array
    {
        // Match: new ResourceClass(...)
        if (preg_match('/new\s+([\\\\a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $value, $matches)) {
            $resourceClass = $matches[1];
            // Single object instance
            return [
                'class' => $resourceClass,
                'type' => 'object',
            ];
        }

        // Match: ResourceClass::collection(...) or ::query() or ::paginate()
        if (preg_match('/([\\\\a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*)::(collection|query|paginate)\s*\(/', $value, $matches)) {
            $resourceClass = $matches[1];
            // Collection/array of resources
            return [
                'class' => $resourceClass,
                'type' => 'collection',
            ];
        }

        return null;
    }

    /**
     * Generate default example based on class name
     */
    public static function generateDefaultResourceExample(string $resourceClass): ?array
    {
        // Extract resource name from class name
        $parts = explode('\\', $resourceClass);
        $className = end($parts);
        $resourceName = str_replace('Resource', '', $className);

        return [
            'id' => [
                'type' => 'integer',
                'example' => 1,
            ],
            'name' => [
                'type' => 'string',
                'example' => $resourceName,
            ],
            'created_at' => [
                'type' => 'string',
                'format' => 'date-time',
                'example' => now()->toIso8601String(),
            ],
            'updated_at' => [
                'type' => 'string',
                'format' => 'date-time',
                'example' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Check if resource is a collection
     */
    public static function isCollection(string $resourceClass): bool
    {
        return class_exists($resourceClass) && is_subclass_of($resourceClass, ResourceCollection::class);
    }
}
