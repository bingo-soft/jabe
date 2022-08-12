<?php

namespace Jabe\Impl\OpLog;

class UserOperationLogContext
{
    protected $operationId;
    protected $userId;
    protected $entries = [];

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function setOperationId(string $operationId): void
    {
        $this->operationId = $operationId;
    }

    public function addEntry(UserOperationLogContextEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }
}
