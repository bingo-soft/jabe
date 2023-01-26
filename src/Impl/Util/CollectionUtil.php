<?php

namespace Jabe\Impl\Util;

class CollectionUtil
{
    // No need to instantiate
    private function __construct()
    {
    }

    /**
     * Helper method that creates a singleton map.
     *
     * Alternative for Collections.singletonMap(), since that method returns a
     * generic typed map <K,T> depending on the input type, but we often need a
     * <String, Object> map.
     */
    public static function singletonMap(?string $key, $value): array
    {
        $map = [];
        $map[$key] = $value;
        return $map;
    }

    /**
     * Arrays.asList cannot be reliably used for SQL parameters on MyBatis < 3.3.0
     */
    public static function asArrayList(array $values): array
    {
        return $values;
    }

    public static function asHashSet(array $elements): array
    {
        return $elements;
    }

    public static function addToMapOfLists(array &$map, $key, $value): void
    {
        $list = null;
        if (array_key_exists($key, $map)) {
            $list = $map[$key];
        }
        if ($list === null) {
            $list = [];
            $map[$key] = $list;
        }
        $map[$key][] = $value;
    }

    public static function addToMapOfSets(array &$map, $key, $value): void
    {
        $list = null;
        if (array_key_exists($key, $map)) {
            $list = $map[$key];
        }
        if ($list === null) {
            $list = [];
            $map[$key] = $list;
        }
        $map[$key][] = $value;
    }

    public static function addCollectionToMapOfSets(array &$map, $key, array $values): void
    {
        $set = null;
        if (array_key_exists($key, $map)) {
            $set = $map[$key];
        }
        if ($set === null) {
            $set = [];
            $map[$key] = $set;
        }
        $map[$key] = array_merge($map[$key], $values);
    }

    /**
     * Chops a list into non-view sublists of length partitionSize. Note: the argument list
     * may be included in the result.
     */
    public static function partition(array $list, int $partitionSize): array
    {
        $parts = [];

        $listSize = count($list);

        if ($listSize <= $partitionSize) {
            // no need for partitioning
            $parts[] = $list;
        } else {
            for ($i = 0; $i < $listSize; $i += $partitionSize) {
                $parts[] = array_slice($list, $i, $partitionSize);
            }
        }

        return $parts;
    }

    public static function collectInList(array $data): array
    {
        $result = [];
        foreach ($data as $value) {
            $result[] = $value;
        }
        return $result;
    }

    public static function isEmpty(?array $collection = null): bool
    {
        return empty($collection);
    }
}
