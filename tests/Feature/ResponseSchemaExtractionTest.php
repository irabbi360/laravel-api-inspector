<?php

use Illuminate\Http\Resources\Json\JsonResource;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

// Test Resources
class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => 'int',
            'name' => 'string',
            'email' => 'string',
            'created_at' => 'datetime',
        ];
    }
}

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => 'int',
            'title' => 'string',
            'content' => 'string',
            'author_id' => 'int',
        ];
    }
}

// Test Controllers
class TestControllerWithDocBlockOnly
{
    /**
     * Get user profile
     *
     * @LAPIresponsesSchema ProfileResource
     */
    public function show()
    {
        return new ProfileResource([]);
    }
}

class TestControllerWithReturnTypeOnly
{
    /**
     * Get user profile
     */
    public function show(): ProfileResource
    {
        return new ProfileResource([]);
    }
}

class TestControllerWithBoth
{
    /**
     * Get user profile
     *
     * @LAPIresponsesSchema PostResource
     */
    public function show(): ProfileResource
    {
        return new ProfileResource([]);
    }
}

class ResponseSchemaExtractionTest extends TestCase
{
    /**
     * Test extraction with DocBlock annotation only
     */
    public function test_extract_schema_from_docblock_annotation()
    {
        $reflection = new \ReflectionClass(TestControllerWithDocBlockOnly::class);
        $method = $reflection->getMethod('show');

        $service = app(\Irabbi360\LaravelApiInspector\Services\LaravelApiInspectorService::class);
        $docComment = $method->getDocComment();

        // Verify DocBlock contains annotation
        $this->assertStringContainsString('@LAPIresponsesSchema', $docComment);

        // Test extracting resource from DocBlock
        $extractedClass = $this->invokePrivateMethod($service, 'extractResourceFromDocBlock', [$method]);
        $this->assertNotNull($extractedClass);
        $this->assertStringContainsString('ProfileResource', $extractedClass);
    }

    /**
     * Test extraction with return type only
     */
    public function test_extract_schema_from_return_type()
    {
        $reflection = new \ReflectionClass(TestControllerWithReturnTypeOnly::class);
        $method = $reflection->getMethod('show');

        $service = app(\Irabbi360\LaravelApiInspector\Services\LaravelApiInspectorService::class);

        // Test resolving return type name
        $returnType = $method->getReturnType();
        $returnTypeName = (string) $returnType;

        $resolvedClass = $this->invokePrivateMethod($service, 'resolveReturnTypeName', [
            $returnTypeName,
            $method,
        ]);

        $this->assertNotNull($resolvedClass);
        $this->assertStringContainsString('ProfileResource', $resolvedClass);
        $this->assertTrue(class_exists($resolvedClass));
    }

    /**
     * Test extraction with both DocBlock and return type (DocBlock should take precedence)
     */
    public function test_docblock_takes_precedence_over_return_type()
    {
        $reflection = new \ReflectionClass(TestControllerWithBoth::class);
        $method = $reflection->getMethod('show');

        $service = app(\Irabbi360\LaravelApiInspector\Services\LaravelApiInspectorService::class);

        // Extract from DocBlock
        $docBlockClass = $this->invokePrivateMethod($service, 'extractResourceFromDocBlock', [$method]);
        $this->assertStringContainsString('PostResource', $docBlockClass);

        // Since DocBlock has annotation, it should use PostResource, not ProfileResource from return type
        $this->assertStringNotContainsString('ProfileResource', $docBlockClass);
    }

    /**
     * Test that schema is extracted correctly from the resource
     */
    public function test_extracted_schema_contains_resource_fields()
    {
        $reflection = new \ReflectionClass(TestControllerWithReturnTypeOnly::class);
        $method = $reflection->getMethod('show');

        $service = app(\Irabbi360\LaravelApiInspector\Services\LaravelApiInspectorService::class);

        // Resolve the return type
        $returnType = $method->getReturnType();
        $returnTypeName = (string) $returnType;
        $resolvedClass = $this->invokePrivateMethod($service, 'resolveReturnTypeName', [
            $returnTypeName,
            $method,
        ]);

        // Extract schema recursively
        $schema = $service->extractResourceSchemaRecursively($resolvedClass);

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('schema', $schema);
        $this->assertArrayHasKey('resource_class', $schema);
        $this->assertNotEmpty($schema['schema']);
    }

    /**
     * Helper method to invoke private methods
     */
    private function invokePrivateMethod($object, $methodName, $params = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $params);
    }
}
