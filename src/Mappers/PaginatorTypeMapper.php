<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Laravel\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Porpaginas\Result;
use RuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasMissingParameterException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use function get_class;
use function is_a;
use function strpos;
use function substr;

class PaginatorTypeMapper implements TypeMapperInterface
{
    /** @var array<string, MutableInterface&(MutableObjectType|MutableInterfaceType)> */
    private $cache = [];
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;

    public function __construct(RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     */
    public function canMapClassToType(string $className): bool
    {
        return is_a($className, Paginator::class, true);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @param (OutputType&Type)|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     *
     * @return MutableObjectType|MutableInterfaceType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableInterface
    {
        if (! $this->canMapClassToType($className)) {
            throw CannotMapTypeException::createForType($className);
        }
        if ($subType === null) {
            throw PaginatorMissingParameterException::noSubType();
        }

        return $this->getObjectType(is_a($className, LengthAwarePaginator::class, true), $subType);
    }

    /**
     * @param OutputType&Type $subType
     *
     * @return MutableObjectType|MutableInterfaceType
     */
    private function getObjectType(bool $countable, OutputType $subType): MutableInterface
    {
        if (! isset($subType->name)) {
            throw new RuntimeException('Cannot get name property from sub type ' . get_class($subType));
        }

        $name = $subType->name;

        $typeName = 'PaginatorResult_' . $name;

        if ($subType instanceof NullableType) {
            $subType = Type::nonNull($subType);
        }

        if (! isset($this->cache[$typeName])) {
            $this->cache[$typeName] = new MutableObjectType([
                'name' => $typeName,
                'fields' => static function () use ($subType, $countable) {
                    $fields = [
                        'items' => [
                            'type' => Type::nonNull(Type::listOf($subType)),
                            'resolve' => static function (Paginator $root) {
                                return $root->items();
                            },
                        ],
                        'firstItem' => [
                            'type' => Type::int(),
                            'description' => 'Get the "index" of the first item being paginated.',
                            'resolve' => static function (Paginator $root): int {
                                return $root->firstItem();
                            },
                        ],
                        'lastItem' => [
                            'type' => Type::int(),
                            'description' => 'Get the "index" of the last item being paginated.',
                            'resolve' => static function (Paginator $root): int {
                                return $root->lastItem();
                            },
                        ],
                        'hasMorePages' => [
                            'type' => Type::boolean(),
                            'description' => 'Determine if there are more items in the data source.',
                            'resolve' => static function (Paginator $root): bool {
                                return $root->hasMorePages();
                            },
                        ],
                        'perPage' => [
                            'type' => Type::int(),
                            'description' => 'Get the number of items shown per page.',
                            'resolve' => static function (Paginator $root): int {
                                return $root->perPage();
                            },
                        ],
                        'hasPages' => [
                            'type' => Type::boolean(),
                            'description' => 'Determine if there are enough items to split into multiple pages.',
                            'resolve' => static function (Paginator $root): bool {
                                return $root->hasPages();
                            },
                        ],
                        'currentPage' => [
                            'type' => Type::int(),
                            'description' => 'Determine the current page being paginated.',
                            'resolve' => static function (Paginator $root): int {
                                return $root->currentPage();
                            },
                        ],
                        'isEmpty' => [
                            'type' => Type::boolean(),
                            'description' => 'Determine if the list of items is empty or not.',
                            'resolve' => static function (Paginator $root): bool {
                                return $root->isEmpty();
                            },
                        ],
                        'isNotEmpty' => [
                            'type' => Type::boolean(),
                            'description' => 'Determine if the list of items is not empty.',
                            'resolve' => static function (Paginator $root): bool {
                                return $root->isNotEmpty();
                            },
                        ],
                    ];

                    if ($countable) {
                        $fields['totalCount'] = [
                            'type' => Type::int(),
                            'description' => 'The total count of items.',
                            'resolve' => static function (LengthAwarePaginator $root): int {
                                return $root->total();
                            }];
                        $fields['lastPage'] = [
                            'type' => Type::int(),
                            'description' => 'Get the page number of the last available page.',
                            'resolve' => static function (LengthAwarePaginator $root): int {
                                return $root->lastPage();
                            }];
                    }

                    return $fields;
                },
            ]);
        }

        return $this->cache[$typeName];
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function canMapNameToType(string $typeName): bool
    {
        return strpos($typeName, 'PaginatorResult_') === 0 || strpos($typeName, 'LengthAwarePaginatorResult_') === 0;
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     *
     * @return Type&NamedType&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType|MutableInterfaceType)
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapNameToType(string $typeName): Type&NamedType
    {
        if (strpos($typeName, 'LengthAwarePaginatorResult_') === 0) {
            $subTypeName = substr($typeName, 27);
            $lengthAware = true;
        } elseif (strpos($typeName, 'PaginatorResult_') === 0) {
            $subTypeName = substr($typeName, 16);
            $lengthAware = false;
        } else {
            throw CannotMapTypeException::createForName($typeName);
        }

        $subType = $this->recursiveTypeMapper->mapNameToType($subTypeName);

        if (! $subType instanceof OutputType) {
            throw CannotMapTypeException::mustBeOutputType($subTypeName);
        }

        return $this->getObjectType($lengthAware, $subType);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        // We cannot get the list of all possible porpaginas results but this is not an issue.
        // getSupportedClasses is only useful to get classes that can be hidden behind interfaces
        // and Porpaginas results are not part of those.
        return [];
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     */
    public function canMapClassToInputType(string $className): bool
    {
        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @return ResolvableMutableInputInterface&InputObjectType
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface
    {
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForClass(string $className, MutableInterface $type): bool
    {
        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableInterface $type): void
    {
        throw CannotMapTypeException::createForExtendType($className, $type);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
    {
        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableInterface $type): void
    {
        throw CannotMapTypeException::createForExtendName($typeName, $type);
    }

    /**
     * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
     */
    public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
    {
        return false;
    }

    /**
     * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
     *
     * @param ResolvableMutableInputInterface&InputObjectType $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
    {
        throw CannotMapTypeException::createForDecorateName($typeName, $type);
    }
}
