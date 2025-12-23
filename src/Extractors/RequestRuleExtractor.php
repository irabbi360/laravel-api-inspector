<?php

namespace Irabbi360\LaravelApiInspector\Extractors;

use Irabbi360\LaravelApiInspector\Support\ReflectionHelper;
use Irabbi360\LaravelApiInspector\Support\RuleParser;

class RequestRuleExtractor
{
    /**
     * Extract request rules from a controller method
     *
     * @return array<string, array>
     */
    public static function extract(string $controller): array
    {
        $parsed = ReflectionHelper::parseControllerString($controller);

        if (! $parsed) {
            return [];
        }

        return self::extractFromMethod($parsed['class'], $parsed['method']);
    }

    /**
     * Extract rules from a controller method
     *
     * @return array<string, array>
     */
    public static function extractFromMethod(string $class, string $method): array
    {
        $reflectionMethod = ReflectionHelper::getMethod($class, $method);

        if (! $reflectionMethod) {
            return [];
        }

        // Check for FormRequest parameter
        $formRequestClass = ReflectionHelper::hasFormRequestParameter($reflectionMethod);

        if ($formRequestClass) {
            return self::extractFromFormRequest($formRequestClass);
        }

        return [];
    }

    /**
     * Extract rules from a FormRequest class
     *
     * @return array<string, array>
     */
    public static function extractFromFormRequest(string $formRequestClass): array
    {
        if (! class_exists($formRequestClass)) {
            return [];
        }

        try {
            $instance = new $formRequestClass;

            if (method_exists($instance, 'rules')) {
                $rules = $instance->rules();

                return RuleParser::parse($rules);
            }
        } catch (\Exception) {
            // Silently fail if we can't instantiate
        }

        return [];
    }

    /**
     * Extract query parameters from route URI
     *
     * @return array<string, array>
     */
    public static function extractQueryParameters(string $uri): array
    {
        $parameters = [];

        // Common query parameters
        $commonParams = [
            'page' => [
                'type' => 'integer',
                'description' => 'Page number',
                'example' => 1,
                'required' => false,
            ],
            'per_page' => [
                'type' => 'integer',
                'description' => 'Number of items per page',
                'example' => 15,
                'required' => false,
            ],
            'search' => [
                'type' => 'string',
                'description' => 'Search query',
                'example' => 'search term',
                'required' => false,
            ],
            'sort' => [
                'type' => 'string',
                'description' => 'Sort field',
                'example' => 'created_at',
                'required' => false,
            ],
            'filter' => [
                'type' => 'object',
                'description' => 'Filter criteria',
                'example' => [],
                'required' => false,
            ],
        ];

        // Add common params for index routes
        if (str_ends_with($uri, 'index') || str_contains($uri, 'list')) {
            $parameters = $commonParams;
        }

        return $parameters;
    }
}
