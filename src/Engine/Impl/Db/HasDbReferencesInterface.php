<?php

namespace BpmPlatform\Engine\Impl\Db;

interface HasDbReferencesInterface
{
    /**
     * <p>Scope: IN-MEMORY references
     *
     * @return the ids of the entities that this entity references. Should
     *   only return ids for entities of the same type
     */
    public function getReferencedEntityIds(): array;

    /**
     * <p>Scope: IN-MEMORY references
     *
     * @return a map of the ids and the entities' classes that this
     * entity references. It's used when trying to determine if there
     * was an Optimistic Locking occurrence on an INSERT or UPDATE of
     * an object of this type.
     */
    public function getReferencedEntitiesIdAndClass(): array;

    /**
     * <p>Scope: PERSISTED references
     */
    public function getDependentEntities(): array;
}
