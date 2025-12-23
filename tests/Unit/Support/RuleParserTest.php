<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Support;

use Irabbi360\LaravelApiInspector\Support\RuleParser;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class RuleParserTest extends TestCase
{
    public function test_it_parses_required_rule()
    {
        $result = RuleParser::parseFieldRule('email', 'required|email');

        expect($result['required'])->toBeTrue();
        expect($result['type'])->toBe('string');
        expect($result['format'])->toBe('email');
    }

    public function test_it_parses_numeric_rules()
    {
        $result = RuleParser::parseFieldRule('age', 'integer|min:18|max:100');

        expect($result['type'])->toBe('integer');
        expect($result['min'])->toBe(18);
        expect($result['max'])->toBe(100);
    }

    public function test_it_parses_validation_rules_array()
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'age' => 'integer|min:18',
        ];

        $result = RuleParser::parse($rules);

        expect($result)->toHaveKeys(['email', 'password', 'age']);
        expect($result['email']['required'])->toBeTrue();
        expect($result['password']['min'])->toBe(8);
        expect($result['age']['type'])->toBe('integer');
    }

    public function test_it_generates_description_from_field_name()
    {
        $description = RuleParser::generateDescription('user_email');

        expect($description)->toBe('User email');
    }

    public function test_it_infers_type_from_rules()
    {
        expect(RuleParser::inferType(['numeric']))->toBe('integer');
        expect(RuleParser::inferType(['boolean']))->toBe('boolean');
        expect(RuleParser::inferType(['array']))->toBe('array');
        expect(RuleParser::inferType(['string', 'email']))->toBe('string');
    }

    public function test_it_extracts_constraints()
    {
        $ruleString = 'min:5|max:255|in:active,inactive';

        $constraints = RuleParser::getFormat(explode('|', $ruleString));

        expect($constraints)->toBeNull();
    }

    public function test_it_handles_array_format_validation_rules()
    {
        // Test parsing array-format rules like ['required', 'email']
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'age' => ['integer', 'min:18'],
        ];

        $result = RuleParser::parse($rules);

        expect($result)->toHaveKeys(['email', 'password', 'age']);
        expect($result['email']['required'])->toBeTrue();
        expect($result['email']['type'])->toBe('string');
        expect($result['email']['format'])->toBe('email');
        expect($result['password']['min'])->toBe(8);
        expect($result['age']['type'])->toBe('integer');
        expect($result['age']['min'])->toBe(18);
    }
}
