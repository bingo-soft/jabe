<?php

namespace Jabe\Engine\Impl\Db\EntityManager\Cache;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger
};

class DbEntityCache
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    /**
     * The cache itself: maps entity types (classes) to maps indexed by id (primary key).
     *
     * The motivation for indexing by type (class) is
     *
     * a) multiple entities of different types could have the same value as primary key. In the
     *    process engine, TaskEntity and HistoricTaskEntity have the same id value.
     *
     * b) performance (?)
     */
    protected $cachedEntites = [];

    protected $cacheKeyMapping;

    public function __construct(DbEntityCacheKeyMapping $cacheKeyMapping = null)
    {
        if ($cacheKeyMapping === null) {
            DbEntityCacheKeyMapping::emptyMapping();
        } else {
            $this->cacheKeyMapping = $cacheKeyMapping;
        }
    }

    /**
     * get an object from the cache
     *
     * @param type the type of the object
     * @param id the id of the object
     * @return mixed the object or 'null' if the object is not in the cache
     * @throws ProcessEngineException if an object for the given id can be found but is of the wrong type.
     */
    public function get(string $type, string $id)
    {
        $cacheKey = $this->cacheKeyMapping->getEntityCacheKey($type);
        $cachedDbEntity = $this->getCachedEntity($cacheKey, $id);
        if ($cachedDbEntity !== null) {
            $dbEntity = $cachedDbEntity->getEntity();
            if (!is_a($dbEntity, $type)) {
                //throw LOG.entityCacheLookupException(type, id, dbEntity.getClass(), null);
                throw new \Exception("entityCacheLookupException");
            }
            try {
                return $dbEntity;
            } catch (\Exception $e) {
                //throw LOG.entityCacheLookupException(type, id, dbEntity.getClass(), e);
                throw new \Exception("entityCacheLookupException");
            }
        } else {
            return null;
        }
    }

    public function getEntitiesByType(string $type): array
    {
        $cacheKey = $cacheKeyMapping->getEntityCacheKey($type);
        if (array_key_exists($cacheKey, $this->cachedEntites)) {
            $entities = $this->cachedEntites[$cacheKey];
        } else {
            $entities = null;
        }
        $result = [];
        if ($entities === null) {
            return [];
        } else {
            foreach (array_values($entities) as $cachedEntity) {
                if ($type != $cacheKey) {
                    // if the cacheKey of this type differs from the actual type,
                    // not all cached entities with the key should be returned.
                    // Then we only add those entities whose type matches the argument type.
                    if (is_a($cachedEntity, $type)) {
                        $result[] = $cachedEntity->getEntity();
                    }
                } else {
                    $result[] = $cachedEntity->getEntity();
                }
            }
            return $result;
        }
    }

    /**
     * Looks up an entity in the cache.
     *
     * @param type the type of the object
     * @param id the id of the CachedEntity to lookup
     * @return CachedDbEntity the cached entity or null if the entity does not exist.
     */
    public function getCachedEntity($typeOrEntity, string $id = null): ?CachedDbEntity
    {
        if (is_string($typeOrEntity)) {
            $cacheKey = $cacheKeyMapping->getEntityCacheKey($typeOrEntity);
            if (array_key_exists($cacheKey, $this->cachedEntites)) {
                $entitiesByType = $this->cachedEntites[$cacheKey];
                return $entitiesByType[$id];
            } else {
                return null;
            }
        } elseif ($typeOrEntity instanceof DbEntityInterface) {
            return $this->getCachedEntity(get_class($dbEntity), $dbEntity->getId());
        }
    }

    /**
     * Put a new, {@link DbEntityState#TRANSIENT} object into the cache.
     *
     * @param e the object to put into the cache
     */
    public function putTransient(DbEntityInterface $e): void
    {
        $cachedDbEntity = new CachedDbEntity();
        $cachedDbEntity->setEntity($e);
        $cachedDbEntity->setEntityState(DbEntityState::TRANSIENT);
        $this->putInternal($cachedDbEntity);
    }

    /**
     * Put a {@link DbEntityState#PERSISTENT} object into the cache.
     *
     * @param e the object to put into the cache
     */
    public function putPersistent(DbEntityInterface $e): void
    {
        $cachedDbEntity = new CachedDbEntity();
        $cachedDbEntity->setEntity($e);
        $cachedDbEntity->setEntityState(DbEntityState::PERSISTENT);
        $cachedDbEntity->determineEntityReferences();
        $cachedDbEntity->makeCopy();
        $this->putInternal($cachedDbEntity);
    }

    /**
     * Put a {@link DbEntityState#MERGED} object into the cache.
     *
     * @param e the object to put into the cache
     */
    public function putMerged(DbEntityInterface $e): void
    {
        $cachedDbEntity = new CachedDbEntity();
        $cachedDbEntity->setEntity($e);
        $cachedDbEntity->setEntityState(DbEntityState::MERGED);
        $cachedDbEntity->determineEntityReferences();
        // no copy required
        $this->putInternal($cachedDbEntity);
    }

    protected function putInternal(CachedDbEntity $entityToAdd): void
    {
        $type = get_class($entityToAdd->getEntity());
        $cacheKey = $this->cacheKeyMapping->getEntityCacheKey($type);

        if (array_key_exists($cacheKey, $this->cachedEntites)) {
            $map = $this->cachedEntites[$cacheKey];
        } else {
            $map = [];
            $this->cachedEntites[$cacheKey] = $map;
        }

        // check whether this object is already present in the cache
        $id = $entityToAdd->getEntity()->getId();
        if (!array_key_exists($id, $map)) {
            // no such entity exists -> put it into the cache
            $this->cachedEntites[$cacheKey][$id] = $entityToAdd;
        } else {
            $existingCachedEntity = $map[$id];
            // the same entity is already cached
            switch ($entityToAdd->getEntityState()) {
                case DbEntityState::TRANSIENT:
                    // cannot put TRANSIENT entity if entity with same id already exists in cache.
                    if ($existingCachedEntity->getEntityState() == DbEntityState::TRANSIENT) {
                        //throw LOG.entityCacheDuplicateEntryException("TRANSIENT", entityToAdd.getEntity().getId(),
                        //entityToAdd.getEntity().getClass(), existingCachedEntity.getEntityState());
                        throw new \Exception("entityCacheDuplicateEntryException");
                    } else {
                        //throw LOG.alreadyMarkedEntityInEntityCacheException(entityToAdd.getEntity().getId(),
                        //entityToAdd.getEntity().getClass(), existingCachedEntity.getEntityState());
                        throw new \Exception("alreadyMarkedEntityInEntityCacheException");
                    }
                case DbEntityState::PERSISTENT:
                    if ($existingCachedEntity->getEntityState() == DbEntityState::PERSISTENT) {
                        // use new entity state, replacing the existing one.
                        $this->cachedEntites[$cacheKey][$id] = $entityToAdd;
                        break;
                    }
                    if (
                        $existingCachedEntity->getEntityState() == DbEntityState::DELETED_PERSISTENT ||
                        $existingCachedEntity->getEntityState() == DbEntityState::DELETED_MERGED
                    ) {
                        // ignore put -> this is already marked to be deleted
                        break;
                    }
                    // otherwise fail:
                    //throw LOG.entityCacheDuplicateEntryException("PERSISTENT", entityToAdd.getEntity().getId(),
                    //    entityToAdd.getEntity().getClass(), existingCachedEntity.getEntityState());
                    throw new \Exception("entityCacheDuplicateEntryException");
                case DbEntityState::MERGED:
                    if (
                        $existingCachedEntity->getEntityState() == DbEntityState::PERSISTENT ||
                        $existingCachedEntity->getEntityState() == DbEntityState::MERGED
                    ) {
                        // use new entity state, replacing the existing one.
                        $this->cachedEntites[$cacheKey][$id] = $entityToAdd;
                        break;
                    }
                    if (
                        $existingCachedEntity->getEntityState() == DbEntityState::DELETED_PERSISTENT ||
                        $existingCachedEntity->getEntityState() == DbEntityState::DELETED_MERGED
                    ) {
                        // ignore put -> this is already marked to be deleted
                        break;
                    }

                    // otherwise fail:
                    //throw LOG.entityCacheDuplicateEntryException("MERGED", entityToAdd.getEntity().getId(),
                    //    entityToAdd.getEntity().getClass(), existingCachedEntity.getEntityState());
                    throw new \Exception("entityCacheDuplicateEntryException");
                default:
                    // deletes are always added
                    $this->cachedEntites[$cacheKey][$id] = $entityToAdd;
                    break;
            }
        }
    }

    /**
     * Remove an entity from the cache
     * @param e the entity to remove
     * @return
     */
    public function remove(DbEntityInterface $e)
    {
        if ($e instanceof DbEntityInterface) {
            $cacheKey = $cacheKeyMapping->getEntityCacheKey(get_class($e));
            if (array_key_exists($cacheKey, $this->cachedEntites)) {
                $typeMap = $this->cachedEntites[$cacheKey];
                if (array_key_exists($e->getId(), $typeMap)) {
                    unset($this->cachedEntites[$cacheKey][$e->getId()]);
                    return true;
                }
                return false;
            } else {
                return false;
            }
        } elseif ($e instanceof CachedDbEntity) {
            return $this->remove($e->getEntity());
        }
        return false;
    }

    /**
     * Allows checking whether the provided entity is present in the cache
     *
     * @param dbEntity the entity to check
     * @return bool true if the the provided entity is present in the cache
     */
    public function contains(DbEntityInterface $dbEntity): bool
    {
        return $this->getCachedEntity($dbEntity) !== null;
    }

    /**
     * Allows checking whether the provided entity is present in the cache
     * and is {@link DbEntityState#PERSISTENT}.
     *
     * @param dbEntity the entity to check
     * @return bool true if the provided entity is present in the cache and is
     * {@link DbEntityState#PERSISTENT}.
     */
    public function isPersistent(DbEntityInterface $dbEntity): bool
    {
        $cachedDbEntity = $this->getCachedEntity($dbEntity);
        if ($cachedDbEntity === null) {
            return false;
        } else {
            return $cachedDbEntity->getEntityState() == DbEntityState::PERSISTENT;
        }
    }

    /**
     * Allows checking whether the provided entity is present in the cache
     * and is marked to be deleted.
     *
     * @param dbEntity the entity to check
     * @return bool true if the provided entity is present in the cache and is
     * marked to be deleted
     */
    public function isDeleted(DbEntityInterface $dbEntity): bool
    {
        $cachedDbEntity = $this->getCachedEntity($dbEntity);
        if ($cachedDbEntity === null) {
            return false;
        } else {
            return $cachedDbEntity->getEntityState() == DbEntityState::DELETED_MERGED
                || $cachedDbEntity->getEntityState() == DbEntityState::DELETED_PERSISTENT
                || $cachedDbEntity->getEntityState() == DbEntityState::DELETED_TRANSIENT;
        }
    }

    /**
     * Allows checking whether the provided entity is present in the cache
     * and is {@link DbEntityState#TRANSIENT}.
     *
     * @param dbEntity the entity to check
     * @return bool true if the provided entity is present in the cache and is
     * {@link DbEntityState#TRANSIENT}.
     */
    public function isTransient(DbEntityInterface $dbEntity): bool
    {
        $cachedDbEntity = $this->getCachedEntity($dbEntity);
        if ($cachedDbEntity === null) {
            return false;
        } else {
            return $cachedDbEntity->getEntityState() == DbEntityState::TRANSIENT;
        }
    }

    public function getCachedEntities(): array
    {
        $result = [];
        foreach (array_values($this->cachedEntites) as $typeCache) {
            $result = array_merge($result, array_values($typeCache));
        }
        return $result;
    }

    /**
     * Sets an object to a deleted state. It will not be removed from the cache but
     * transition to one of the DELETED states, depending on it's current state.
     *
     * @param dbEntity the object to mark deleted.
     */
    public function setDeleted(DbEntityInterface $dbEntity): void
    {
        $cachedEntity = $this->getCachedEntity($dbEntity);
        if ($cachedEntity !== null) {
            if ($cachedEntity->getEntityState() == DbEntityState::TRANSIENT) {
                $cachedEntity->setEntityState(DbEntityState::DELETED_TRANSIENT);
            } elseif ($cachedEntity->getEntityState() == DbEntityState::PERSISTENT) {
                $cachedEntity->setEntityState(DbEntityState::DELETED_PERSISTENT);
            } elseif ($cachedEntity->getEntityState() == DbEntityState::MERGED) {
                $cachedEntity->setEntityState(DbEntityState::DELETED_MERGED);
            }
        } else {
            // put a deleted merged into the cache
            $cachedDbEntity = new CachedDbEntity();
            $cachedDbEntity->setEntity($dbEntity);
            $cachedDbEntity->setEntityState(DbEntityState::DELETED_MERGED);
            $this->putInternal($cachedDbEntity);
        }
    }

    public function undoDelete(DbEntityInterface $dbEntity): void
    {
        $cachedEntity = $this->getCachedEntity($dbEntity);
        if ($cachedEntity->getEntityState() == DbEntityState::DELETED_TRANSIENT) {
            $cachedEntity->setEntityState(DbEntityState::TRANSIENT);
        } else {
            $cachedEntity->setEntityState(DbEntityState::MERGED);
        }
    }
}
