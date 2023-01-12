<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

class DbBulkOperationComparator implements ComparatorInterface
{
    public function compareTo(/*DbBulkOperation*/$firstOperation, /*DbBulkOperation*/$secondOperation): int
    {
        if ($firstOperation->equals($secondOperation)) {
            return 0;
        }

        // order by statement
        $statementOrder = strcmp($firstOperation->getStatement(), $secondOperation->getStatement());

        return $statementOrder;
    }
}
