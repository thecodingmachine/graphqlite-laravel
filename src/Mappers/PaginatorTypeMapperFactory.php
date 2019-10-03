<?php


namespace TheCodingMachine\GraphQLite\Laravel\Mappers;


use TheCodingMachine\GraphQLite\FactoryContext;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;

class PaginatorTypeMapperFactory implements TypeMapperFactoryInterface
{

    public function create(FactoryContext $context): TypeMapperInterface
    {
        return new PaginatorTypeMapper($context->getRecursiveTypeMapper());
    }
}
