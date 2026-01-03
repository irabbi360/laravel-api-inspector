<?php

use Illuminate\Support\Facades\Route;
use Irabbi360\LaravelApiInspector\Http\Controllers\ApiInspectorController;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class ApiCollectionDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register test routes
        Route::get('/api/test-endpoint', function () {
            return response()->json(['message' => 'success']);
        });

        Route::post('/api/test-create', function () {
            return response()->json(['id' => 1, 'message' => 'created']);
        });
    }

    /**
     * Test Postman collection can be downloaded
     */
    public function test_postman_collection_download()
    {
        $controller = app(ApiInspectorController::class);
        $response = $controller->postman();

        // Check if response is successful (BinaryFileResponse)
        $this->assertNotNull($response);

        // Check content type header
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));

        // Check disposition header for download
        $this->assertStringContainsString('postman_collection.json', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test OpenAPI specification can be downloaded
     */
    public function test_openapi_specification_download()
    {
        $controller = app(ApiInspectorController::class);
        $response = $controller->openapi();

        // Check if response is successful (BinaryFileResponse)
        $this->assertNotNull($response);

        // Check content type header
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));

        // Check disposition header for download
        $this->assertStringContainsString('openapi.json', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test Postman collection is generated automatically if not exists
     */
    public function test_postman_collection_auto_generation()
    {
        // Delete any existing files
        $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }
        $file = "$docsPath/postman_collection.json";
        if (file_exists($file)) {
            unlink($file);
        }

        $controller = app(ApiInspectorController::class);
        $response = $controller->postman();

        // Check response is not null
        $this->assertNotNull($response);

        // File should now exist
        $this->assertTrue(file_exists($file), 'Postman collection should be auto-generated');
    }

    /**
     * Test OpenAPI specification is generated automatically if not exists
     */
    public function test_openapi_auto_generation()
    {
        // Delete any existing files
        $docsPath = config('api-inspector.response_path') ?? storage_path('api-docs');
        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }
        $file = "$docsPath/openapi.json";
        if (file_exists($file)) {
            unlink($file);
        }

        $controller = app(ApiInspectorController::class);
        $response = $controller->openapi();

        // Check response is not null
        $this->assertNotNull($response);

        // File should now exist
        $this->assertTrue(file_exists($file), 'OpenAPI spec should be auto-generated');
    }

    /**
     * Test API route can download Postman collection via HTTP
     */
    public function test_postman_endpoint_via_http()
    {
        $response = $this->get('/api/api-inspector-postman');

        // TestResponse has getStatusCode() method
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Test API route can download OpenAPI spec via HTTP
     */
    public function test_openapi_endpoint_via_http()
    {
        $response = $this->get('/api/api-inspector-openapi');

        // TestResponse has getStatusCode() method
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    }
}
