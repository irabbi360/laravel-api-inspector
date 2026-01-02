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
            if (is_array($ruleString)) {
                $ruleString = self::convertRulesArrayToString($ruleString);
            }
            $schema[$fieldName] = self::parseFieldRule($fieldName, $ruleString);
        }

        // Add dependent fields that are required by relational rules
        $schema = self::addDependentFields($schema);

        return $schema;
    }

    /**
     * Convert array of rules (including Rule objects) to string
     *
     * @param  array<string|object>  $rulesArray
     */
    private static function convertRulesArrayToString(array $rulesArray): string
    {
        $stringRules = [];

        foreach ($rulesArray as $rule) {
            // If it's a string rule, add it directly
            if (is_string($rule)) {
                $stringRules[] = $rule;
            }
            // If it's a custom Rule object, get its class name as identifier
            elseif (is_object($rule)) {
                $className = class_basename($rule);
                // Store object rules for potential future validation
                $stringRules[] = strtolower($className);
            }
        }

        return implode('|', $stringRules);
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

        // Add constraints (min, max, size, pattern, enum)
        $constraints = TypeInferer::extractConstraints($ruleString);
        if (! empty($constraints)) {
            $field = array_merge($field, $constraints);
        }

        // Add relational rule information (confirmed, same, different, before, after, etc.)
        $relational = TypeInferer::extractRelationalRules($ruleString, $fieldName);
        if (! empty($relational)) {
            $field = array_merge($field, $relational);
        }

        // Add format if applicable
        $format = self::getFormat($rules);
        if ($format) {
            $field['format'] = $format;
        }

        // Add description from field name (with relational info if available)
        $description = self::generateDescription($fieldName);
        if (isset($relational['description_suffix'])) {
            $description .= ' '.$relational['description_suffix'];
        }
        $field['description'] = $description;

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
     * Add dependent fields that are required by relational rules
     * For example, if password has 'confirmed' rule, add password_confirmation field
     *
     * @param  array<string, array>  $schema
     * @return array<string, array>
     */
    private static function addDependentFields(array $schema): array
    {
        $fieldsToAdd = [];

        // Check each field for relational rules that require other fields
        foreach ($schema as $fieldName => $fieldData) {
            // Handle 'confirmed' rule - requires {field}_confirmation
            if (isset($fieldData['confirmed']) && $fieldData['confirmed']) {
                $confirmationField = $fieldData['requires_field'] ?? $fieldName.'_confirmation';
                if (! isset($schema[$confirmationField])) {
                    $fieldsToAdd[$confirmationField] = [
                        'name' => $confirmationField,
                        'required' => true,
                        'type' => $fieldData['type'] ?? 'string',
                        'example' => $fieldData['example'] ?? '',
                        'description' => 'Confirmation of '.$fieldName,
                    ];
                }
            }

            // Handle 'same:field' rule - the target field must exist
            if (isset($fieldData['same_as'])) {
                $targetField = $fieldData['same_as'];
                if (! isset($schema[$targetField]) && ! isset($fieldsToAdd[$targetField])) {
                    $fieldsToAdd[$targetField] = [
                        'name' => $targetField,
                        'required' => true,
                        'type' => 'string',
                        'example' => '',
                        'description' => 'Target field for '.$fieldName,
                    ];
                }
            }

            // Handle 'different:field' rule - the target field must exist
            if (isset($fieldData['different_from'])) {
                $targetField = $fieldData['different_from'];
                if (! isset($schema[$targetField]) && ! isset($fieldsToAdd[$targetField])) {
                    $fieldsToAdd[$targetField] = [
                        'name' => $targetField,
                        'required' => true,
                        'type' => 'string',
                        'example' => '',
                        'description' => 'Target field for '.$fieldName,
                    ];
                }
            }
        }

        // Merge added fields into schema
        return array_merge($schema, $fieldsToAdd);
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
