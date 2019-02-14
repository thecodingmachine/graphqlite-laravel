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
        $response = $this->json('POST', '/graphql', ['query' => '{ dummyQuery }']);
        $this->assertSame(200, $response->getStatusCode());
        $response->assertJson(["data" => ["dummyQuery" => "This is a placeholder query. Please create a query using the @Query annotation."]]);
    }
}
