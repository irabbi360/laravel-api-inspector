<?php

namespace Irabbi360\LaravelApiInspector\Extractors;

use ReflectionClass;

class WebhookExtractor
{
    /**
     * Extract webhook definitions from config or attributes
     */
    public function extract(): array
    {
        $webhooks = [];

        // Check for webhooks config
        $webhooksConfig = config('api-inspector.webhooks', []);

        foreach ($webhooksConfig as $name => $webhook) {
            $webhooks[] = $this->formatWebhook($name, $webhook);
        }

        // Also scan for webhook classes with attributes
        $webhooks = array_merge($webhooks, $this->scanWebhookClasses());

        return $webhooks;
    }

    /**
     * Format webhook data
     */
    protected function formatWebhook(string $name, array $webhook): array
    {
        return [
            'name' => $name,
            'event' => $webhook['event'] ?? $name,
            'description' => $webhook['description'] ?? '',
            'url' => $webhook['url'] ?? '',
            'method' => $webhook['method'] ?? 'POST',
            'headers' => $webhook['headers'] ?? ['Content-Type' => 'application/json'],
            'payload_schema' => $webhook['payload'] ?? [],
            'response_schema' => $webhook['response'] ?? [],
            'examples' => $webhook['examples'] ?? [],
            'retry_policy' => $webhook['retry'] ?? [
                'max_attempts' => 3,
                'backoff' => 'exponential',
                'initial_delay' => 1000,
            ],
            'status' => $webhook['active'] ?? true,
        ];
    }

    /**
     * Scan for webhook classes
     */
    protected function scanWebhookClasses(): array
    {
        $webhooks = [];
        $webhookDir = app_path('Webhooks');

        if (! is_dir($webhookDir)) {
            return $webhooks;
        }

        $files = scandir($webhookDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (! str_ends_with($file, '.php')) {
                continue;
            }

            $className = 'App\\Webhooks\\'.str_replace('.php', '', $file);

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $webhooks[] = $this->extractFromClass($reflection);
            } catch (\Exception $e) {
                // Skip invalid classes
            }
        }

        return $webhooks;
    }

    /**
     * Extract webhook from class
     */
    protected function extractFromClass(ReflectionClass $reflection): array
    {
        $docblock = $reflection->getDocComment() ?: '';

        return [
            'name' => $reflection->getShortName(),
            'event' => $this->extractDocValue($docblock, 'event', $reflection->getShortName()),
            'description' => $this->extractDocValue($docblock, 'description', ''),
            'url' => $this->extractDocValue($docblock, 'url', ''),
            'method' => $this->extractDocValue($docblock, 'method', 'POST'),
            'headers' => ['Content-Type' => 'application/json'],
            'payload_schema' => $this->extractPayloadSchema($reflection),
            'response_schema' => $this->extractResponseSchema($reflection),
            'examples' => $this->extractExamples($reflection),
            'retry_policy' => [
                'max_attempts' => 3,
                'backoff' => 'exponential',
                'initial_delay' => 1000,
            ],
            'status' => true,
        ];
    }

    /**
     * Extract payload schema from class
     */
    protected function extractPayloadSchema(ReflectionClass $reflection): array
    {
        $method = $reflection->hasMethod('getPayload')
            ? $reflection->getMethod('getPayload')
            : null;

        if (! $method) {
            return [];
        }

        try {
            $instance = $reflection->newInstance();
            $payload = $instance->getPayload();

            return is_array($payload) ? $payload : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract response schema from class
     */
    protected function extractResponseSchema(ReflectionClass $reflection): array
    {
        $method = $reflection->hasMethod('getResponse')
            ? $reflection->getMethod('getResponse')
            : null;

        if (! $method) {
            return [];
        }

        try {
            $instance = $reflection->newInstance();
            $response = $instance->getResponse();

            return is_array($response) ? $response : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract examples from class
     */
    protected function extractExamples(ReflectionClass $reflection): array
    {
        $method = $reflection->hasMethod('getExamples')
            ? $reflection->getMethod('getExamples')
            : null;

        if (! $method) {
            return [];
        }

        try {
            $instance = $reflection->newInstance();
            $examples = $instance->getExamples();

            return is_array($examples) ? $examples : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Extract value from docblock
     */
    protected function extractDocValue(string $docblock, string $key, string $default): string
    {
        $pattern = "/@{$key}\s+(.+?)(?:\n|$)/i";

        if (preg_match($pattern, $docblock, $matches)) {
            return trim($matches[1]);
        }

        return $default;
    }
}
