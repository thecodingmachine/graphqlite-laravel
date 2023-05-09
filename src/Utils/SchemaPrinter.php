<?php

namespace TheCodingMachine\GraphQLite\Laravel\Utils;

use GraphQL\Type\Schema;

class SchemaPrinter extends \GraphQL\Utils\SchemaPrinter
{
    /**
     * In GraphQLite, getType throws an exception if not found. The "Subscription" type is never present.
     * So it throws always. We reimplement this method to avoid throwing.
     */
    protected static function hasDefaultRootOperationTypes(Schema $schema): bool
    {
        return $schema->getQueryType() && $schema->getQueryType() === $schema->getType('Query')
            && $schema->getMutationType() && $schema->getMutationType() === $schema->getType('Mutation');
    }
}