<?php

namespace Jabe\Engine\Impl\Db\EntityManager\Operation;

use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    HasDbReferencesInterface
};

class DbOperationManager
{
    // comparators ////////////////

    public $inserts = [];

    /** UPDATEs of a single entity */
    public $updates = [];

    /** DELETEs of a single entity */
    public $deletes = [];

    /** bulk modifications (DELETE, UPDATE) on an entity collection */
    public $bulkOperations = [];

    /** bulk modifications (DELETE, UPDATE) for which order of execution is important */
    public $bulkOperationsInsertionOrder = [];

    public function addOperation(DbEntityOperation $newOperation): bool
    {
        if ($newOperation instanceof DbEntityOperation) {
            $clazz = get_class($newOperation);
            if ($newOperation->getOperationType() == DbOperationType::INSERT) {
                if (!array_key_exists($clazz, $this->inserts)) {
                    $this->inserts[$clazz] = [];
                }
                if (!in_array($newOperation, $this->inserts[$clazz])) {
                    $this->inserts[$clazz][] = $newOperation;
                    return true;
                }
            } elseif ($newOperation->getOperationType() == DbOperationType::DELETE) {
                if (!array_key_exists($clazz, $this->deletes)) {
                    $this->deletes[$clazz] = [];
                }
                if (!in_array($newOperation, $this->deletes)) {
                    $this->deletes[$clazz][] = $newOperation;
                    return true;
                }
            } else {// UPDATE
                if (!array_key_exists($clazz, $this->updates)) {
                    $this->updates[$clazz] = [];
                }
                if (!in_array($newOperation, $this->updates)) {
                    $this->updates[$clazz][] = $newOperation;
                    return true;
                }
            }
        } elseif ($newOperation instanceof DbBulkOperation) {
            if (!array_key_exists($clazz, $this->bulkOperations)) {
                $this->bulkOperations[$clazz] = [];
            }
            if (!in_array($newOperation, $this->bulkOperations)) {
                $this->bulkOperations[$clazz][] = $newOperation;
                return true;
            }
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
        foreach ($this->inserts as $clazz => $operationsForType) {
            // add inserts to flush
            if (is_a($clazz, HasDbReferencesInterface::class, true)) {
                // if this type has self references, we need to resolve the reference order
                $flush = array_merge($flush, $this->sortByReferences($operationsForType));
            } else {
                $flush = array_merge($flush, $operationsForType);
            }
        }
    }

    /** Adds a correctly ordered list of UPDATE and DELETE operations to the flush.
     * @param flush */
    protected function addSortedModifications(array &$flush): void
    {
        // calculate sorted set of all modified entity types
        $modifiedEntityTypes = [];
        $modifiedEntityTypes = array_keys($this->updates);
        $modifiedEntityTypes = array_merge($modifiedEntityTypes, array_keys($this->deletes));
        $modifiedEntityTypes = array_merge($modifiedEntityTypes, array_keys($this->bulkOperations));

        foreach ($modifiedEntityTypes as $type) {
            // first perform entity UPDATES
            $this->addSortedModificationsForType($type, $this->updates[$type], $flush);
            // next perform entity DELETES
            $this->addSortedModificationsForType($type, $this->deletes[$type], $flush);
            // last perform bulk operations
            if (array_key_exists($type, $this->bulkOperations)) {
                $bulkOperationsForType = $this->bulkOperations[$type];
                $flush = array_merge($flush, $bulkOperationsForType);
            }
        }

        //the very last perform bulk operations for which the order is important
        if (!empty($this->bulkOperationsInsertionOrder)) {
            $flush = array_merge($flush, $this->bulkOperationsInsertionOrder);
        }
    }

    protected function addSortedModificationsForType(string $type, array $preSortedOperations, array &$flush): void
    {
        if (!empty($preSortedOperations)) {
            if (is_a($type, HasDbReferencesInterface::class, true)) {
                // if this type has self references, we need to resolve the reference order
                $flush = array_merge($flush, $this->sortByReferences($preSortedOperations));
            } else {
                $flush = array_merge($flush, $preSortedOperations);
            }
        }
    }

    /**
     * Assumptions:
     * a) all operations in the set work on entities such that the entities implement {@link HasDbReferences}.
     * b) all operations in the set work on the same type (ie. all operations are INSERTs or DELETEs).
     *
     */
    protected function sortByReferences(array $preSorted): array
    {
        // copy the pre-sorted set and apply final sorting to list
        $opList = $preSorted;

        for ($i = 0; $i < count($opList); $i += 1) {
            $currentOperation = $opList[$i];
            $currentEntity = $currentOperation->getEntity();
            $currentReferences = $currentOperation->getFlushRelevantEntityReferences();

            // check whether this operation must be placed after another operation
            $moveTo = $i;
            for ($k = $i + 1; $k < count($opList); $k += 1) {
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
                unset($opList[$i]);
                $opList[$moveTo] = $currentOperation;
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
                            if (array_key_exists($type, $this->deletes)) {
                                foreach ($this->deletes[$type] as $o) {
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
