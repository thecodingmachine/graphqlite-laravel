<?php


namespace TheCodingMachine\GraphQLite\Laravel;


use function class_exists;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * A container adapter around Laravel containers that adds a "sane" implementation of PSR-11.
 * Notably, "has" will return true if the class exists, since Laravel is an auto-wiring framework.
 */
class SanePsr11ContainerAdapter implements ContainerInterface
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id): bool
    {
        if (class_exists($id) && !$this->container->has($id)) {
            try {
                $this->container->get($id);
            } catch (EntryNotFoundException $e) {
                return false;
            }
            return true;
        }
        return $this->container->has($id);
    }
}
