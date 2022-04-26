<?php

namespace Jabe\Engine\Impl\Db\EntityManager\Operation;

use Jabe\Engine\Impl\Db\EntityManager\RecyclableInterface;

abstract class DbOperation implements RecyclableInterface
{
    /**
     * The type of the operation.
     */
    protected $operationType;

    protected $rowsAffected;
    protected $failure;
    protected $state;

    /**
     * The type of the DbEntity this operation is executed on.
     */
    protected $entityType;

    public function recycle(): void
    {
        // clean out the object state
        $this->operationType = null;
        $this->entityType = null;
    }

    // getters / setters //////////////////////////////////////////

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): void
    {
        $this->entityType = $entityType;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): void
    {
        $this->operationType = $operationType;
    }

    public function getRowsAffected(): int
    {
        return $this->rowsAffected;
    }

    public function setRowsAffected(int $rowsAffected): void
    {
        $this->rowsAffected = $rowsAffected;
    }

    public function isFailed(): bool
    {
        return $this->state == DbOperationState::FAILED_CONCURRENT_MODIFICATION
            || $this->state == DbOperationState::FAILED_CONCURRENT_MODIFICATION_CRDB
            || $this->state == DbOperationState::FAILED_ERROR;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getFailure(): ?\Exception
    {
        return $this->failure;
    }

    public function setFailure(\Exception $failure): void
    {
        $this->failure = $failure;
    }
}
