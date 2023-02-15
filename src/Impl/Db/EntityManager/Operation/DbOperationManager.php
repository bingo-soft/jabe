<?php

namespace Jabe\Impl\Db\EntityManager\Operation;

use Jabe\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface
};
use Jabe\Impl\Db\EntityManager\Operation\Comparator\{
    DbBulkOperationComparator,
    DbEntityOperationComparator,
    EntityTypeComparatorForInserts,
    EntityTypeComparatorForModifications,
    TreeMap,
    TreeSet
};

class DbOperationManager
{
    // comparators ////////////////

    public static $insertTypeComparator;
    public static $modificationTypeComparator;
    public static $insertOperationComparator;
    public static $modificationOperationComparator;
    public static $bulkOperationComparator;

    public $inserts;

    /** UPDATEs of a single entity */
    public $updates;

    /** DELETEs of a single entity */
    public $deletes;

    /** bulk modifications (DELETE, UPDATE) on an entity collection */
    public $bulkOperations;

    /** bulk modifications (DELETE, UPDATE) for which order of execution is important */
    public $bulkOperationsInsertionOrder = [];

    public function __construct()
    {
        if (self::$insertTypeComparator === null) {
            self::$insertTypeComparator = new EntityTypeComparatorForInserts();
            self::$modificationTypeComparator = new EntityTypeComparatorForModifications();
            self::$insertOperationComparator = new DbEntityOperationComparator();
            self::$modificationOperationComparator = new DbEntityOperationComparator();
            self::$bulkOperationComparator = new DbBulkOperationComparator();
        }
        $this->inserts = new TreeMap(self::$insertTypeComparator);
        $this->updates = new TreeMap(self::$modificationTypeComparator);
        $this->deletes = new TreeMap(self::$modificationTypeComparator);
        $this->bulkOperations = new TreeMap(self::$modificationTypeComparator);
    }

    public function addOperation(/*DbEntityOperation|DbBulkOperation*/$newOperation): bool
    {
        $clazz = $newOperation->getEntityType();
        if ($newOperation instanceof DbEntityOperation) {
            if ($newOperation->getOperationType() == DbOperationType::INSERT) {
                if ($this->inserts->get($clazz) === null) {
                    $this->inserts->put($clazz, new TreeSet(self::$insertOperationComparator));
                }
                $this->inserts->get($clazz)->add($newOperation);
                return true;
            } elseif ($newOperation->getOperationType() == DbOperationType::DELETE) {
                if ($this->deletes->get($clazz) === null) {
                    $this->deletes->put($clazz, new TreeSet(self::$modificationOperationComparator));
                }
                $this->deletes->get($clazz)->add($newOperation);
                return true;
            } else {// UPDATE
                if ($this->updates->get($clazz) === null) {
                    $this->updates->put($clazz, new TreeSet(self::$modificationOperationComparator));
                }
                $this->updates->get($clazz)->add($newOperation);
                return true;
            }
        } elseif ($newOperation instanceof DbBulkOperation) {
            if ($this->bulkOperations->get($clazz) === null) {
                $this->bulkOperations->put($clazz, new TreeSet(self::$bulkOperationComparator));
            }
            $this->bulkOperations->get($clazz)->add($newOperation);
            return true;
        }
        return false;
    }

    public function addOperationPreserveOrder(DbBulkOperation $newOperation): bool
    {
        if (!in_array($newOperation, $this->bulkOperationsInsertionOrder)) {
            $this->bulkOperationsInsertionOrder[] = $newOperation;
            return true;
        }
        return false;
    }

    public function calculateFlush(): array
    {
        $flush = [];
        // first INSERTs
        $this->addSortedInserts($flush);
        // then UPDATEs + DELETEs
        $this->addSortedModifications($flush);
        $this->determineDependencies($flush);
        return $flush;
    }

    /** Adds the insert operations to the flush (in correct order).
     * @param operationsForFlush */
    protected function addSortedInserts(array &$flush): void
    {
        $keys = [];
        foreach ($this->inserts as $key => $ops) {
            $keys[] = $key;
        }
        foreach ($this->inserts->getArrayCopy() as $clazz => $operationsForType) {
            // add inserts to flush
            if (is_a($clazz, HasDbReferencesInterface::class, true)) {
                // if this type has self references, we need to resolve the reference order
                array_push($flush, ...$this->sortByReferences($operationsForType->getArrayCopy()));
            } else {
                array_push($flush, ...$operationsForType->getArrayCopy());
            }
        }
    }

    /** Adds a correctly ordered list of UPDATE and DELETE operations to the flush.
     * @param flush */
    protected function addSortedModifications(array &$flush): void
    {
        // calculate sorted set of all modified entity types
        $modifiedEntityTypes = new TreeSet(self::$modificationTypeComparator);
        foreach ($this->updates->getArrayCopy() as $key => $val) {
            $modifiedEntityTypes->add($key);
        }
        foreach ($this->deletes->getArrayCopy() as $key => $val) {
            $modifiedEntityTypes->add($key);
        }
        foreach ($this->bulkOperations->getArrayCopy() as $key => $val) {
            $modifiedEntityTypes->add($key);
        }

        foreach ($modifiedEntityTypes as $type) {
            // first perform entity UPDATES
            if ($this->updates->get($type) !== null) {
                $this->addSortedModificationsForType($type, $this->updates->get($type)->getArrayCopy(), $flush);
            }
            // next perform entity DELETES
            if ($this->deletes->get($type) !== null) {
                $this->addSortedModificationsForType($type, $this->deletes->get($type)->getArrayCopy(), $flush);
            }
            // last perform bulk operations
            if ($this->bulkOperations->get($type) !== null) {
                array_push($flush, ...$this->bulkOperations->get($type)->getArrayCopy());
                //array_splice($flush, count($flush), 0, $this->bulkOperations->get($type)->getArrayCopy());
            }
        }
        //the very last perform bulk operations for which the order is important
        array_push($flush, ...$this->bulkOperationsInsertionOrder);
        //array_splice($flush, count($flush), 0, $this->bulkOperationsInsertionOrder);
    }

    protected function addSortedModificationsForType(?string $type, array $preSortedOperations, array &$flush): void
    {
        if (!empty($preSortedOperations)) {
            if (is_a($type, HasDbReferencesInterface::class, true)) {
                // if this type has self references, we need to resolve the reference order
                array_push($flush, ...$this->sortByReferences($preSortedOperations));
            } else {
                array_push($flush, ...$preSortedOperations);
            }
        }
    }

    /**
     * Assumptions:
     * a) all operations in the set work on entities such that the entities implement HasDbReferences.
     * b) all operations in the set work on the same type (ie. all operations are INSERTs or DELETEs).
     *
     */
    protected function sortByReferences(array $preSorted): array
    {
        // copy the pre-sorted set and apply final sorting to list
        $opList = array_values($preSorted);
        for ($i = 0; $i < count($opList); $i += 1) {
            //$keysOuter = array_keys($opList);
            $currentOperation = $opList[$i];
            $currentEntity = $currentOperation->getEntity();
            $currentReferences = $currentOperation->getFlushRelevantEntityReferences();

            // check whether this operation must be placed after another operation
            $moveTo = $i;
            for ($k = $i + 1; $k < count($opList); $k += 1) {
                //$keysInner = array_keys($opList);
                $otherOperation = $opList[$k];
                $otherEntity = $otherOperation->getEntity();
                $otherReferences = $otherOperation->getFlushRelevantEntityReferences();

                if ($currentOperation->getOperationType() == DbOperationType::INSERT) {
                    // if we reference the other entity, we need to be inserted after that entity
                    if (!empty($currentReferences) && in_array($otherEntity->getId(), $currentReferences)) {
                        $moveTo = $k;
                        break; // we can only reference a single entity
                    }
                } else { // UPDATE or DELETE
                    // if the other entity has a reference to us, we must be placed after the other entity
                    if (!empty($otherReferences) && in_array($currentEntity->getId(), $otherReferences)) {
                        $moveTo = $k;
                        // cannot break, there may be another entity further to the right which also references us
                    }
                }
            }

            if ($moveTo > $i) {
                array_splice($opList, $i, 1, []);
                array_splice($opList, $moveTo, 0, [ $currentOperation ]);
                $i -= 1;
            }
        }

        return $opList;
    }

    protected function determineDependencies(array $flush): void
    {
        $defaultValue = [];
        foreach ($flush as $operation) {
            if ($operation instanceof DbEntityOperation) {
                $entity = $operation->getEntity();
                if ($entity instanceof HasDbReferencesInterface) {
                    $dependentEntities = $entity->getDependentEntities();
                    if (!empty($dependentEntities)) {
                        foreach ($dependentEntities as $id => $type) {
                            if ($this->deletes->get($type) !== null) {
                                foreach ($this->deletes->get($type)->getArrayCopy() as $o) {
                                    if ($id == $o->getEntity()->getId()) {
                                        $o->setDependency($operation);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
