<?php

namespace Irabbi360\LaravelApiInspector\Commands;

use Illuminate\Console\Command;
use Irabbi360\LaravelApiInspector\Extractors\RequestRuleExtractor;
use Irabbi360\LaravelApiInspector\Extractors\ResponseExtractor;
use Irabbi360\LaravelApiInspector\Extractors\RouteExtractor;
use Irabbi360\LaravelApiInspector\Generators\HtmlDocsGenerator;
use Irabbi360\LaravelApiInspector\Generators\OpenApiGenerator;
use Irabbi360\LaravelApiInspector\Generators\PostmanGenerator;
use Irabbi360\LaravelApiInspector\Writers\CacheWriter;
use Irabbi360\LaravelApiInspector\Writers\JsonFileWriter;

class GenerateDocsCommand extends Command
{
    public $signature = 'api-inspector:generate {--format=all : Format to generate (all, postman, openapi, html)}';

    public $description = 'Generate API documentation from routes, FormRequests, and API Resources';

    protected JsonFileWriter $fileWriter;

    protected CacheWriter $cacheWriter;

    public function __construct()
    {
        parent::__construct();

        $this->fileWriter = new JsonFileWriter;
        $this->cacheWriter = new CacheWriter;
    }

    public function handle(): int
    {
        if (! config('api-inspector.enabled')) {
            $this->error('API Inspector is disabled. Set API_INSPECTOR_ENABLED=true to enable it.');

            return self::FAILURE;
        }

        $this->info('Generating API documentation...');

        try {
            // Extract routes
            $this->info('Extracting routes...');
            $routes = RouteExtractor::extract();

            if (empty($routes)) {
                $this->warn('No API routes found.');

                return self::SUCCESS;
            }

            $this->info('Found '.count($routes).' routes');

            // Enhance routes with request and response data
            // $routes = $this->enrichRoutes($routes);

            // Cache routes
            $this->cacheWriter->saveRoutes($routes);

            // Generate documentation
            $format = $this->option('format');

            if ($format === 'all' || $format === 'postman') {
                $this->generatePostman($routes);
            }

            if ($format === 'all' || $format === 'openapi') {
                $this->generateOpenApi($routes);
            }

            if ($format === 'all' || $format === 'html') {
                $this->generateHtml($routes);
            }

            $this->info('✨ Documentation generated successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Enrich routes with request and response data
     */
    protected function enrichRoutes(array $routes): array
    {
        return array_map(function ($route) {
            // Extract request rules
            $route['request_rules'] = RequestRuleExtractor::extract($route['controller']);

            // Extract query parameters
            $route['query_parameters'] = RequestRuleExtractor::extractQueryParameters($route['uri']);

            // Extract response
            $responseData = ResponseExtractor::extract($route['controller']);
            $route['response_example'] = $responseData['example'] ?? [];

            return $route;
        }, $routes);
    }

    /**
     * Generate Postman collection
     */
    protected function generatePostman(array $routes): void
    {
        $this->info('Generating Postman collection...');

        try {
            $generator = new PostmanGenerator(
                $routes,
                config('app.name').' API',
                config('app.url') ?? 'https://api.example.com'
            );

            $collection = $generator->generate();

            if (config('api-inspector.output.postman')) {
                $this->fileWriter->savePostmanCollection($collection);
                $this->info('Postman collection saved');
            }
        } catch (\Exception $e) {
            $this->warn('Failed to generate Postman collection: '.$e->getMessage());
        }
    }

    /**
     * Generate OpenAPI specification
     */
    protected function generateOpenApi(array $routes): void
    {
        $this->info('Generating OpenAPI specification...');

        try {
            $generator = new OpenApiGenerator(
                $routes,
                config('app.name').' API',
                config('app.version', '1.0.0'),
                config('app.url') ?? 'https://api.example.com'
            );

            $spec = $generator->generate();

            if (config('api-inspector.output.openapi')) {
                $this->fileWriter->saveOpenApiSpec($spec);
                $this->info('OpenAPI specification saved');
            }
        } catch (\Exception $e) {
            $this->warn('Failed to generate OpenAPI spec: '.$e->getMessage());
        }
    }

    /**
     * Generate HTML documentation
     */
    protected function generateHtml(array $routes): void
    {
        $this->info('Generating HTML documentation...');

        try {
            $generator = new HtmlDocsGenerator(
                $routes,
                config('app.name').' API Documentation',
                config('app.version', '1.0.0')
            );

            $html = $generator->generateHtml();
            $outputPath = config('api-inspector.response_path');
            $storagePath = config('api-inspector.storage_path');

            if ($storagePath === 'local') {
                $outputPath = public_path($outputPath);
            } else {
                $outputPath = storage_path("app/public/{$outputPath}");
            }

            // Save HTML as raw file
            $directory = dirname("$outputPath/index.html");
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents("$outputPath/index.html", $html);
            $this->info('HTML documentation saved');
        } catch (\Exception $e) {
            $this->warn('Failed to generate HTML documentation: '.$e->getMessage());
        }
    }
}
