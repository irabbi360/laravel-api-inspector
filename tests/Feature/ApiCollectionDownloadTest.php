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
        // Get the absolute path
        $responsePath = config('api-inspector.response_path') ?? 'api-docs';
        $storagePath = config('api-inspector.storage_path', 'storage');
        
        if ($storagePath === 'local') {
            $docsPath = public_path($responsePath);
        } else {
            $docsPath = storage_path("app/public/{$responsePath}");
        }
        
        // Create directory if needed
        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }
        
        $file = "$docsPath/postman_collection.json";
        
        // Delete any existing files
        if (file_exists($file)) {
            unlink($file);
        }

        $controller = app(ApiInspectorController::class);
        $response = $controller->postman();

        // Check response is not null
        $this->assertNotNull($response);

        // File should now exist
        $this->assertTrue(file_exists($file), "Postman collection should be auto-generated at $file");
    }

    /**
     * Test OpenAPI specification is generated automatically if not exists
     */
    public function test_openapi_auto_generation()
    {
        // Get the absolute path
        $responsePath = config('api-inspector.response_path') ?? 'api-docs';
        $storagePath = config('api-inspector.storage_path', 'storage');
        
        if ($storagePath === 'local') {
            $docsPath = public_path($responsePath);
        } else {
            $docsPath = storage_path("app/public/{$responsePath}");
        }
        
        // Create directory if needed
        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }
        
        $file = "$docsPath/openapi.json";
        
        // Delete any existing files
        if (file_exists($file)) {
            unlink($file);
        }

        $controller = app(ApiInspectorController::class);
        $response = $controller->openapi();

        // Check response is not null
        $this->assertNotNull($response);

        // File should now exist
        $this->assertTrue(file_exists($file), "OpenAPI spec should be auto-generated at $file");
    }

    /**
     * Test API route can download Postman collection via HTTP
     */
    public function test_postman_endpoint_via_http()
    {
        $response = $this->get('/api/api-inspector-postman');

        // Debug: Check if we have an error response
        if ($response->getStatusCode() !== 200) {
            $json = $response->json();
            \Log::error('Postman endpoint error: '.json_encode($json));
        }

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
