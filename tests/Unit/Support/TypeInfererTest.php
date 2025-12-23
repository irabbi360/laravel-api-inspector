<?php

namespace Irabbi360\LaravelApiInspector\Tests\Unit\Support;

use Irabbi360\LaravelApiInspector\Support\TypeInferer;
use Irabbi360\LaravelApiInspector\Tests\TestCase;

class TypeInfererTest extends TestCase
{
    public function test_it_infers_example_from_type()
    {
        expect(TypeInferer::inferExample('name', 'int'))->toBe(1);
        expect(TypeInferer::inferExample('name', 'bool'))->toBeTrue();
        expect(TypeInferer::inferExample('name', 'array'))->toBe([]);
    }

    public function test_it_infers_example_from_field_name()
    {
        expect(TypeInferer::inferExample('email'))->toBe('user@example.com');
        expect(TypeInferer::inferExample('password'))->toBe('password123');
        expect(TypeInferer::inferExample('phone'))->toBe('1234567890');
        expect(TypeInferer::inferExample('active'))->toBeTrue();
    }

    public function test_it_converts_rule_to_type()
    {
        expect(TypeInferer::ruleToType('email'))->toBe('string');
        expect(TypeInferer::ruleToType('numeric'))->toBe('integer');
        expect(TypeInferer::ruleToType('boolean'))->toBe('boolean');
        expect(TypeInferer::ruleToType('array'))->toBe('array');
    }

    public function test_it_gets_format_for_rules()
    {
        expect(TypeInferer::getFormat('email'))->toBe('email');
        expect(TypeInferer::getFormat('date'))->toBe('date');
        expect(TypeInferer::getFormat('url'))->toBe('uri');
        expect(TypeInferer::getFormat('string'))->toBeNull();
    }

    public function test_it_checks_if_field_is_required()
    {
        expect(TypeInferer::isRequired('required|email'))->toBeTrue();
        expect(TypeInferer::isRequired('email|nullable'))->toBeFalse();
    }

    public function test_it_extracts_constraints()
    {
        $constraints = TypeInferer::extractConstraints('min:5|max:100|in:active,inactive');

        expect($constraints['min'])->toBe(5);
        expect($constraints['max'])->toBe(100);
        expect($constraints['enum'])->toContain('active');
        expect($constraints['enum'])->toContain('inactive');
    }
}
