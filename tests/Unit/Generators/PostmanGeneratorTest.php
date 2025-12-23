<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Generators;

use Irabbi360\LaravelApiInspector\Generators\PostmanGenerator;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class PostmanGeneratorTest extends TestCase
{
    protected array $sampleRoute = [
        'method' => 'POST',
        'uri' => '/api/users',
        'description' => 'Create User',
        'controller' => 'UserController@store',
        'middleware' => ['api'],
        'requires_auth' => false,
        'parameters' => [],
        'request_rules' => [
            'email' => [
                'type' => 'string',
                'example' => 'user@example.com',
            ],
        ],
        'query_parameters' => [],
        'response_example' => ['success' => true],
    ];

    public function test_it_generates_postman_collection()
    {
        $generator = new PostmanGenerator([$this->sampleRoute], 'Test API');
        $collection = $generator->generate();

        expect($collection)->toHaveKeys(['info', 'item', 'variable']);
        expect($collection['info']['name'])->toBe('Test API');
        expect($collection['item'])->toHaveLength(1);
    }

    public function test_it_generates_postman_item()
    {
        $generator = new PostmanGenerator([$this->sampleRoute]);
        $collection = $generator->generate();
        $item = $collection['item'][0];

        expect($item['request']['method'])->toBe('POST');
        expect($item['request']['url']['raw'])->toContain('/api/users');
    }

    public function test_it_adds_auth_headers_for_protected_routes()
    {
        $route = $this->sampleRoute;
        $route['requires_auth'] = true;

        $generator = new PostmanGenerator([$route]);
        $collection = $generator->generate();
        $headers = $collection['item'][0]['request']['header'];

        $authHeader = collect($headers)->firstWhere('key', 'Authorization');
        expect($authHeader)->not->toBeNull();
        expect($authHeader['value'])->toContain('Bearer');
    }

    public function test_it_generates_json_format()
    {
        $generator = new PostmanGenerator([$this->sampleRoute]);
        $json = $generator->generateJson();

        expect($json)->toBeString();
        expect(json_decode($json, true))->toHaveKey('info');
    }
}
