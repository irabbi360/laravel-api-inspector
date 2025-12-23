<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Generators;

use Irabbi360\LaravelApiInspector\Generators\OpenApiGenerator;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class OpenApiGeneratorTest extends TestCase
{
    protected array $sampleRoute = [
        'method' => 'GET',
        'uri' => '/api/users/{id}',
        'description' => 'Get User',
        'controller' => 'UserController@show',
        'middleware' => ['api'],
        'requires_auth' => true,
        'parameters' => [
            'id' => [
                'name' => 'id',
                'type' => 'string',
                'description' => 'User ID',
            ],
        ],
        'request_rules' => [],
        'query_parameters' => [],
        'response_example' => ['id' => 1, 'name' => 'John'],
    ];

    public function test_it_generates_openapi_spec()
    {
        $generator = new OpenApiGenerator([$this->sampleRoute], 'Test API', '1.0.0');
        $spec = $generator->generate();

        expect($spec)->toHaveKeys(['openapi', 'info', 'servers', 'paths', 'components']);
        expect($spec['openapi'])->toBe('3.0.0');
        expect($spec['info']['title'])->toBe('Test API');
        expect($spec['info']['version'])->toBe('1.0.0');
    }

    public function test_it_generates_paths()
    {
        $generator = new OpenApiGenerator([$this->sampleRoute]);
        $spec = $generator->generate();

        expect($spec['paths'])->toHaveKey('/api/users/{id}');
        expect($spec['paths']['/api/users/{id}'])->toHaveKey('get');
    }

    public function test_it_generates_parameters()
    {
        $generator = new OpenApiGenerator([$this->sampleRoute]);
        $spec = $generator->generate();
        $operation = $spec['paths']['/api/users/{id}']['get'];

        expect($operation['parameters'])->not->toBeEmpty();
        expect($operation['parameters'][0]['name'])->toBe('id');
        expect($operation['parameters'][0]['in'])->toBe('path');
        expect($operation['parameters'][0]['required'])->toBeTrue();
    }

    public function test_it_adds_security_for_protected_routes()
    {
        $generator = new OpenApiGenerator([$this->sampleRoute]);
        $spec = $generator->generate();
        $operation = $spec['paths']['/api/users/{id}']['get'];

        expect($operation)->toHaveKey('security');
        expect($operation['security'][0])->toHaveKey('bearerAuth');
    }

    public function test_it_generates_json_format()
    {
        $generator = new OpenApiGenerator([$this->sampleRoute]);
        $json = $generator->generateJson();

        expect($json)->toBeString();
        expect(json_decode($json, true))->toHaveKey('openapi');
    }
}
