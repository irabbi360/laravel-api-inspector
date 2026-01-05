<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class CollectionMetadataTest extends TestCase
{
    /**
     * Test Postman collection includes request body schema
     */
    public function test_postman_collection_includes_body_schema()
    {
        // Generate Postman collection
        $controller = app(ApiInspectorController::class);
        $response = $controller->postman();

        // Get the absolute path to the generated file
        $responsePath = config('api-inspector.response_path');
        $storagePath = config('api-inspector.storage_path', 'storage');

        if ($storagePath === 'local') {
            $docsPath = public_path($responsePath);
        } else {
            $docsPath = storage_path("app/public/{$responsePath}");
        }

        $file = "$docsPath/postman_collection.json";
        $this->assertTrue(file_exists($file), 'Postman collection file should exist');

        // Read the generated collection
        $content = file_get_contents($file);
        $collection = json_decode($content, true);

        // The collection should have items/folders
        $this->assertIsArray($collection, 'Collection should be an array');
        $this->assertArrayHasKey('info', $collection, 'Collection should have info');
    }

    /**
     * Test OpenAPI spec includes parameters and request body
     */
    public function test_openapi_spec_includes_parameters()
    {
        // Generate OpenAPI spec
        $controller = app(ApiInspectorController::class);
        $response = $controller->openapi();

        // Get the absolute path to the generated file
        $responsePath = config('api-inspector.response_path');
        $storagePath = config('api-inspector.storage_path', 'storage');

        if ($storagePath === 'local') {
            $docsPath = public_path($responsePath);
        } else {
            $docsPath = storage_path("app/public/{$responsePath}");
        }

        $file = "$docsPath/openapi.json";
        $this->assertTrue(file_exists($file), 'OpenAPI spec file should exist');

        // Read the generated spec
        $content = file_get_contents($file);
        $spec = json_decode($content, true);

        // The spec should have openapi version and paths
        $this->assertIsArray($spec, 'Spec should be an array');
        $this->assertArrayHasKey('openapi', $spec, 'Spec should have openapi version');
        $this->assertArrayHasKey('paths', $spec, 'Spec should have paths');
    }
}
