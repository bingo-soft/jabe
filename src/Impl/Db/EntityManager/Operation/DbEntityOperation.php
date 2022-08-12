<?php

namespace Jabe\Impl\Db\EntityManager\Operation;

use Jabe\Impl\Util\ClassNameUtil;
use Jabe\Impl\Db\DbEntityInterface;

class DbEntityOperation extends DbOperation
{
    /**
     * The entity the operation is performed on.
     */
    protected $entity;

    protected $flushRelevantEntityReferences;

    protected $dependentOperation;

    public function recycle(): void
    {
        $this->entity = null;
        parent::recycle();
    }

    public function getEntity(): ?DbEntityInterface
    {
        return $this->entity;
    }

    public function setEntity(DbEntityInterface $dbEntity): void
    {
        $this->entityType = get_class($dbEntity);
        $this->entity = $dbEntity;
    }

    public function setFlushRelevantEntityReferences(array $flushRelevantEntityReferences): void
    {
        $this->flushRelevantEntityReferences = $flushRelevantEntityReferences;
    }

    public function getFlushRelevantEntityReferences(): array
    {
        return $this->flushRelevantEntityReferences;
    }

    public function __toString()
    {
        return $this->operationType . " " . ClassNameUtil::getClassNameWithoutPackage(get_class($this->entity)) . "[" . $this->entity->getId() . "]";
    }

    public function setDependency(DbOperation $owner): void
    {
        $this->dependentOperation = $owner;
    }

    public function getDependentOperation(): ?DbOperation
    {
        return $this->dependentOperation;
    }

    public function equals($obj): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->entity === null) {
            if ($obj->entity !== null) {
                return false;
            }
        } elseif ($this->entity != $obj->entity) {
            return false;
        }
        if ($this->operationType != $obj->operationType) {
            return false;
        }
        return true;
    }
}
