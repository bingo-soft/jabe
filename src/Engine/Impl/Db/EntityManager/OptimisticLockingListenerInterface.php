<?php

namespace Jabe\Engine\Impl\Db\EntityManager;

use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;

interface OptimisticLockingListenerInterface
{
    /**
     * The type of the entity for which this listener should be notified.
     * If the implementation returns 'null', the listener is notified for all
     * entity types.
     *
     * @return the entity type for which the listener should be notified.
     */
    public function getEntityType(): string;

    /**
     * Signifies that an operation failed due to optimistic locking.
     *
     * @param operation the failed operation.
     * @return {@link OptimisticLockingResult} that instructs the caller how to handle
     *            the result of the failed operation.
     */
    public function failedOperation(DbOperation $operation): string;
}
