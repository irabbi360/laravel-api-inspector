<?php

namespace Irabbi360\LaravelApiInspector\Tests\Feature;

use Irabbi360\LaravelApiInspector\Extractors\ResourceExtractor;
use Orchestra\Testbench\TestCase;

class NestedResourceExtractionTest extends TestCase
{
    public function test_detects_nested_resource_instantiation()
    {
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (int) $this->id,
                "name" => $this->name,
                "nested" => new RelatedResource($this->whenLoaded("related")),
            ];'
        );

        // Should detect the nested resource
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('nested', $schema);
        $this->assertEquals('nested_resource', $schema['nested']['type']);
        $this->assertEquals('RelatedResource', $schema['nested']['resource_class']);
    }

    public function test_detects_resource_collection()
    {
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (int) $this->id,
                "items" => ItemResource::collection($this->items),
                "tags" => TagResource::collection($this->whenLoaded("tags")),
            ];'
        );

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('items', $schema);
        $this->assertEquals('nested_resource', $schema['items']['type']);
        $this->assertEquals('ItemResource', $schema['items']['resource_class']);
        $this->assertEquals('collection', $schema['items']['resource_type']);

        $this->assertArrayHasKey('tags', $schema);
        $this->assertEquals('nested_resource', $schema['tags']['type']);
        $this->assertEquals('TagResource', $schema['tags']['resource_class']);
        $this->assertEquals('collection', $schema['tags']['resource_type']);
    }

    public function test_detects_new_resource_syntax()
    {
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (int) $this->id,
                "owner" => new OwnerResource($this->owner),
                "metadata" => new ConfigResource($this->meta, true),
            ];'
        );

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('owner', $schema);
        $this->assertEquals('nested_resource', $schema['owner']['type']);
        $this->assertEquals('OwnerResource', $schema['owner']['resource_class']);
        $this->assertEquals('object', $schema['owner']['resource_type']);

        $this->assertArrayHasKey('metadata', $schema);
        $this->assertEquals('nested_resource', $schema['metadata']['type']);
        $this->assertEquals('ConfigResource', $schema['metadata']['resource_class']);
        $this->assertEquals('object', $schema['metadata']['resource_type']);
    }

    public function test_handles_mixed_field_types()
    {
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (int) $this->id,
                "name" => $this->name,
                "email" => $this->email,
                "profile" => new DetailResource($this->profile),
                "status" => "active",
            ];'
        );

        $this->assertIsArray($schema);

        // Regular fields
        $this->assertEquals('string', $schema['id']['type']);
        $this->assertEquals('string', $schema['name']['type']);
        $this->assertEquals('string', $schema['email']['type']);

        // Nested resource
        $this->assertEquals('nested_resource', $schema['profile']['type']);
        $this->assertEquals('DetailResource', $schema['profile']['resource_class']);
    }

    public function test_detects_namespaced_resources()
    {
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (int) $this->id,
                "user" => new \App\Http\Resources\UserResource($this->user),
                "settings" => new App\Http\Resources\API\SettingsResource($this->settings),
            ];'
        );

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('user', $schema);
        $this->assertEquals('nested_resource', $schema['user']['type']);
        $this->assertEquals('\App\Http\Resources\UserResource', $schema['user']['resource_class']);

        $this->assertArrayHasKey('settings', $schema);
        $this->assertEquals('nested_resource', $schema['settings']['type']);
        $this->assertEquals('App\Http\Resources\API\SettingsResource', $schema['settings']['resource_class']);
    }

    public function test_multiple_nested_resources_in_single_return()
    {
        // Test case with multiple nested resources like the RMG example
        $schema = ResourceExtractor::parseArrayFromCode(
            'return [
                "id" => (string) Hashids::encode($this->id),
                "regNo" => $this->reg_no,
                "name" => $this->name,
                "address" => $this->address,
                "telephone" => $this->telephone,
                "associationType" => new AssociationTypeResource($this->whenLoaded("associationType"), $this->simple),
                "division" => new DivisionResource($this->whenLoaded("division"), $this->simple),
                "district" => new DistrictResource($this->whenLoaded("district"), $this->simple),
                "tags" => TagResource::collection($this->whenLoaded("tags")),
            ];'
        );

        $this->assertIsArray($schema);

        // Verify regular fields
        $this->assertEquals('string', $schema['id']['type']);
        $this->assertEquals('string', $schema['regNo']['type']);
        $this->assertEquals('string', $schema['name']['type']);

        // Verify nested object resources
        $this->assertEquals('nested_resource', $schema['associationType']['type']);
        $this->assertEquals('AssociationTypeResource', $schema['associationType']['resource_class']);
        $this->assertEquals('object', $schema['associationType']['resource_type']);

        $this->assertEquals('nested_resource', $schema['division']['type']);
        $this->assertEquals('DivisionResource', $schema['division']['resource_class']);
        $this->assertEquals('object', $schema['division']['resource_type']);

        $this->assertEquals('nested_resource', $schema['district']['type']);
        $this->assertEquals('DistrictResource', $schema['district']['resource_class']);
        $this->assertEquals('object', $schema['district']['resource_type']);

        // Verify collection resource
        $this->assertEquals('nested_resource', $schema['tags']['type']);
        $this->assertEquals('TagResource', $schema['tags']['resource_class']);
        $this->assertEquals('collection', $schema['tags']['resource_type']);
    }
}
