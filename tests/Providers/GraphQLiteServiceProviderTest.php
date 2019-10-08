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
        $this->assertSame(401, $response->getStatusCode(), $response->getContent());
        $response->assertJson(["errors" => [["message" => "You need to be logged to access this field"]]]);
    }

    public function testPagination()
    {
        $response = $this->json('POST', '/graphql', ['query' => <<<GQL
{ 
    testPaginator {
        items
        firstItem
        lastItem
        hasMorePages
        perPage
        hasPages
        currentPage
        isEmpty
        isNotEmpty
        totalCount
        lastPage
    }
}
GQL
]);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $response->assertJson([
    "data" => [
        "testPaginator" => [
            "items" => [
                1,
                2,
                3,
                4
            ],
            "firstItem" => 5,
            "lastItem" => 8,
            "hasMorePages" => true,
            "perPage" => 4,
            "hasPages" => true,
            "currentPage" => 2,
            "isEmpty" => false,
            "isNotEmpty" => true,
            "totalCount" => 42,
            "lastPage" => 11,
        ]
    ]
]);
    }

    public function testValidator()
    {
        $response = $this->json('POST', '/graphql', ['query' => '{ testValidator(foo:"a", bar:0) }']);
        $response->assertJson([
            'errors' => [
                [
                    'message' => 'The foo must be a valid email address.',
                    'extensions' => [
                        'argument' => 'foo',
                        'category' => 'Validate'
                    ],
                ],
                [
                    'message' => 'The bar must be greater than 42.',
                    'extensions' => [
                        'argument' => 'bar',
                        'category' => 'Validate'
                    ],
                ]
            ]
        ]);

        $this->assertSame(400, $response->getStatusCode(), $response->getContent());
    }

    public function testValidatorMultiple()
    {
        $response = $this->json('POST', '/graphql', ['query' => '{ testValidatorMultiple(foo:"191.168.1") }']);
        $response->assertJson([
            'errors' => [
                [
                    'message' => 'The foo must start with one of the following: 192',
                    'extensions' => [
                        'argument' => 'foo',
                        'category' => 'Validate'
                    ],
                ],
                [
                    'message' => 'The foo must be a valid IPv4 address.',
                    'extensions' => [
                        'argument' => 'foo',
                        'category' => 'Validate'
                    ],
                ]
            ]
        ]);

        $this->assertSame(400, $response->getStatusCode(), $response->getContent());
        $response = $this->json('POST', '/graphql', ['query' => '{ testValidatorMultiple(foo:"192.168.1") }']);
        $response->assertJson([
            'errors' => [
                [
                    'message' => 'The foo must be a valid IPv4 address.',
                    'extensions' => [
                        'argument' => 'foo',
                        'category' => 'Validate'
                    ],
                ]
            ]
        ]);

        $this->assertSame(400, $response->getStatusCode(), $response->getContent());

        $this->assertSame(400, $response->getStatusCode(), $response->getContent());
        $response = $this->json('POST', '/graphql', ['query' => '{ testValidatorMultiple(foo:"191.168.1.1") }']);
        $response->assertJson([
            'errors' => [
                [
                    'message' => 'The foo must start with one of the following: 192',
                    'extensions' => [
                        'argument' => 'foo',
                        'category' => 'Validate'
                    ],
                ]
            ]
        ]);

        $this->assertSame(400, $response->getStatusCode(), $response->getContent());

        $response = $this->json('POST', '/graphql', ['query' => '{ testValidatorMultiple(foo:"192.168.1.1") }']);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
    }
}
