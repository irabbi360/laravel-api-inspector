<?php

namespace Irabbi360\LaravelApiInspector\Generators;

class PostmanGenerator
{
    protected array $routes;

    protected string $name;

    protected string $baseUrl;

    public function __construct(array $routes, string $name = 'Laravel API Inspector', string $baseUrl = '{{base_url}}')
    {
        $this->routes = $routes;
        $this->name = $name;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Generate Postman collection
     */
    public function generate(): array
    {
        return [
            'info' => [
                'name' => $this->name,
                'description' => 'Auto-generated '.config('api-inspector.title').' Postman collection',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $this->generateItems(),
            'variable' => $this->generateVariables(),
        ];
    }

    /**
     * Generate Postman collection items from routes
     */
    protected function generateItems(): array
    {
        $items = [];

        foreach ($this->routes as $route) {
            $items[] = $this->generateItem($route);
        }

        return $items;
    }

    /**
     * Generate single Postman item
     */
    protected function generateItem(array $route): array
    {
        $method = $route['http_method'] ?? $route['method'] ?? 'GET';
        
        return [
            'name' => $route['description'] ?? $route['uri'],
            'request' => [
                'method' => $method,
                'header' => $this->generateHeaders($route),
                'body' => $this->generateBody($route),
                'url' => [
                    'raw' => $this->generateUrl($route),
                    'host' => ['{{base_url}}'],
                    'path' => $this->parsePath($route['uri']),
                    'query' => $this->generateQueryParams($route),
                ],
                'description' => $route['description'] ?? '',
            ],
            'response' => [],
        ];
    }

    /**
     * Generate request headers
     */
    protected function generateHeaders(array $route): array
    {
        $headers = [
            [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'type' => 'text',
            ],
            [
                'key' => 'Accept',
                'value' => 'application/json',
                'type' => 'text',
            ],
        ];

        // Add auth header if route requires auth
        if ($route['requires_auth'] ?? false) {
            $headers[] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{token}}',
                'type' => 'text',
            ];
        }

        return $headers;
    }

    /**
     * Generate request body
     */
    protected function generateBody(array $route): ?array
    {
        $method = $route['http_method'] ?? $route['method'] ?? 'GET';
        
        if (! in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return null;
        }

        $bodyData = [];

        // Add request rules as body schema
        if (! empty($route['request_rules'])) {
            foreach ($route['request_rules'] as $fieldName => $field) {
                $bodyData[$fieldName] = $field['example'] ?? '';
            }
        }

        return [
            'mode' => 'raw',
            'raw' => json_encode($bodyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'options' => [
                'raw' => [
                    'language' => 'json',
                ],
            ],
        ];
    }

    /**
     * Generate query parameters
     */
    protected function generateQueryParams(array $route): array
    {
        $params = [];

        // Use 'parameters' field from service (path parameters extracted from URI)
        if (! empty($route['parameters'])) {
            foreach ($route['parameters'] as $paramName => $param) {
                $params[] = [
                    'key' => $paramName,
                    'value' => $param['example'] ?? '',
                    'description' => $param['description'] ?? 'Path parameter',
                    'disabled' => false,
                ];
            }
        }

        return $params;
    }

    /**
     * Generate full URL
     */
    protected function generateUrl(array $route): string
    {
        $uri = $route['uri'];

        // Replace Laravel route parameters with Postman variables
        $uri = preg_replace('/\{(\w+)\}/', ':$1', $uri);

        return "{{base_url}}/$uri";
    }

    /**
     * Parse URI path segments
     */
    protected function parsePath(string $uri): array
    {
        return array_filter(explode('/', trim($uri, '/')));
    }

    /**
     * Generate Postman variables
     */
    protected function generateVariables(): array
    {
        return [
            [
                'key' => 'base_url',
                'value' => $this->baseUrl,
                'type' => 'string',
            ],
            [
                'key' => 'token',
                'value' => '',
                'type' => 'string',
            ],
        ];
    }

    /**
     * Generate as JSON string
     */
    public function generateJson(): string
    {
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
