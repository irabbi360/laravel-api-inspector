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

        // Check for Action class parameter
        $actionClass = self::hasActionParameter($reflectionMethod);

        if ($actionClass) {
            return self::extractFromAction($actionClass);
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
     * Check if method has an Action class parameter
     */
    public static function hasActionParameter(\ReflectionMethod $method): ?string
    {
        foreach ($method->getParameters() as $param) {
            $paramType = $param->getType();

            if (! $paramType instanceof \ReflectionNamedType || $paramType->isBuiltin()) {
                continue;
            }

            $paramClass = $paramType->getName();

            // Check if class exists and has any rules method (Action pattern)
            if (class_exists($paramClass) && self::hasRulesMethod($paramClass)) {
                return $paramClass;
            }
        }

        return null;
    }

    /**
     * Check if Action class has any rules method
     */
    public static function hasRulesMethod(string $class): bool
    {
        $methodNames = ['rules', 'getRules', 'getValidationRules', 'validate'];

        foreach ($methodNames as $methodName) {
            if (method_exists($class, $methodName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract rules from an Action class
     *
     * @return array<string, array>
     */
    public static function extractFromAction(string $actionClass): array
    {
        if (! class_exists($actionClass)) {
            return [];
        }

        // Try different rule method names in priority order
        $methodNames = ['rules', 'getRules', 'getValidationRules'];

        foreach ($methodNames as $methodName) {
            if (method_exists($actionClass, $methodName)) {
                $rules = self::extractRulesFromMethod($actionClass, $methodName);
                if (! empty($rules)) {
                    return $rules;
                }
            }
        }

        // Try to extract from validate() method
        if (method_exists($actionClass, 'validate')) {
            $rules = self::extractRulesFromValidateMethod($actionClass);
            if (! empty($rules)) {
                return $rules;
            }
        }

        return [];
    }

    /**
     * Extract rules by calling a specific method
     *
     * @return array<string, array>
     */
    private static function extractRulesFromMethod(string $actionClass, string $methodName): array
    {
        try {
            $instance = new $actionClass;

            if (method_exists($instance, $methodName)) {
                $rules = $instance->{$methodName}();

                // Only parse if rules is an array
                if (is_array($rules)) {
                    return RuleParser::parse($rules);
                }
            }
        } catch (\Exception) {
            // Silently fail
        }

        return [];
    }

    /**
     * Extract rules from a validate() method by analyzing its code
     *
     * @return array<string, array>
     */
    private static function extractRulesFromValidateMethod(string $actionClass): array
    {
        try {
            $reflection = new \ReflectionClass($actionClass);
            if (! $reflection->hasMethod('validate')) {
                return [];
            }

            $method = $reflection->getMethod('validate');
            $file = $method->getFileName();
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();

            if (! file_exists($file)) {
                return [];
            }

            $lines = file($file);
            $methodCode = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

            // First try to extract rules from Validator::make() or validator() call
            $rulesFromValidator = self::parseValidatorRulesFromCode($methodCode);
            if (! empty($rulesFromValidator)) {
                return $rulesFromValidator;
            }

            // If no Validator::make() found, try to extract rules array from return statement
            if (preg_match('/return\s*(\[.+?\];?)/s', $methodCode, $matches)) {
                $rulesCode = $matches[1];

                // Remove trailing semicolon if present
                $rulesCode = rtrim($rulesCode, '; \t\n\r');

                // Try to evaluate as PHP array (safely with regex parsing)
                if (strpos($rulesCode, '[') === 0 && strpos($rulesCode, ']') === strlen($rulesCode) - 1) {
                    return self::extractRulesUsingRegex($rulesCode);
                }
            }
        } catch (\Exception) {
            // Silently fail
        }

        return [];
    }

    /**
     * Parse validation rules from Validator::make() code
     *
     * @return array<string, array>
     */
    private static function parseValidatorRulesFromCode(string $code): array
    {
        // Look for Validator::make($input, [ ... ]) or validator($input, [ ... ])
        // Find the opening bracket after the comma
        if (preg_match('/(?:Validator::make|validator)\s*\([^,]+,\s*(\[)/s', $code, $matches)) {
            // Find the position where the array starts
            $arrayStart = strpos($code, '[', strpos($code, ',')) + 1;

            // Extract the complete rules array by counting brackets
            $bracketCount = 0;
            $rulesCode = '[';
            $foundStart = false;

            for ($i = $arrayStart; $i < strlen($code); $i++) {
                $char = $code[$i];

                if ($char === '[') {
                    $bracketCount++;
                    $foundStart = true;
                    $rulesCode .= $char;
                } elseif ($char === ']') {
                    if ($bracketCount === 0) {
                        // Found the closing bracket of the rules array
                        $rulesCode .= $char;
                        break;
                    } else {
                        $bracketCount--;
                        $rulesCode .= $char;
                    }
                } else {
                    $rulesCode .= $char;
                }
            }

            if (strlen($rulesCode) > 2) {
                // Use regex extraction instead of eval to avoid syntax errors
                return self::extractRulesUsingRegex($rulesCode);
            }
        }

        return [];
    }

    /**
     * Extract rules using regex patterns and bracket-aware splitting
     *
     * @return array<string, array>
     */
    private static function extractRulesUsingRegex(string $rulesContent): array
    {
        $rules = [];

        // First, extract the content between outer brackets
        $trimmed = trim($rulesContent);
        if (strpos($trimmed, '[') === 0 && strrpos($trimmed, ']') === strlen($trimmed) - 1) {
            $innerContent = trim(substr($trimmed, 1, -1));
        } else {
            $innerContent = trim($rulesContent, '[] \t\n\r');
        }

        // Split by top-level commas to get individual field => rules pairs
        $fields = self::splitFieldDefinitions($innerContent);

        foreach ($fields as $field) {
            $field = trim($field);

            // Skip empty fields
            if (empty($field)) {
                continue;
            }

            // Match: 'fieldName' => rules or "fieldName" => rules
            if (preg_match('/^[\'"]([^\'"]+)[\'"]\s*=>\s*(.+)$/s', $field, $match)) {
                $fieldName = $match[1];
                $ruleValue = trim($match[2]);

                // Remove trailing commas and whitespace
                $ruleValue = rtrim($ruleValue, ',; \t\n\r');

                if (! empty($ruleValue)) {
                    // Extract simple string rules: 'rule|rule|rule'
                    if (strpos($ruleValue, '[') === 0) {
                        // Array format: ['rule', 'rule', 'rule']
                        // Extract strings and rule objects from array
                        $rules[$fieldName] = self::extractRulesFromArray($ruleValue);
                    } else {
                        // String format: 'rule|rule|rule'
                        $rules[$fieldName] = $ruleValue;
                    }
                }
            }
        }

        // Parse the collected rules
        if (! empty($rules)) {
            return RuleParser::parse($rules);
        }

        return [];
    }

    /**
     * Split field definitions by top-level comma (respecting brackets and parentheses)
     *
     * @return array<string>
     */
    private static function splitFieldDefinitions(string $content): array
    {
        $fields = [];
        $current = '';
        $bracketDepth = 0;
        $parenDepth = 0;
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];

            // Handle string delimiters
            if (($char === '"' || $char === "'") && ($i === 0 || $content[$i - 1] !== '\\')) {
                if (! $inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            // If we're inside a string, just append the character
            if ($inString) {
                $current .= $char;

                continue;
            }

            switch ($char) {
                case '[':
                    $bracketDepth++;
                    $current .= $char;
                    break;
                case ']':
                    $bracketDepth--;
                    $current .= $char;
                    break;
                case '(':
                    $parenDepth++;
                    $current .= $char;
                    break;
                case ')':
                    $parenDepth--;
                    $current .= $char;
                    break;
                case ',':
                    if ($bracketDepth === 0 && $parenDepth === 0) {
                        if (! empty($current)) {
                            $fields[] = $current;
                            $current = '';
                        }
                    } else {
                        $current .= $char;
                    }
                    break;
                default:
                    $current .= $char;
            }
        }

        if (! empty($current)) {
            $fields[] = $current;
        }

        return $fields;
    }

    /**
     * Split rules by top-level comma (respecting nested brackets)
     *
     * @return array<string>
     */
    private static function splitRulesByTopLevelComma(string $content): array
    {
        $parts = [];
        $current = '';
        $bracketDepth = 0;
        $parenDepth = 0;

        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];

            switch ($char) {
                case '[':
                    $bracketDepth++;
                    $current .= $char;
                    break;
                case ']':
                    $bracketDepth--;
                    $current .= $char;
                    break;
                case '(':
                    $parenDepth++;
                    $current .= $char;
                    break;
                case ')':
                    $parenDepth--;
                    $current .= $char;
                    break;
                case ',':
                    if ($bracketDepth === 0 && $parenDepth === 0) {
                        if (! empty($current)) {
                            $parts[] = $current;
                            $current = '';
                        }
                    } else {
                        $current .= $char;
                    }
                    break;
                default:
                    $current .= $char;
            }
        }

        if (! empty($current)) {
            $parts[] = $current;
        }

        return $parts;
    }

    /**
     * Extract rules from array format like ['required', 'email', Rule::exists(...)]
     */
    private static function extractRulesFromArray(string $arrayStr): string
    {
        // Remove outer brackets
        $arrayStr = trim($arrayStr, '[]');

        // Split by comma at the top level
        $items = self::splitRulesByTopLevelComma('['.$arrayStr.']');

        $rules = [];
        foreach ($items as $item) {
            $item = trim($item);

            // Remove leading/trailing brackets if they wrap the whole thing
            if (str_starts_with($item, '[') && str_ends_with($item, ']')) {
                $item = trim($item, '[]');
            }

            // Extract string rules: 'required', "required", required
            if (preg_match('/[\'"]?([a-z_:0-9|]+)[\'"]?/i', $item, $match)) {
                $rule = trim($match[1], '\'\"');
                if (! empty($rule) && ! str_contains($rule, '(') && ! str_contains($rule, 'Rule::')) {
                    $rules[] = $rule;
                }
            }
            // For Rule objects, try to extract the rule from the PHP code
            elseif (preg_match('/Rule::(\w+)\s*\(\s*([^)]+)\s*\)/i', $item, $match)) {
                $ruleName = $match[1];
                $ruleArgs = trim($match[2]);

                // Handle common Rule:: methods
                if (strtolower($ruleName) === 'exists') {
                    $rules[] = 'exists:'.str_replace(['\'', '"'], '', $ruleArgs);
                } elseif (strtolower($ruleName) === 'unique') {
                    $rules[] = 'unique:'.str_replace(['\'', '"'], '', $ruleArgs);
                } elseif (strtolower($ruleName) === 'in') {
                    $rules[] = 'in:'.str_replace(['\'', '"'], '', $ruleArgs);
                }
            }
        }

        return implode('|', $rules);
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
