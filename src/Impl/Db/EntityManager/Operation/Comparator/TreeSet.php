<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

class TreeSet extends \ArrayObject
{
    private ComparatorInterface $comparator;

    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    public function add($object)
    {
        if (!in_array($object, $this->getArrayCopy())) {
            $this[] = $object;
            $comparator = $this->comparator;
            $this->uasort(function ($a, $b) use ($comparator) {
                return $comparator->compareTo($a, $b);
            });
        }
    }
}
