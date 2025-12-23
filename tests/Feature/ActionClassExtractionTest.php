<?php

namespace Irabbi360\LaravelApiInspector\Tests\Feature;

use Irabbi360\LaravelApiInspector\Extractors\RequestRuleExtractor;
use Orchestra\Testbench\TestCase;

class ActionClassExtractionTest extends TestCase
{
    /**
     * Test extracting rules from an Action class
     */
    public function test_extracts_rules_from_action_class(): void
    {
        // Create a test action class
        $actionClass = new class {
            public function validate()
            {
                return [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email',
                    'description' => 'nullable|string',
                ];
            }
        };

        $rules = RequestRuleExtractor::extractFromAction(get_class($actionClass));

        $this->assertNotEmpty($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('description', $rules);
    }

    /**
     * Test that Action class rules are extracted correctly
     */
    public function test_action_class_rules_have_correct_metadata(): void
    {
        $actionClass = new class {
            public function rules()
            {
                return [
                    'title' => 'required|string|min:3|max:255',
                    'status' => 'required|in:active,inactive',
                ];
            }
        };

        $rules = RequestRuleExtractor::extractFromAction(get_class($actionClass));

        // Check title field
        $this->assertTrue($rules['title']['required']);
        $this->assertEquals('string', $rules['title']['type']);
        $this->assertEquals(3, $rules['title']['min']);
        $this->assertEquals(255, $rules['title']['max']);

        // Check status field
        $this->assertTrue($rules['status']['required']);
        $this->assertIsArray($rules['status']['enum']);
    }

    /**
     * Test that missing Action class returns empty array
     */
    public function test_nonexistent_action_class_returns_empty(): void
    {
        $rules = RequestRuleExtractor::extractFromAction('NonExistentActionClass');

        $this->assertEmpty($rules);
    }

    /**
     * Test that Action class without rules method returns empty
     */
    public function test_action_without_rules_method_returns_empty(): void
    {
        $actionClass = new class {
            // No rules method
        };

        $rules = RequestRuleExtractor::extractFromAction(get_class($actionClass));

        $this->assertEmpty($rules);
    }
}
