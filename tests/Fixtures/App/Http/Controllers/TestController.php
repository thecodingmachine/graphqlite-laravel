<?php


namespace App\Http\Controllers;


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
}