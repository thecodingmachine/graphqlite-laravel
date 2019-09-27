<?php

namespace TheCodingMachine\GraphQLite\Laravel\Providers;


use Orchestra\Testbench\TestCase;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\TDBM\TDBMService;

class GraphQLiteServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GraphQLiteServiceProvider::class];
    }

    public function testServiceProvider()
    {
        $schema = $this->app->make(Schema::class);
        $this->assertInstanceOf(Schema::class, $schema);
    }

    public function testHttpQuery()
    {
        $response = $this->json('POST', '/graphql', ['query' => '{ test }']);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $response->assertJson(["data" => ["test" => "foo"]]);
    }

    public function testAuthentication()
    {
        $response = $this->json('POST', '/graphql', ['query' => '{ testLogged }']);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $response->assertJson(["errors" => [["message" => "You need to be logged to access this field"]]]);
    }
}
