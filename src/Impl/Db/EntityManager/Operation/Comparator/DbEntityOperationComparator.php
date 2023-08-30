<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

class DbEntityOperationComparator implements ComparatorInterface
{
    public function compareTo(/*DbEntityOperation*/$firstOperation, /*DbEntityOperation*/$secondOperation): int
    {
        if ($firstOperation->equals($secondOperation)) {
            return 0;
        }

        $firstEntity = $firstOperation->getEntity();
        $secondEntity = $secondOperation->getEntity();

        return strcmp($firstEntity->getId(), $secondEntity->getId());
    }
}
