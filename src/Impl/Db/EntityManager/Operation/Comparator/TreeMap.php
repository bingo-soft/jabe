<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

class TreeMap extends \ArrayObject
{
    private ComparatorInterface $comparator;

    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    public function put($key, $object): void
    {
        $this[$key] = $object;
        $comparator = $this->comparator;
        $this->uksort(function ($a, $b) use ($comparator) {
            return $comparator->compareTo($a, $b);
        });
    }

    public function get($key)
    {
        if (isset($this[$key])) {
            return $this[$key];
        }
        return null;
    }
}
