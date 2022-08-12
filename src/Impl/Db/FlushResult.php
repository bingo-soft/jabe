<?php

namespace Jabe\Impl\Db;

class FlushResult
{
    protected $failedOperations = [];
    protected $remainingOperations = [];

    public function __construct(array $failedOperations = [], array $remainingOperations = [])
    {
        $this->failedOperations = $failedOperations;
        $this->remainingOperations = $remainingOperations;
    }

    /**
     * @return array the operation that could not be performed
     */
    public function getFailedOperations(): array
    {
        return $this->failedOperations;
    }

    /**
     * @return operations that were not applied, because a preceding operation failed
     */
    public function getRemainingOperations(): array
    {
        return $this->remainingOperations;
    }

    public function hasFailures(): bool
    {
        return !empty($this->failedOperations);
    }

    public function hasRemainingOperations(): bool
    {
        return !empty($this->remainingOperations);
    }

    public static function allApplied(): FlushResult
    {
        return new FlushResult();
    }

    public static function withFailures(array $failedOperations): FlushResult
    {
        return new FlushResult($failedOperations);
    }

    public static function withFailuresAndRemaining(array $failedOperations, array $remainingOperations): FlushResult
    {
        return new FlushResult($failedOperations, $remainingOperations);
    }
}
