<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;


use BadMethodCallException;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\StandardServer;
use Orchestra\Testbench\TestCase;
use TheCodingMachine\GraphQLite\Http\HttpCodeDeciderInterface;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;
use TheCodingMachine\GraphQLite\Laravel\Listeners\CachePurger;
use TheCodingMachine\GraphQLite\Schema;
use Illuminate\Http\Request;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use TheCodingMachine\GraphQLite\Laravel\Controllers\GraphQLiteController;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
