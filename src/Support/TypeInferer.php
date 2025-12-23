<?php

namespace Irabbi360\LaravelApiInspector\Support;

class TypeInferer
{
    /**
     * Infer example value based on parameter name and type
     */
    public static function inferExample(string $fieldName, string $type = ''): mixed
    {
        // Type-based inference
        if ($type) {
            return match ($type) {
                'int', 'integer' => 1,
                'float', 'double' => 1.5,
                'bool', 'boolean' => true,
                'array' => [],
                'object' => (object) [],
                default => '',
            };
        }

        // Name-based inference
        return match (true) {
            str_contains($fieldName, 'email') => 'user@example.com',
            str_contains($fieldName, 'password') => 'password123',
            str_contains($fieldName, 'phone') => '1234567890',
            str_contains($fieldName, 'url') || str_contains($fieldName, 'link') => 'https://example.com',
            str_contains($fieldName, 'date') => now()->toDateString(),
            str_contains($fieldName, 'time') => now()->toTimeString(),
            str_contains($fieldName, 'id') => 1,
            str_contains($fieldName, 'count') || str_contains($fieldName, 'number') => 1,
            str_contains($fieldName, 'active') || str_contains($fieldName, 'enabled') => true,
            str_contains($fieldName, 'description') || str_contains($fieldName, 'content') => 'Sample content',
            str_contains($fieldName, 'name') => 'John Doe',
            default => 'example',
        };
    }

    /**
     * Convert Laravel validation rule to OpenAPI/JSON schema type
     */
    public static function ruleToType(string $rule): string
    {
        return match ($rule) {
            'numeric', 'integer', 'int' => 'integer',
            'number', 'float', 'double' => 'number',
            'boolean', 'bool' => 'boolean',
            'array' => 'array',
            'file', 'image', 'mimes' => 'string',
            'date', 'date_format' => 'string',
            'email' => 'string',
            'url' => 'string',
            'ip', 'ipv4', 'ipv6' => 'string',
            default => 'string',
        };
    }

    /**
     * Get OpenAPI format for a type
     */
    public static function getFormat(string $rule): ?string
    {
        return match ($rule) {
            'email' => 'email',
            'date', 'date_format' => 'date',
            'file' => 'binary',
            'image' => 'binary',
            'url' => 'uri',
            'ipv4' => 'ipv4',
            'ipv6' => 'ipv6',
            'uuid' => 'uuid',
            'json' => 'json',
            default => null,
        };
    }

    /**
     * Infer if a field is required based on validation rules
     */
    public static function isRequired(string $rules): bool
    {
        $ruleArray = explode('|', $rules);

        return in_array('required', $ruleArray);
    }

    /**
     * Extract min/max constraints from rules
     */
    public static function extractConstraints(string $rules): array
    {
        $constraints = [];
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            if (str_starts_with($rule, 'min:')) {
                $constraints['min'] = (int) str_replace('min:', '', $rule);
            } elseif (str_starts_with($rule, 'max:')) {
                $constraints['max'] = (int) str_replace('max:', '', $rule);
            } elseif (str_starts_with($rule, 'size:')) {
                $constraints['size'] = (int) str_replace('size:', '', $rule);
            } elseif (str_starts_with($rule, 'regex:')) {
                $constraints['pattern'] = str_replace('regex:', '', $rule);
            } elseif (str_starts_with($rule, 'in:')) {
                $enum = str_replace('in:', '', $rule);
                $constraints['enum'] = explode(',', $enum);
            }
        }

        return $constraints;
    }
}
