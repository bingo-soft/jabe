<?php

namespace Jabe\Impl\Db\EntityManager\Operation;

class DbOperationState
{
    public const NOT_APPLIED = 'not_applied';
    public const APPLIED = 'applied';

    /**
     * Indicates that the operation was not performed for any reason except
     * concurrent modifications.
     */
    public const FAILED_ERROR = 'failed_error';

    /**
     * Indicates that the operation was not performed and that the reason
     * was a concurrent modification to the data to be updated.
     * Applies to databases with isolation level READ_COMMITTED.
     */
    public const FAILED_CONCURRENT_MODIFICATION = 'failed_concurrent_modification';
}
