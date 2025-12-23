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

            // Extract field names
            preg_match_all('/[\'"]?(\w+)[\'"]?\s*=>\s*/', $arrayContent, $fields);

            if (! empty($fields[1])) {
                $structure = [];
                foreach ($fields[1] as $field) {
                    $structure[$field] = [
                        'type' => 'string',
                        'example' => ucfirst($field),
                    ];
                }

                return $structure;
            }
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
