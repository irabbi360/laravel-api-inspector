<?php

namespace Irabbi360\LaravelApiInspector\Support;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class ReflectionHelper
{
    /**
     * Get the fully qualified class name from a controller string
     *
     * @param  string  $controller  e.g., "UserController@index" or "App\Http\Controllers\UserController@index"
     * @return array{class: string, method: string}|null
     */
    public static function parseControllerString(string $controller): ?array
    {
        if (! str_contains($controller, '@')) {
            return null;
        }

        [$class, $method] = explode('@', $controller);

        // Resolve class name if it doesn't have a namespace
        if (! str_contains($class, '\\')) {
            $class = 'App\\Http\\Controllers\\'.$class;
        }

        return [
            'class' => $class,
            'method' => $method,
        ];
    }

    /**
     * Check if a class exists and is instantiable
     */
    public static function classExists(string $class): bool
    {
        try {
            return class_exists($class);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get method from a class if it exists
     */
    public static function getMethod(string $class, string $method): ?ReflectionMethod
    {
        try {
            if (! self::classExists($class)) {
                return null;
            }

            $reflectionClass = new ReflectionClass($class);

            return $reflectionClass->hasMethod($method)
                ? $reflectionClass->getMethod($method)
                : null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get method parameters
     *
     * @return array<string, ReflectionParameter>
     */
    public static function getMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /**
     * Check if method has FormRequest parameter
     */
    public static function hasFormRequestParameter(ReflectionMethod $method): ?string
    {
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && class_exists((string) $type)) {
                $className = (string) $type;
                if (is_subclass_of($className, 'Illuminate\\Foundation\\Http\\FormRequest')) {
                    return $className;
                }
            }
        }

        return null;
    }

    /**
     * Get the return type of a method
     */
    public static function getReturnType(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return null;
        }

        return (string) $returnType;
    }
}
