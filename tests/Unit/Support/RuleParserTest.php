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

    public function test_it_parses_confirmed_rule()
    {
        // 'confirmed' rule requires a {field}_confirmation field
        $result = RuleParser::parseFieldRule('password', 'required|string|min:8|confirmed');

        expect($result['confirmed'])->toBeTrue();
        expect($result['requires_field'])->toBe('password_confirmation');
        expect($result['description'])->toContain('password_confirmation');
    }

    public function test_it_parses_same_rule()
    {
        // 'same:field' rule requires the field to match another field
        $result = RuleParser::parseFieldRule('password_confirmation', 'required|same:password');

        expect($result['same_as'])->toBe('password');
        expect($result['description'])->toContain('must match password');
    }

    public function test_it_parses_different_rule()
    {
        // 'different:field' rule requires the field to be different from another
        $result = RuleParser::parseFieldRule('email', 'required|email|different:username');

        expect($result['different_from'])->toBe('username');
        expect($result['description'])->toContain('must be different from username');
    }

    public function test_it_parses_in_array_rule()
    {
        // 'in_array:field.*' rule requires values to be in another field's array
        $result = RuleParser::parseFieldRule('selected_ids', 'array|in_array:available_ids.*');

        expect($result['in_array'])->toBe('available_ids.*');
        expect($result['description'])->toContain('must be values from');
    }

    public function test_it_parses_before_rule()
    {
        // 'before:field' rule requires date to be before another field's value
        $result = RuleParser::parseFieldRule('start_date', 'required|date|before:end_date');

        expect($result['before'])->toBe('end_date');
        expect($result['description'])->toContain('must be before');
    }

    public function test_it_parses_after_rule()
    {
        // 'after:field' rule requires date to be after another field's value
        $result = RuleParser::parseFieldRule('end_date', 'required|date|after:start_date');

        expect($result['after'])->toBe('start_date');
        expect($result['description'])->toContain('must be after');
    }

    public function test_it_parses_before_or_equal_rule()
    {
        // 'before_or_equal:field' rule
        $result = RuleParser::parseFieldRule('deadline', 'required|date|before_or_equal:today');

        expect($result['before_or_equal'])->toBe('today');
        expect($result['description'])->toContain('must be before or equal to');
    }

    public function test_it_parses_after_or_equal_rule()
    {
        // 'after_or_equal:field' rule
        $result = RuleParser::parseFieldRule('birth_date', 'required|date|after_or_equal:1900-01-01');

        expect($result['after_or_equal'])->toBe('1900-01-01');
    }

    public function test_it_parses_complex_password_validation()
    {
        // Complex example: password with confirmation
        $rules = [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $result = RuleParser::parse($rules);

        expect($result['password']['required'])->toBeTrue();
        expect($result['password']['type'])->toBe('string');
        expect($result['password']['min'])->toBe(8);
        expect($result['password']['confirmed'])->toBeTrue();
        expect($result['password']['requires_field'])->toBe('password_confirmation');
    }

    public function test_it_auto_creates_password_confirmation_field()
    {
        // When password has 'confirmed' rule, password_confirmation should be auto-created
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $result = RuleParser::parse($rules);

        // Check that password_confirmation field was automatically added
        expect($result)->toHaveKey('password_confirmation');
        expect($result['password_confirmation']['required'])->toBeTrue();
        expect($result['password_confirmation']['type'])->toBe('string');
        expect($result['password_confirmation']['description'])->toContain('Confirmation of password');
    }

    public function test_it_does_not_duplicate_existing_password_confirmation()
    {
        // If password_confirmation is already defined, don't create a duplicate
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'same:password'],
        ];

        $result = RuleParser::parse($rules);

        // Should have the explicit password_confirmation from rules, not a duplicate
        expect($result)->toHaveKey('password_confirmation');
        expect($result['password_confirmation']['same_as'])->toBe('password');
    }

    public function test_it_auto_creates_confirmation_field_for_any_field_name()
    {
        // Test with new_password field
        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $result = RuleParser::parse($rules);

        // Should auto-create new_password_confirmation
        expect($result)->toHaveKey('new_password_confirmation');
        expect($result['new_password_confirmation']['required'])->toBeTrue();
        expect($result['new_password_confirmation']['type'])->toBe('string');
        expect($result['new_password_confirmation']['description'])->toContain('Confirmation of new_password');
    }

    public function test_it_handles_multiple_confirmed_fields()
    {
        // Test with multiple fields requiring confirmation
        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'old_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $result = RuleParser::parse($rules);

        // Should auto-create all confirmation fields
        expect($result)->toHaveKey('password_confirmation');
        expect($result)->toHaveKey('new_password_confirmation');
        expect($result)->toHaveKey('old_password_confirmation');

        // Verify each confirmation field
        expect($result['password_confirmation']['description'])->toContain('Confirmation of password');
        expect($result['new_password_confirmation']['description'])->toContain('Confirmation of new_password');
        expect($result['old_password_confirmation']['description'])->toContain('Confirmation of old_password');
    }
}
