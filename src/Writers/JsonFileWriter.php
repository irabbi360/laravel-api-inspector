<?php

namespace Irabbi360\LaravelApiInspector\Writers;

use Illuminate\Filesystem\Filesystem;

class JsonFileWriter
{
    protected Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = app('files');
    }

    /**
     * Save data to JSON file
     */
    public function save(string $path, array $data): bool
    {
        // Create directory if it doesn't exist
        $directory = dirname($path);

        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return (bool) $this->filesystem->put($path, $json);
    }

    /**
     * Save route documentation
     */
    public function saveRouteDocs(string $routeName, array $routeData): bool
    {
        $basePath = config('api-inspector.response_path') ?? storage_path('api-docs');
        $filename = str_replace('/', '_', $routeName) ?: 'route';

        return $this->save("$basePath/$filename.json", $routeData);
    }

    /**
     * Save Postman collection
     */
    public function savePostmanCollection(array $collection): bool
    {
        $basePath = config('api-inspector.response_path') ?? storage_path('api-docs');

        return $this->save("$basePath/postman_collection.json", $collection);
    }

    /**
     * Save OpenAPI specification
     */
    public function saveOpenApiSpec(array $spec): bool
    {
        $basePath = config('api-inspector.response_path') ?? storage_path('api-docs');

        return $this->save("$basePath/openapi.json", $spec);
    }

    /**
     * Save response example
     */
    public function saveResponse(string $routePath, array $response): bool
    {
        $basePath = config('api-inspector.response_path') ?? storage_path('api-docs/responses');

        // Convert route path to file path
        $filePath = str_replace(['/', '{', '}'], ['_', '', ''], $routePath);

        return $this->save("$basePath/$filePath.json", $response);
    }

    /**
     * Delete a file
     */
    public function delete(string $path): bool
    {
        if ($this->filesystem->exists($path)) {
            return $this->filesystem->delete($path);
        }

        return true;
    }

    /**
     * Clear all generated documentation
     */
    public function clearAll(): bool
    {
        $basePath = config('api-inspector.response_path') ?? storage_path('api-docs');

        if ($this->filesystem->isDirectory($basePath)) {
            return $this->filesystem->deleteDirectory($basePath);
        }

        return true;
    }
}
