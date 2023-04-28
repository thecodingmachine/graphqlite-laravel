<?php

namespace TheCodingMachine\GraphQLite\Laravel\Console\Commands;


use Orchestra\Testbench\TestCase;
use TheCodingMachine\GraphQLite\Laravel\Providers\GraphQLiteServiceProvider;
use TheCodingMachine\TDBM\TDBMService;


class GraphqliteExportSchemaTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GraphQLiteServiceProvider::class];
    }

    public function testCommand(): void
    {
        $this->artisan('graphqlite:export-schema -O test.graphql')
            ->assertExitCode(0);

        $this->assertFileExists('test.graphql');
        $content = file_get_contents('test.graphql');
        $this->assertStringContainsString('type Query {', $content);
        unlink('test.graphql');
    }
}
