<?php


namespace App\Http\Controllers;


use Illuminate\Pagination\LengthAwarePaginator;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

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
}
