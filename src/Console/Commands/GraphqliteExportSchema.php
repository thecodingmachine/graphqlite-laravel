<?php

namespace TheCodingMachine\GraphQLite\Laravel\Console\Commands;

use Illuminate\Console\Command;
use TheCodingMachine\GraphQLite\Laravel\Utils\SchemaPrinter;
use TheCodingMachine\GraphQLite\Schema;

/**
 * A command to export the GraphQL schema in "Schema Definition Language" (SDL) format.
 */
class GraphqliteExportSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphqlite:export-schema {--O|output= : Output file name. If not specified, prints on stdout}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports the GraphQL schema in "Schema Definition Language" (SDL) format.';

    public function __construct(private Schema $schema)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = $this->option('output');

        $sdl = SchemaPrinter::doPrint($this->schema, [
            "sortArguments" => true,
            "sortEnumValues" => true,
            "sortFields" => true,
            "sortInputFields" => true,
            "sortTypes" => true,
        ]);

        if ($output === null) {
            $this->line($sdl);
        } else {
            file_put_contents($output, $sdl);
        }

        return Command::SUCCESS;
    }
}
