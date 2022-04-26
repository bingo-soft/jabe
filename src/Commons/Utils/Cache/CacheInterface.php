<?php

namespace Jabe\Commons\Utils\Cache;

interface CacheInterface
{
    /**
     * Gets an entry from the cache.
     *
     * @param key the key whose associated value is to be returned
     * @return the element, or <code>null</code>, if it does not exist.
     */
    public function get($key);

    /**
     * Associates the specified value with the specified key in the cache.
     *
     * @param key   key with which the specified value is to be associated
     * @param value value to be associated with the specified key
     * @throws NullPointerException if key is <code>null</code> or if value is <code>null</code>
     */
    public function put($key, $value): void;

    /**
     * Clears the contents of the cache.
     */
    public function clear(): void;

    /**
     * Removes an entry from the cache.
     *
     * @param key key with which the specified value is to be associated.
     */
    public function remove($key): void;

    /**
     * Returns a Set view of the keys contained in this cache.
     */
    public function keySet(): array;

    /**
     * @return the current size of the cache
     */
    public function size(): int;

    /**
     * Returns <code>true</code> if this cache contains no key-value mappings.
     */
    public function isEmpty(): bool;
}
