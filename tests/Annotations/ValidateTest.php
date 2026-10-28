<?php

namespace TheCodingMachine\GraphQLite\Laravel\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
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
        new Validate('');
    }

    public function testArrayWithoutRuleThrows(): void
    {
        $this->expectException(BadMethodCallException::class);
        new Validate(['for' => 'x']);
    }

    public function testNonStringRuleThrows(): void
    {
        $this->expectException(BadMethodCallException::class);
        new Validate(['for' => 'x', 'rule' => ['required', 'email']]);
    }

    public function testDoctrineValueKey(): void
    {
        $this->assertEquals('email', (new Validate(['value' => 'email']))->getRule());
    }

    public function testZeroRuleIsAccepted(): void
    {
        $this->assertEquals('0', (new Validate('0'))->getRule());
    }
}
