<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

interface ComparatorInterface
{
    public function compareTo($obj1, $obj2): int;
}
