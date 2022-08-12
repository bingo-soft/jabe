<?php

namespace Jabe\Impl\Identity;

class IdentityOperationResult
{
    public const OPERATION_CREATE = "create";
    public const OPERATION_UPDATE = "update";
    public const OPERATION_DELETE = "delete";
    public const OPERATION_UNLOCK = "unlock";
    public const OPERATION_NONE = "none";

    protected $value;
    protected $operation;

    public function __construct(\Serializable $value, string $operation)
    {
        $this->value = $value;
        $this->operation = $operation;
    }

    public function getValue(): \Serializable
    {
        return $this->value;
    }

    public function setValue(\Serializable $value): void
    {
        $this->value = $value;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }
}
