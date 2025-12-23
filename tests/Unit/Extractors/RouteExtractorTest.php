<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Extractors;

use Irabbi360\LaravelApiInspector\Extractors\RouteExtractor;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class RouteExtractorTest extends TestCase
{
    public function test_it_extracts_api_routes()
    {
        $routes = RouteExtractor::extract();

        // Should return an array
        expect($routes)->toBeArray();
    }

    public function test_it_generates_description()
    {
        $description = RouteExtractor::generateDescription('/api/users', 'GET');

        expect($description)->toContain('Retrieve');
        expect($description)->toContain('Users');
    }

    public function test_it_detects_post_method_description()
    {
        $description = RouteExtractor::generateDescription('/api/users', 'POST');

        expect($description)->toContain('Create');
    }

    public function test_it_detects_delete_method_description()
    {
        $description = RouteExtractor::generateDescription('/api/users', 'DELETE');

        expect($description)->toContain('Delete');
    }

    public function test_it_detects_update_method_description()
    {
        $description = RouteExtractor::generateDescription('/api/users', 'PUT');

        expect($description)->toContain('Update');
    }
}
