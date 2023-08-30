<?php

namespace Jabe\Impl\Db\EntityManager\Cache;

use Jabe\Impl\Db\{
    HasDbReferencesInterface,
    DbEntityInterface
};
use Jabe\Impl\Db\EntityManager\RecyclableInterface;

class CachedDbEntity implements RecyclableInterface
{
    protected $dbEntity;

    protected $copy;

    protected $entityState;

    /**
     * Ids of referenced entities of the same entity type
     */
    protected $flushRelevantEntityReferences = [];

    public function recycle(): void
    {
        // clean out state
        $this->dbEntity = null;
        $this->copy = null;
        $this->entityState = null;
    }

    /**
     * Allows checking whether this entity is dirty.
     * @return bool - true if the entity is dirty (state has changed since it was put into the cache)
     */
    public function isDirty(): bool
    {
        return $this->dbEntity->getPersistentState() != $this->copy;
    }

    public function forceSetDirty(): void
    {
        // set the value of the copy to some value which will always be different from the new entity state.
        $this->copy = -1;
    }

    public function makeCopy(): void
    {
        $this->copy = $this->dbEntity->getPersistentState();
    }

    public function __toString()
    {
        return $this->entityState . " " . get_class($this->dbEntity) . "[" . $this->dbEntity->getId() . "]";
    }

    public function determineEntityReferences(): void
    {
        if ($this->dbEntity instanceof HasDbReferencesInterface) {
            $this->flushRelevantEntityReferences = $this->dbEntity->getReferencedEntityIds();
        } else {
            $this->flushRelevantEntityReferences = [];
        }
    }

    public function areFlushRelevantReferencesDetermined(): bool
    {
        return !empty($this->flushRelevantEntityReferences);
    }

    public function getFlushRelevantEntityReferences(): array
    {
        return $this->flushRelevantEntityReferences;
    }

    // getters / setters ////////////////////////////
    public function getEntity(): ?DbEntityInterface
    {
        return $this->dbEntity;
    }

    public function setEntity(DbEntityInterface $dbEntity): void
    {
        $this->dbEntity = $dbEntity;
    }

    public function getEntityState(): ?string
    {
        return $this->entityState;
    }

    public function setEntityState(?string $entityState): void
    {
        $this->entityState = $entityState;
    }

    public function getEntityType(): ?string
    {
        return $this->dbEntity !== null ? get_class($this->dbEntity) : null;
    }
}
