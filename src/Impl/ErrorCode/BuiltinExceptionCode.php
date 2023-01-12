<?php

namespace Jabe\Impl\ErrorCode;

class BuiltinExceptionCode
{

    /**
     * The code assigned to a {@link ProcessEngineException} when no other code is assigned.
     */
    public const FALLBACK = 0;

    /**
     * This code is assigned when an {@link OptimisticLockingException} or {@link CrdbTransactionRetryException} occurs.
     */
    public const OPTIMISTIC_LOCKING = 1;

    /**
     * This code is assigned when a "deadlock" persistence exception is detected.
     */
    public const DEADLOCK = 10000;

    /**
     * This code is assigned when a "foreign key constraint violation" persistence exception is detected.
     */
    public const FOREIGN_KEY_CONSTRAINT_VIOLATION = 10001;

    /**
     * This code is assigned when a "column size too small" persistence exception is detected.
     */
    public const COLUMN_SIZE_TOO_SMALL = 10002;

    protected int $code = 0;

    public function __construct(int $code)
    {
        $this->code = $code;
    }

    public function getCode(): int
    {
        return $this->code;
    }
}
