<?php

namespace Jabe\Engine\Commons\Utils\Cache;

class ConcurrentLruCache implements CacheInterface
{
    private $capacity;

    private $cache = [];
    private $keys = [];

    /**
     * Creates the cache with a fixed capacity.
     *
     * @param capacity max number of cache entries
     * @throws IllegalArgumentException if capacity is negative
     */
    public function __construct(int $capacity)
    {
        if ($capacity < 0) {
            throw new \Exception("Illegal argument for cache capacity");
        }
        $this->capacity = $capacity;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->cache)) {
            $value = $this->cache[$key];
            if ($value !== null) {
                foreach ($this->keys as $innerKey => $value) {
                    if ($value == $key) {
                        unset($this->keys[$innerKey]);
                        break;
                    }
                }
                $this->keys[] = $key;
            }
            return $value;
        }
        return null;
    }

    public function put($key, $value): void
    {
        if ($key === null || $value === null) {
            throw new \Exception("NullPointer");
        }

        $previousValue = null;
        if (array_key_exists($key, $this->cache)) {
            $previousValue = $this->cache[$key];
        }
        $this->cache[$key] = $value;
        if ($previousValue !== null) {
            foreach ($this->keys as $innerKey => $value) {
                if ($value == $key) {
                    unset($this->keys[$innerKey]);
                }
            }
        }
        $this->keys[] = $key;

        if (count($this->cache) > $this->capacity) {
            $lruKey = array_shift($this->keys);
            if ($lruKey !== null) {
                foreach ($this->cache as $innerKey => $value) {
                    if ($value == $lruKey) {
                        unset($this->cache[$innerKey]);
                    }
                }

                // remove duplicated keys
                $this->removeAll($lruKey);

                // queue may not contain any key of a possibly concurrently added entry of the same key in the cache
                if (array_key_exists($lruKey, $this->cache)) {
                    $this->keys[] = $lruKey;
                }
            }
        }
    }

    public function remove($key): void
    {
        foreach ($this->keys as $innerKey => $value) {
            if ($value == $key) {
                unset($this->keys[$innerKey]);
            }
        }
        foreach ($this->cache as $innerKey => $value) {
            if ($value == $key) {
                unset($this->cache[$innerKey]);
            }
        }
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->keys = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->cache);
    }

    public function keySet(): array
    {
        return array_keys($this->cache);
    }

    public function size(): int
    {
        return count($this->cache);
    }

    /**
     * Removes all instances of the given key within the keys queue.
     */
    protected function removeAll($key): void
    {
        foreach ($this->keys as $innerKey => $value) {
            if ($value == $key) {
                unset($this->keys[$innerKey]);
            }
        }
    }
}
