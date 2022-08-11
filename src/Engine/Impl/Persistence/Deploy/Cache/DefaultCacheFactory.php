<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Commons\Utils\Cache\{
    CacheInterface,
    ConcurrentLruCache
};

class DefaultCacheFactory implements CacheFactoryInterface
{
    public function createCache(int $maxNumberOfElementsInCache): CacheInterface
    {
        return new ConcurrentLruCache($maxNumberOfElementsInCache);
    }
}
