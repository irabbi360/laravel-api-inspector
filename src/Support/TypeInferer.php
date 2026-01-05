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

    /**
     * Check if a string is a date value rather than a field name
     */
    private static function isDateValue(string $value): bool
    {
        // Check for date patterns like YYYY-MM-DD, DD/MM/YYYY, etc
        if (preg_match('/^\d{4}-\d{2}-\d{2}|^\d{2}\/\d{2}\/\d{4}/', $value)) {
            return true;
        }
        // Check for time patterns
        if (preg_match('/\d{2}:\d{2}(:\d{2})?/', $value)) {
            return true;
        }

        return false;
    }

    /**
     * Extract relational field information from rules
     * For rules like 'confirmed', 'same:field', 'different:field', 'in_array:field.*'
     *
     * @return array<string, mixed>
     */
    public static function extractRelationalRules(string $rules, string $fieldName): array
    {
        $relational = [];
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            if ($rule === 'confirmed') {
                // 'confirmed' rule requires a {field}_confirmation field
                $relational['confirmed'] = true;
                $relational['requires_field'] = $fieldName.'_confirmation';
                $relational['description_suffix'] = '(must match '.$fieldName.'_confirmation)';
            } elseif (str_starts_with($rule, 'same:')) {
                // 'same:field' rule requires the field to match another field
                $otherField = str_replace('same:', '', $rule);
                $relational['same_as'] = $otherField;
                $relational['description_suffix'] = '(must match '.$otherField.')';
            } elseif (str_starts_with($rule, 'different:')) {
                // 'different:field' rule requires the field to be different from another
                $otherField = str_replace('different:', '', $rule);
                $relational['different_from'] = $otherField;
                $relational['description_suffix'] = '(must be different from '.$otherField.')';
            } elseif (str_starts_with($rule, 'in_array:')) {
                // 'in_array:field.*' rule requires values to be in another field's array
                $otherField = str_replace('in_array:', '', $rule);
                $relational['in_array'] = $otherField;
                $relational['description_suffix'] = '(must be values from '.$otherField.')';
            } elseif (str_starts_with($rule, 'before:')) {
                // 'before:field' or 'before:2024-01-01'
                $beforeValue = str_replace('before:', '', $rule);
                $relational['before'] = $beforeValue;
                // It's a field reference if it's not a date value
                if (! self::isDateValue($beforeValue)) {
                    $relational['description_suffix'] = '(must be before '.$beforeValue.')';
                }
            } elseif (str_starts_with($rule, 'after:')) {
                // 'after:field' or 'after:2024-01-01'
                $afterValue = str_replace('after:', '', $rule);
                $relational['after'] = $afterValue;
                // It's a field reference if it's not a date value
                if (! self::isDateValue($afterValue)) {
                    $relational['description_suffix'] = '(must be after '.$afterValue.')';
                }
            } elseif (str_starts_with($rule, 'before_or_equal:')) {
                $beforeValue = str_replace('before_or_equal:', '', $rule);
                $relational['before_or_equal'] = $beforeValue;
                // Always add description for before_or_equal
                $relational['description_suffix'] = '(must be before or equal to '.$beforeValue.')';
            } elseif (str_starts_with($rule, 'after_or_equal:')) {
                $afterValue = str_replace('after_or_equal:', '', $rule);
                $relational['after_or_equal'] = $afterValue;
                // Always add description for after_or_equal
                $relational['description_suffix'] = '(must be after or equal to '.$afterValue.')';
            }
        }

        return $relational;
    }
}
