<?php


namespace App\Http\Controllers;


use Illuminate\Pagination\LengthAwarePaginator;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;

class TestController
{
    /**
     * @Query()
     */
    public function test(): string
    {
        return 'foo';
    }

    /**
     * @Query()
     * @Logged()
     */
    public function testLogged(): string
    {
        return 'foo';
    }

    /**
     * @Query()
     * @return int[]
     */
    public function testPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([1,2,3,4], 42, 4, 2);
    }

    /**
     * @Query()
     * @Validate(for="foo", rule="email")
     * @Validate(for="bar", rule="gt:42")
     */
    public function testValidator(string $foo, int $bar): string
    {
        return 'success';
    }

    /**
     * @Query()
     * @Validate(for="foo", rule="starts_with:192|ipv4")
     */
    public function testValidatorMultiple(string $foo): string
    {
        return 'success';
    }

    /** @Query() */
    public function testValidatorForParameterPHP8(
        #[Validate("required")]
        string $foo,
        #[Validate("sometimes")]
        null|string $bar,
    ): string {
        return 'success';
    }
}
