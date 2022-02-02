<?php

namespace BpmPlatform\Engine\Impl\Persistence\Deploy\Cache;

use BpmPlatform\Commons\Utils\Cache\{
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
