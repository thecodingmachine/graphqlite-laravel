<?php


namespace App\Http\Controllers;


use Illuminate\Pagination\LengthAwarePaginator;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;

/**
 * PHP attributes and not Doctrine annotations: GraphQLite 8 no longer reads the latter.
 */
class TestController
{
    #[Query]
    public function test(): string
    {
        return 'foo';
    }

    #[Query]
    #[Logged]
    public function testLogged(): string
    {
        return 'foo';
    }

    /**
     * @return int[]
     */
    #[Query]
    public function testPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([1,2,3,4], 42, 4, 2);
    }

    #[Query]
    public function testValidator(
        #[Validate("email")]
        string $foo,
        #[Validate("gt:42")]
        int $bar,
    ): string {
        return 'success';
    }

    #[Query]
    public function testValidatorMultiple(
        #[Validate("starts_with:192|ipv4")]
        string $foo,
    ): string {
        return 'success';
    }

    #[Query]
    public function testValidatorForParameterPHP8(
        #[Validate("required")]
        string $foo,
        #[Validate("sometimes")]
        null|string $bar,
    ): string {
        return 'success';
    }
}
