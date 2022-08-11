<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Commons\Utils\Cache\CacheInterface;

interface CacheFactoryInterface
{

    /**
     * Creates a cache that does not exceed a specified number of elements.
     *
     * @param maxNumberOfElementsInCache
     *        The maximum number of elements that is allowed within the cache at the same time.
     * @return
     *        The cache to be created.
     */
    public function createCache(int $maxNumberOfElementsInCache): CacheInterface;
}
