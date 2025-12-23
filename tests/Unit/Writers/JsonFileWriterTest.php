<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Writers;

use Irabbi360\LaravelApiInspector\Tests\TestCase;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

class JsonFileWriterTest extends TestCase
{
    protected JsonFileWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = new JsonFileWriter;
    }

    public function test_it_saves_json_file()
    {
        $path = storage_path('api-docs/test.json');
        $data = ['success' => true, 'message' => 'Test'];

        $result = $this->writer->save($path, $data);

        expect($result)->toBeTrue();
        expect(file_exists($path))->toBeTrue();

        // Cleanup
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function test_it_creates_directory_if_not_exists()
    {
        $path = storage_path('api-docs/nested/test.json');
        $data = ['test' => true];

        $this->writer->save($path, $data);

        expect(is_dir(dirname($path)))->toBeTrue();

        // Cleanup
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function test_it_saves_postman_collection()
    {
        $collection = [
            'info' => ['name' => 'Test'],
            'item' => [],
        ];

        $result = $this->writer->savePostmanCollection($collection);

        expect($result)->toBeTrue();

        // Cleanup
        $path = config('api-inspector.response_path') ?? storage_path('api-docs');
        if (file_exists("$path/postman_collection.json")) {
            unlink("$path/postman_collection.json");
        }
    }

    public function test_it_saves_openapi_spec()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'Test'],
        ];

        $result = $this->writer->saveOpenApiSpec($spec);

        expect($result)->toBeTrue();

        // Cleanup
        $path = config('api-inspector.response_path') ?? storage_path('api-docs');
        if (file_exists("$path/openapi.json")) {
            unlink("$path/openapi.json");
        }
    }

    public function test_it_deletes_file()
    {
        $path = storage_path('api-docs/test-delete.json');
        $this->writer->save($path, ['test' => true]);

        expect(file_exists($path))->toBeTrue();

        $result = $this->writer->delete($path);

        expect($result)->toBeTrue();
        expect(file_exists($path))->toBeFalse();
    }
}
