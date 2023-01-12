<?php

namespace Jabe\Impl\ErrorCode;

use Doctrine\DBAL\Exception\{
    ConstraintViolationException,
    DeadlockException,
    ForeignKeyConstraintViolationException,
    ServerException
};
use Jabe\{
    OptimisticLockingException,
    ProcessEngineException
};
use Jabe\Impl\Util\ExceptionUtil;

abstract class ExceptionCodeProvider
{
    /**
     * <p>Called when a {@link ProcessEngineException} occurs.
     *
     * <p>Provides the exception code that can be determined based on the passed {@link ProcessEngineException}.
     * Only called when no other provider method is called.
     *
     * @param processEngineException that occurred.
     * @return an integer value representing the error code. When returning {@code null},
     * the {@link BuiltinExceptionCode#FALLBACK} gets assigned to the exception.
     */
    public function provideCode(/*ProcessEngineException*/$processEngineException): ?int
    {
        if ($processEngineException instanceof OptimisticLockingException) {
            return BuiltinExceptionCode::OPTIMISTIC_LOCKING;
        }
        if (
            ExceptionUtil::checkDeadlockException($processEngineException)
        ) {
            return BuiltinExceptionCode::DEADLOCK;
        }
        if (
            ExceptionUtil::checkForeignKeyConstraintViolation($processEngineException, false)
        ) {
            return BuiltinExceptionCode::FOREIGN_KEY_CONSTRAINT_VIOLATION;
        }
        $columnSizeTooSmall = ExceptionUtil::checkValueTooLongException($processEngineException);
        if ($columnSizeTooSmall) {
            return BuiltinExceptionCode::COLUMN_SIZE_TOO_SMALL;
        }
        return null;
    }
}
