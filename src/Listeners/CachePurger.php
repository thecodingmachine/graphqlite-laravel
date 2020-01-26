<?php


namespace TheCodingMachine\GraphQLite\Laravel\Listeners;


use Psr\SimpleCache\CacheInterface;

class CachePurger
{
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        $this->cache->clear();
    }
}
