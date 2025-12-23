<?php

namespace Irabbi360\LaravelApiInspector\Support;

class RuleParser
{
    /**
     * Parse Laravel validation rules into schema
     *
     * @param  array<string, string|array>  $rules  FormRequest rules
     * @return array<string, array>
     */
    public static function parse(array $rules): array
    {
        $schema = [];

        foreach ($rules as $fieldName => $ruleString) {
            // Handle both string and array rule formats
            $ruleString = is_array($ruleString) ? implode('|', $ruleString) : $ruleString;
            $schema[$fieldName] = self::parseFieldRule($fieldName, $ruleString);
        }

        return $schema;
    }

    /**
     * Parse a single field's validation rules
     */
    public static function parseFieldRule(string $fieldName, string $ruleString): array
    {
        $rules = explode('|', $ruleString);
        $field = [
            'name' => $fieldName,
            'required' => in_array('required', $rules),
            'type' => self::inferType($rules),
            'example' => TypeInferer::inferExample($fieldName, self::inferType($rules)),
        ];

        // Add constraints
        $constraints = TypeInferer::extractConstraints($ruleString);
        if (! empty($constraints)) {
            $field = array_merge($field, $constraints);
        }

        // Add format if applicable
        $format = self::getFormat($rules);
        if ($format) {
            $field['format'] = $format;
        }

        // Add description from field name
        $field['description'] = self::generateDescription($fieldName);

        return $field;
    }

    /**
     * Infer primary type from rules array
     */
    public static function inferType(array $rules): string
    {
        $typeRules = ['numeric', 'integer', 'int', 'number', 'float', 'double', 'boolean', 'bool', 'array', 'object', 'file', 'image'];

        foreach ($rules as $rule) {
            // Extract base rule (before the colon)
            $baseRule = explode(':', $rule)[0];

            if (in_array($baseRule, $typeRules)) {
                return TypeInferer::ruleToType($baseRule);
            }
        }

        return 'string';
    }

    /**
     * Get format from rules
     */
    public static function getFormat(array $rules): ?string
    {
        foreach ($rules as $rule) {
            $baseRule = explode(':', $rule)[0];
            $format = TypeInferer::getFormat($baseRule);

            if ($format) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Generate human-readable description from field name
     */
    public static function generateDescription(string $fieldName): string
    {
        $words = preg_split('/(?=[A-Z])|_/', $fieldName, -1, PREG_SPLIT_NO_EMPTY);

        return ucfirst(implode(' ', $words));
    }

    /**
     * Extract validation rules from a FormRequest class
     */
    public static function extractFromFormRequest(string $formRequestClass): array
    {
        if (! class_exists($formRequestClass)) {
            return [];
        }

        try {
            $instance = new $formRequestClass;

            if (method_exists($instance, 'rules')) {
                return self::parse($instance->rules());
            }
        } catch (\Exception) {
            // Silently fail if we can't instantiate
        }

        return [];
    }
}
