<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;

use BadMethodCallException;
use Orchestra\Testbench\TestCase;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;

class ValidateTest extends TestCase
{
    public function testStringArgument(): void
    {
        $validator = new Validate('any-rule');
        $this->assertEquals('any-rule', $validator->getRule());
    }

    public function testArrayArgument(): void
    {
        $validator = new Validate(['rule' => 'any-rule']);
        $this->assertEquals('any-rule', $validator->getRule());
    }

    public function testArrayArgumentWithRuleAndForProperties(): void
    {
        $validator = new Validate([
            'rule' => 'any-rule',
            'for' => 'any-for',
        ]);
        $this->assertEquals('any-rule', $validator->getRule());
        $this->assertEquals('any-for', $validator->getTarget());
    }

    public function testRuleShouldNotBeEmpty(): void
    {
        $this->expectException(BadMethodCallException::class);
        $validator = new Validate('');
    }
}
