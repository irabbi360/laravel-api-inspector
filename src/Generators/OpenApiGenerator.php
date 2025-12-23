<?php

namespace Irabbi360\LaravelApiInspector\Generators;

class OpenApiGenerator
{
    protected array $routes;

    protected string $title;

    protected string $version;

    protected string $baseUrl;

    public function __construct(
        array $routes,
        string $title = 'Laravel API Inspector',
        string $version = '1.0.0',
        string $baseUrl = 'http://localhost'
    ) {
        $this->routes = $routes;
        $this->title = $title;
        $this->version = $version;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Generate OpenAPI specification
     */
    public function generate(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->title,
                'version' => $this->version,
                'description' => 'Auto-generated API documentation',
            ],
            'servers' => [
                [
                    'url' => $this->baseUrl,
                    'description' => 'API Server',
                ],
            ],
            'paths' => $this->generatePaths(),
            'components' => $this->generateComponents(),
        ];
    }

    /**
     * Generate OpenAPI paths
     */
    protected function generatePaths(): array
    {
        $paths = [];

        foreach ($this->routes as $route) {
            $path = $route['uri'];
            $method = strtolower($route['method']);

            if (! isset($paths[$path])) {
                $paths[$path] = [];
            }

            $paths[$path][$method] = $this->generateOperation($route);
        }

        return $paths;
    }

    /**
     * Generate OpenAPI operation
     */
    protected function generateOperation(array $route): array
    {
        $operation = [
            'summary' => $route['description'] ?? 'API endpoint',
            'tags' => [$this->extractTag($route['uri'])],
            'parameters' => $this->generateParameters($route),
            'responses' => $this->generateResponses($route),
        ];

        // Add request body if method supports it
        if (in_array($route['method'], ['POST', 'PUT', 'PATCH'])) {
            $operation['requestBody'] = $this->generateRequestBody($route);
        }

        // Add security if route requires auth
        if ($route['requires_auth'] ?? false) {
            $operation['security'] = [
                ['bearerAuth' => []],
            ];
        }

        return $operation;
    }

    /**
     * Generate parameters
     */
    protected function generateParameters(array $route): array
    {
        $parameters = [];

        // Path parameters
        if (! empty($route['parameters'])) {
            foreach ($route['parameters'] as $paramName => $param) {
                $parameters[] = [
                    'name' => $paramName,
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => $param['type'] ?? 'string',
                    ],
                    'description' => $param['description'] ?? '',
                ];
            }
        }

        // Query parameters
        if (! empty($route['query_parameters'])) {
            foreach ($route['query_parameters'] as $paramName => $param) {
                $parameters[] = [
                    'name' => $paramName,
                    'in' => 'query',
                    'required' => $param['required'] ?? false,
                    'schema' => [
                        'type' => $param['type'] ?? 'string',
                    ],
                    'description' => $param['description'] ?? '',
                ];
            }
        }

        return $parameters;
    }

    /**
     * Generate request body
     */
    protected function generateRequestBody(array $route): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        if (! empty($route['request_rules'])) {
            foreach ($route['request_rules'] as $fieldName => $field) {
                $schema['properties'][$fieldName] = [
                    'type' => $field['type'] ?? 'string',
                    'example' => $field['example'] ?? '',
                    'description' => $field['description'] ?? '',
                ];

                if ($field['required'] ?? false) {
                    $schema['required'][] = $fieldName;
                }
            }
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => $schema,
                ],
            ],
        ];
    }

    /**
     * Generate responses
     */
    protected function generateResponses(array $route): array
    {
        $successStatus = $this->getSuccessStatusCode($route['method']);

        return [
            (string) $successStatus => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean'],
                                'data' => ['type' => 'object'],
                            ],
                        ],
                        'example' => $route['response_example'] ?? self::getDefaultExample($route['method']),
                    ],
                ],
            ],
            '400' => [
                'description' => 'Bad Request',
            ],
            '401' => [
                'description' => 'Unauthorized',
            ],
            '404' => [
                'description' => 'Not Found',
            ],
            '500' => [
                'description' => 'Server Error',
            ],
        ];
    }

    /**
     * Generate components (schemas)
     */
    protected function generateComponents(): array
    {
        return [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
        ];
    }

    /**
     * Extract tag from URI
     */
    protected function extractTag(string $uri): string
    {
        $parts = explode('/', trim($uri, '/'));

        // Skip 'api' if it's the first part
        if ($parts[0] === 'api' && isset($parts[1])) {
            return ucfirst($parts[1]);
        }

        return ucfirst($parts[0]);
    }

    /**
     * Get success status code for HTTP method
     */
    protected function getSuccessStatusCode(string $method): int
    {
        return match ($method) {
            'POST' => 201,
            'DELETE' => 204,
            default => 200,
        };
    }

    /**
     * Get default example for method
     */
    protected static function getDefaultExample(string $method): array
    {
        return match (true) {
            str_contains($method, 'DELETE') => [
                'success' => true,
                'message' => 'Resource deleted successfully',
            ],
            default => [
                'success' => true,
                'data' => [],
            ],
        };
    }

    /**
     * Generate as JSON string
     */
    public function generateJson(): string
    {
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate as YAML string
     */
    public function generateYaml(): string
    {
        // Simple YAML generation for now
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
