<?php

namespace BpmPlatform\Engine\Impl\Db\EntityManager\Operation;

class DbBulkOperation extends AbstractDbOperation
{
    protected $statement;
    protected $parameter;

    public function __construct(string $operationType, string $entityType, string $statement, $parameter)
    {
        $this->operationType = $operationType;
        $this->entityType = $entityType;
        $this->statement = $statement;
        $this->parameter = $parameter;
    }

    public function recycle(): void
    {
        $this->statement = null;
        $this->parameter = null;
        parent::recycle();
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function setParameter($parameter): void
    {
        $this->parameter = $parameter;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    public function __toString()
    {
        return $this->operationType . " " . $this->statement . " " . $this->parameter;
    }

    public function equals($obj = null): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj == null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->parameter == null) {
            if ($obj->parameter != null) {
                return false;
            }
        } elseif ($this->parameter != $other->parameter) {
            return false;
        }
        if ($this->statement == null) {
            if ($obj->statement != null) {
                return false;
            }
        } elseif ($this->statement != $obj->statement) {
            return false;
        }
        return true;
    }
}
