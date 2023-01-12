<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

class EntityTypeComparatorForInserts extends EntityTypeComparatorForModifications
{
    public function compareTo(/*string*/$firstEntityType, /*string*/$secondEntityType): int
    {
        return parent::compareTo($firstEntityType, $secondEntityType) * (-1);
    }
}
