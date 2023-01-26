<?php

namespace Jabe\Impl\Db\EntityManager;

use Jabe\{
    OptimisticLockingException,
    ProcessEngineException
};
use Jabe\Impl\{
    DeploymentQueryImpl,
    ExecutionQueryImpl,
    GroupQueryImpl,
    HistoricActivityInstanceQueryImpl,
    HistoricDetailQueryImpl,
    HistoricJobLogQueryImpl,
    HistoricProcessInstanceQueryImpl,
    HistoricTaskInstanceQueryImpl,
    HistoricVariableInstanceQueryImpl,
    JobQueryImpl,
    Page,
    ProcessDefinitionQueryImpl,
    ProcessEngineLogger,
    ProcessInstanceQueryImpl,
    TaskQueryImpl,
    UserQueryImpl
};
use Jabe\Impl\Cfg\{
    IdGeneratorInterface,
    ProcessEngineConfigurationImpl
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    EntityLoadListenerInterface,
    FlushResult,
    HistoricEntityInterface,
    ListQueryParameterObject,
    PersistenceSessionInterface
};
use Jabe\Impl\Db\EntityManager\Cache\{
    CachedDbEntity,
    DbEntityCache,
    DbEntityState
};
use Jabe\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbEntityOperation,
    DbOperation,
    DbOperationState,
    DbOperationManager,
    DbOperationType
};
use Jabe\Impl\Identity\Db\{
    DbGroupQueryImpl,
    DbUserQueryImpl
};
use Jabe\Impl\Interceptor\SessionInterface;
use Jabe\Impl\Persistence\Entity\ByteArrayEntity;
use Jabe\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};
use Jabe\Repository\ResourceTypes;

class DbEntityManager implements SessionInterface, EntityLoadListenerInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    public const TOGGLE_FOREIGN_KEY_STMT = "toggleForeignKey";
    public const BATCH_SIZE = 50;

    protected $optimisticLockingListeners = [];

    protected $idGenerator;

    protected $dbEntityCache;

    protected $dbOperationManager;

    protected $persistenceSession;
    protected bool $isIgnoreForeignKeysForNextFlush = false;

    public function __construct(IdGeneratorInterface $idGenerator, PersistenceSessionInterface $persistenceSession = null)
    {
        $this->idGenerator = $idGenerator;
        $this->persistenceSession = $persistenceSession;
        if ($this->persistenceSession !== null) {
            $this->persistenceSession->addEntityLoadListener($this);
        }
        $this->initializeEntityCache();
        $this->initializeOperationManager();
    }

    protected function initializeOperationManager(): void
    {
        $this->dbOperationManager = new DbOperationManager();
    }

    protected function initializeEntityCache(): void
    {
        $jobExecutorContext = Context::getJobExecutorContext();
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        if (
            $processEngineConfiguration !== null
            && $processEngineConfiguration->isDbEntityCacheReuseEnabled()
            && $jobExecutorContext !== null
        ) {
            $this->dbEntityCache = $jobExecutorContext->getEntityCache();
            if ($this->dbEntityCache === null) {
                $this->dbEntityCache = new DbEntityCache($processEngineConfiguration->getDbEntityCacheKeyMapping());
                $jobExecutorContext->setEntityCache($this->dbEntityCache);
            }
        } else {
            if ($processEngineConfiguration !== null) {
                $this->dbEntityCache = new DbEntityCache($processEngineConfiguration->getDbEntityCacheKeyMapping());
            } else {
                $this->dbEntityCache = new DbEntityCache();
            }
        }
    }

    // selects /////////////////////////////////////////////////

    public function selectList(?string $statement, $parameter = null, $pageOrFirstResult = null, $maxResults = null): array
    {
        if ($parameter === null) {
            return $this->selectList($statement, null, 0, PHP_INT_MAX);
        } elseif ($parameter instanceof ListQueryParameterObject && $pageOrFirstResult === null) {
            return $this->selectListWithRawParameter($statement, $parameter, $parameter->getFirstResult(), $parameter->getMaxResults());
        } elseif ($pageOrFirstResult === null) {
            return $this->selectList($statement, $parameter, 0, PHP_INT_MAX);
        } elseif ($pageOrFirstResult instanceof Page) {
            if ($parameter instanceof ListQueryParameterObject) {
                return $this->selectList($statement, $parameter);
            }
            return $this->selectList($statement, $parameter, $pageOrFirstResult->getFirstResult(), $pageOrFirstResult->getMaxResults());
        } elseif (is_int($pageOrFirstResult) && is_int($maxResults)) {
            return $this->selectList($statement, new ListQueryParameterObject($parameter, $pageOrFirstResult, $maxResults));
        }
    }

    public function selectListWithRawParameter(?string $statement, $parameter, ?int $firstResult, ?int $maxResults): array
    {
        if ($firstResult == -1 ||  $maxResults == -1) {
            return [];
        }
        $loadedObjects = $this->persistenceSession->selectList($statement, $parameter);
        return $this->filterLoadedObjects($loadedObjects);
    }

    public function selectOne(?string $statement, $parameter)
    {
        $result = $this->persistenceSession->selectOne($statement, $parameter);
        if ($result instanceof DbEntityInterface) {
            $loadedObject = $result;
            $result = $this->cacheFilter($loadedObject);
        }
        return $result;
    }

    public function selectBoolean(?string $statement, $parameter): bool
    {
        $result = $this->persistenceSession->selectList($statement, $parameter);
        if (!empty($result)) {
            return in_array(1, $result);
        }
        return false;
    }

    public function selectById(?string $entityClass, ?string $id): ?DbEntityInterface
    {
        $persistentObject = $this->dbEntityCache->get($entityClass, $id);
        if (!empty($persistentObject)) {
            return $persistentObject;
        }

        $persistentObject = $this->persistenceSession->selectById($entityClass, $id);

        if (empty($persistentObject)) {
            return null;
        }
        // don't have to put object into the cache now. See onEntityLoaded() callback
        return $persistentObject;
    }

    public function getCachedEntity(?string $type, ?string $id): ?DbEntityInterface
    {
        return $this->dbEntityCache->get($type, $id);
    }

    public function getCachedEntitiesByType(?string $type): array
    {
        return $this->dbEntityCache->getEntitiesByType($type);
    }

    protected function filterLoadedObjects(array $loadedObjects): array
    {
        if (empty($loadedObjects) || $loadedObjects[0] === null) {
            return $loadedObjects;
        }
        if (!(is_a($loadedObjects[0], DbEntityInterface::class))) {
            return $loadedObjects;
        }
        $filteredObjects = [];
        foreach ($loadedObjects as $loadedObject) {
            $cachedPersistentObject = $this->cacheFilter($loadedObject);
            $filteredObjects[] = $cachedPersistentObject;
        }
        return $filteredObjects;
    }

    /** returns the object in the cache.  if this object was loaded before,
     * then the original object is returned. */
    protected function cacheFilter(DbEntityInterface $persistentObject): DbEntityInterface
    {
        $cachedPersistentObject = $this->dbEntityCache->get(get_class($persistentObject), $persistentObject->getId());
        if ($cachedPersistentObject !== null) {
            return $cachedPersistentObject;
        } else {
            return $persistentObject;
        }
    }

    public function onEntityLoaded(DbEntityInterface $entity): void
    {
        // we get a callback when the persistence session loads an object from the database
        $cachedPersistentObject = $this->dbEntityCache->get(get_class($entity), $entity->getId());
        if (empty($cachedPersistentObject)) {
            // only put into the cache if not already present
            $this->dbEntityCache->putPersistent($entity);

            // invoke postLoad() lifecycle method
            if ($entity instanceof DbEntityLifecycleAwareInterface) {
                $lifecycleAware = $entity;
                $lifecycleAware->postLoad();
            }
        }
    }

    public function lock(?string $statement, $parameter = null): void
    {
        $this->persistenceSession->lock($statement, $parameter);
    }

    public function isDirty(DbEntityInterface $dbEntity): bool
    {
        $cachedEntity = $this->dbEntityCache->getCachedEntity($dbEntity);
        if ($cachedEntity === null) {
            return false;
        } else {
            return $cachedEntity->isDirty() || $cachedEntity->getEntityState() == DbEntityState::MERGED;
        }
    }

    public function flush(): void
    {
        // flush the entity cache which inserts operations to the db operation manager
        $this->flushEntityCache();

        // flush the db operation manager
        $this->flushDbOperationManager();
    }

    public function setIgnoreForeignKeysForNextFlush(bool $ignoreForeignKeysForNextFlush): void
    {
        $this->isIgnoreForeignKeysForNextFlush = $ignoreForeignKeysForNextFlush;
    }

    protected function flushDbOperationManager(): void
    {
        // obtain totally ordered operation list from operation manager
        $operationsToFlush = $this->dbOperationManager->calculateFlush();
        if (empty($operationsToFlush)) {
            return;
        }

        //LOG.databaseFlushSummary(operationsToFlush);

        // If we want to delete all table data as bulk operation, on tables which have self references,
        // We need to turn the foreign key check off on MySQL and MariaDB.
        // On other databases we have to do nothing, the mapped statement will be empty.
        if ($this->isIgnoreForeignKeysForNextFlush) {
            $this->persistenceSession->executeNonEmptyUpdateStmt(self::TOGGLE_FOREIGN_KEY_STMT, false);
            $this->persistenceSession->flushOperations();
        }

        try {
            $batches = CollectionUtil::partition($operationsToFlush, self::BATCH_SIZE);
            foreach ($batches as $key => $batch) {
                $this->flushDbOperations($batch, $operationsToFlush);
            }
        } finally {
            if ($this->isIgnoreForeignKeysForNextFlush) {
                $this->persistenceSession->executeNonEmptyUpdateStmt(self::TOGGLE_FOREIGN_KEY_STMT, true);
                $this->persistenceSession->flushOperations();
                $this->isIgnoreForeignKeysForNextFlush = false;
            }
        }
    }

    protected function flushDbOperations(
        array &$operationsToFlush,
        array $allOperations
    ): void {
        // execute the flush
        while (!empty($operationsToFlush)) {
            $flushResult = null;
            try {
                $flushResult = $this->persistenceSession->executeDbOperations($operationsToFlush);
            } catch (\Exception $e) {
                // Top level persistence exception
                //throw LOG.flushDbOperationUnexpectedException(allOperations, e);
                throw new \Exception("flushDbOperationUnexpectedException");
            }

            $failedOperations = $flushResult->getFailedOperations();

            foreach ($failedOperations as $failedOperation) {
                $failureState = $failedOperation->getState();

                if ($failureState == DbOperationState::FAILED_CONCURRENT_MODIFICATION) {
                    // this method throws an exception in case the flush cannot be continued;
                    // accordingly, this method will be left as well in this case
                    $this->handleConcurrentModification($failedOperation);
                } elseif ($failureState == DbOperationState::FAILED_ERROR) {
                    // Top level persistence exception
                    $failure = $failedOperation->getFailure();
                    //throw LOG.flushDbOperationException(allOperations, failedOperation, failure);
                    throw new \Exception("flushDbOperationException");
                } else {
                    // This branch should never be reached and the exception thus indicates a bug
                    throw new ProcessEngineException(
                        "Entity session returned a failed operation not " .
                        "in an error state. This indicates a bug"
                    );
                }
                /*elseif ($failureState == DbOperationState::FAILED_CONCURRENT_MODIFICATION_CRDB) {
                    $this->handleConcurrentModificationCrdb($failedOperation);
                }*/
            }

            $remainingOperations = $flushResult->getRemainingOperations();

            // avoid infinite loops
            EnsureUtil::ensureLessThan(
                "Database flush did not process any operations. This indicates a bug.",
                "remainingOperations",
                count($remainingOperations),
                count($operationsToFlush)
            );

            $operationsToFlush = $remainingOperations;
        }
    }

    public function flushEntity(DbEntityInterface $entity): void
    {
        $cachedEntity = $this->dbEntityCache->getCachedEntity($entity);
        if ($cachedEntity !== null) {
            $this->flushCachedEntity($cachedEntity);
        }

        $this->flushDbOperationManager();
    }

    /**
     * Decides if an operation that failed for concurrent modifications can be tolerated,
     * or if OptimisticLockingException should be raised
     *
     * @param dbOperation
     * @throws OptimisticLockingException if there is no handler for the failure
     */
    protected function handleConcurrentModification(DbOperation $dbOperation): void
    {
        $handlingResult = $this->invokeOptimisticLockingListeners($dbOperation);

        if (
            OptimisticLockingResult::THROW == $handlingResult
            && canIgnoreHistoryModificationFailure($dbOperation)
        ) {
            $handlingResult = OptimisticLockingResult::IGNORE;
        }

        switch ($handlingResult) {
            case OptimisticLockingResult::IGNORE:
                break;
            case OptimisticLockingResult::THROW:
            default:
                //throw LOG.concurrentUpdateDbEntityException(dbOperation);
                throw new \Exception("concurrentUpdateDbEntityException");
        }
    }

    /*protected function handleConcurrentModificationCrdb(DbOperation $dbOperation): void
    {
        $handlingResult = $this->invokeOptimisticLockingListeners($dbOperation);

        if (OptimisticLockingResult::IGNORE == $handlingResult) {
            //LOG.crdbFailureIgnored(dbOperation);
        }

        // CRDB concurrent modification exceptions always lead to the transaction
        // being aborted, so we must always throw an exception.
        //throw LOG.crdbTransactionRetryException(dbOperation);
        //throw new \Exception("crdbTransactionRetryException");
    }*/

    private function invokeOptimisticLockingListeners(DbOperation $dbOperation): OptimisticLockingResult
    {
        $handlingResult = OptimisticLockingResult::THROW;

        if (!empty($this->optimisticLockingListeners)) {
            foreach ($this->optimisticLockingListeners as $optimisticLockingListener) {
                if (
                    $optimisticLockingListener->getEntityType() === null
                    || is_a($optimisticLockingListener->getEntityType(), $dbOperation->getEntityType(), true)
                ) {
                    $handlingResult = $optimisticLockingListener->failedOperation($dbOperation);
                }
            }
        }
        return $handlingResult;
    }

    /**
     * Determines if a failed database operation (OptimisticLockingException)
     * on a Historic entity can be ignored.
     *
     * @param dbOperation that failed
     * @return bool true if the failure can be ignored
     */
    protected function canIgnoreHistoryModificationFailure(DbOperation $dbOperation): bool
    {
        $dbEntity = $dbOperation->getEntity();
        return Context::getProcessEngineConfiguration()->isSkipHistoryOptimisticLockingExceptions()
                && ($dbEntity instanceof HistoricEntityInterface || $this->isHistoricByteArray($dbEntity));
    }

    protected function isHistoricByteArray(DbEntityInterface $dbEntity): bool
    {
        if ($dbEntity instanceof ByteArrayEntity) {
            $byteArrayEntity = $dbEntity;
            return $byteArrayEntity->getType() == ResourceTypes::history()->getValue();
        } else {
            return false;
        }
    }

    /**
     * Flushes the entity cache:
     * Depending on the entity state, the required DbOperation is performed and the cache is updated.
     */
    protected function flushEntityCache(): void
    {
        $cachedEntities = $this->dbEntityCache->getCachedEntities();
        foreach ($cachedEntities as $cachedDbEntity) {
            $this->flushCachedEntity($cachedDbEntity);
        }

        // log cache state after flush
        //LOG.flushedCacheState(dbEntityCache->getCachedEntities());
    }

    protected function flushCachedEntity(CachedDbEntity $cachedDbEntity): void
    {
        if ($cachedDbEntity->getEntityState() == DbEntityState::TRANSIENT) {
            // latest state of references in cache is relevant when determining insertion order
            $cachedDbEntity->determineEntityReferences();
            // perform DbOperationType::INSERT
            $this->performEntityOperation($cachedDbEntity, DbOperationType::INSERT);
            // mark DbEntityState::PERSISTENT
            $cachedDbEntity->setEntityState(DbEntityState::PERSISTENT);
        } elseif ($cachedDbEntity->getEntityState() == DbEntityState::PERSISTENT && $cachedDbEntity->isDirty()) {
            // object is dirty -> perform UPDATE
            $this->performEntityOperation($cachedDbEntity, DbOperationType::UPDATE);
        } elseif ($cachedDbEntity->getEntityState() == DbEntityState::MERGED) {
            // perform UPDATE
            $this->performEntityOperation($cachedDbEntity, DbOperationType::UPDATE);
            // mark DbEntityState::PERSISTENT
            $cachedDbEntity->setEntityState(DbEntityState::PERSISTENT);
        } elseif ($cachedDbEntity->getEntityState() == DbEntityState::DELETED_TRANSIENT) {
            // remove from cache
            $this->dbEntityCache->remove($cachedDbEntity);
        } elseif (
            $cachedDbEntity->getEntityState() == DbEntityState::DELETED_PERSISTENT
            || $cachedDbEntity->getEntityState() == DbEntityState::DELETED_MERGED
        ) {
            // perform DbOperationType::DELETE
            $this->performEntityOperation($cachedDbEntity, DbOperationType::DELETE);
            // remove from cache
            $this->dbEntityCache->remove($cachedDbEntity);
        }

        // if object is DbEntityState::PERSISTENT after flush
        if ($cachedDbEntity->getEntityState() == DbEntityState::PERSISTENT) {
            // make a new copy
            $cachedDbEntity->makeCopy();
            // update cached references
            $cachedDbEntity->determineEntityReferences();
        }
    }

    public function insert(DbEntityInterface $dbEntity): void
    {
        // generate Id if not present
        $this->ensureHasId($dbEntity);

        $this->validateId($dbEntity);

        // put into cache
        $this->dbEntityCache->putTransient($dbEntity);
    }

    public function merge(DbEntityInterface $dbEntity): void
    {

        if ($dbEntity->getId() === null) {
            //throw LOG.mergeDbEntityException(dbEntity);
            throw new \Exception("mergeDbEntityException");
        }

        // NOTE: a proper implementation of merge() would fetch the entity from the database
        // and merge the state changes. For now, we simply always perform an update.
        // Supposedly, the "proper" implementation would reduce the number of situations where
        // optimistic locking results in a conflict.

        $this->dbEntityCache->putMerged($dbEntity);
    }

    public function forceUpdate(DbEntityInterface $entity): void
    {
        $cachedEntity = $this->dbEntityCache->getCachedEntity($entity);
        if ($cachedEntity !== null && $cachedEntity->getEntityState() == DbEntityState::PERSISTENT) {
            $cachedEntity->forceSetDirty();
        }
    }

    public function undoDelete(DbEntityInterface $entity): void
    {
        $this->dbEntityCache->undoDelete($entity);
    }

    public function update(?string $entityType, ?string $statement, $parameter): void
    {
        $this->performBulkOperation($entityType, $statement, $parameter, DbOperationType::UPDATE_BULK);
    }

    /**
     * Several update operations added by this method will be executed preserving the order of method calls, no matter what entity type they refer to.
     * They will though be executed after all "not-bulk" operations (e.g. DbEntityManager#insert(DbEntity) or DbEntityManager#merge(DbEntity))
     * and after those updates added by {@link DbEntityManager#update(Class, String, Object)}.
     * @param entityType
     * @param statement
     * @param parameter
     */
    public function updatePreserveOrder(?string $entityType, ?string $statement, $parameter): void
    {
        $this->performBulkOperationPreserveOrder($entityType, $statement, $parameter, DbOperationType::UPDATE_BULK);
    }

    public function delete($entityTypeOrEntity, ?string $statement = null, $parameter = null): void
    {
        if (is_string($entityTypeOrEntity)) {
            $this->performBulkOperation($entityTypeOrEntity, $statement, $parameter, DbOperationType::DELETE_BULK);
        } elseif ($entityTypeOrEntity instanceof DbEntityInterface) {
            $this->dbEntityCache->setDeleted($entityTypeOrEntity);
        }
    }

    /**
     * Several delete operations added by this method will be executed preserving the order of method calls, no matter what entity type they refer to.
     * They will though be executed after all "not-bulk" operations (e.g. DbEntityManager#insert(DbEntity) or DbEntityManager#merge(DbEntity))
     * and after those deletes added by {@link DbEntityManager#delete(Class, String, Object)}.
     * @param entityType
     * @param statement
     * @param parameter
     * @return delete operation
     */
    public function deletePreserveOrder(?string $entityType, ?string $statement, $parameter): DbBulkOperation
    {
        return $this->performBulkOperationPreserveOrder($entityType, $statement, $parameter, DbOperationType::DELETE_BULK);
    }

    protected function performBulkOperation(?string $entityType, ?string $statement, $parameter, ?string $operationType): DbBulkOperation
    {
        // create operation
        $bulkOperation = $this->createDbBulkOperation($entityType, $statement, $parameter, $operationType);

        // schedule operation
        $this->dbOperationManager->addOperation($bulkOperation);
        return $bulkOperation;
    }

    protected function performBulkOperationPreserveOrder(?string $entityType, ?string $statement, $parameter, ?string $operationType): DbBulkOperation
    {
        $bulkOperation = $this->createDbBulkOperation($entityType, $statement, $parameter, $operationType);

        // schedule operation
        $this->dbOperationManager->addOperationPreserveOrder($bulkOperation);
        return $bulkOperation;
    }

    private function createDbBulkOperation(?string $entityType, ?string $statement, $parameter, ?string $operationType): DbBulkOperation
    {
        // create operation
        $bulkOperation = new DbBulkOperation();
        // configure operation
        $bulkOperation->setOperationType($operationType);
        $bulkOperation->setEntityType($entityType);
        $bulkOperation->setStatement($statement);
        $bulkOperation->setParameter($parameter);
        return $bulkOperation;
    }

    protected function performEntityOperation(CachedDbEntity $cachedDbEntity, ?string $type): void
    {
        $dbOperation = new DbEntityOperation();
        $dbOperation->setEntity($cachedDbEntity->getEntity());
        $dbOperation->setFlushRelevantEntityReferences($cachedDbEntity->getFlushRelevantEntityReferences());
        $dbOperation->setOperationType($type);
        $this->dbOperationManager->addOperation($dbOperation);
    }

    public function close(): void
    {
    }

    public function isDeleted(DbEntityInterface $object): bool
    {
        return $this->dbEntityCache->isDeleted($object);
    }

    protected function ensureHasId(DbEntityInterface $dbEntity): void
    {
        if ($dbEntity->getId() === null) {
            $nextId = $this->idGenerator->getNextId();
            $dbEntity->setId($nextId);
        }
    }

    protected function validateId(DbEntityInterface $dbEntity): void
    {
        EnsureUtil::ensureValidIndividualResourceId("Entity has an invalid id", $dbEntity->getId());
    }

    public function pruneDeletedEntities(array $listToPrune): array
    {
        $prunedList = [];
        foreach ($listToPrune as $potentiallyDeleted) {
            if (!$this->isDeleted($potentiallyDeleted)) {
                $prunedList[] = $potentiallyDeleted;
            }
        }
        return $prunedList;
    }

    public function contains(DbEntityInterface $dbEntity): bool
    {
        return $this->dbEntityCache->contains($dbEntity);
    }

    // getters / setters /////////////////////////////////

    public function getDbOperationManager(): DbOperationManager
    {
        return $this->dbOperationManager;
    }

    public function setDbOperationManager(DbOperationManager $operationManager): void
    {
        $this->dbOperationManager = $operationManager;
    }

    public function getDbEntityCache(): DbEntityCache
    {
        return $this->dbEntityCache;
    }

    public function setDbEntityCache(DbEntityCache $dbEntityCache): void
    {
        $this->dbEntityCache = $dbEntityCache;
    }

    // query factory methods ////////////////////////////////////////////////////

    public function createDeploymentQuery(): DeploymentQueryImpl
    {
        return new DeploymentQueryImpl();
    }

    public function createProcessDefinitionQuery(): ProcessDefinitionQueryImpl
    {
        return new ProcessDefinitionQueryImpl();
    }

    /*public CaseDefinitionQueryImpl createCaseDefinitionQuery() {
        return new CaseDefinitionQueryImpl();
    }*/

    public function createProcessInstanceQuery(): ProcessInstanceQueryImpl
    {
        return new ProcessInstanceQueryImpl();
    }

    public function createExecutionQuery(): ExecutionQueryImpl
    {
        return new ExecutionQueryImpl();
    }

    public function createTaskQuery(): TaskQueryImpl
    {
        return new TaskQueryImpl();
    }

    public function createJobQuery(): JobQueryImpl
    {
        return new JobQueryImpl();
    }

    public function createHistoricProcessInstanceQuery(): HistoricProcessInstanceQueryImpl
    {
        return new HistoricProcessInstanceQueryImpl();
    }

    public function createHistoricActivityInstanceQuery(): HistoricActivityInstanceQueryImpl
    {
        return new HistoricActivityInstanceQueryImpl();
    }

    public function createHistoricTaskInstanceQuery(): HistoricTaskInstanceQueryImpl
    {
        return new HistoricTaskInstanceQueryImpl();
    }

    public function createHistoricDetailQuery(): HistoricDetailQueryImpl
    {
        return new HistoricDetailQueryImpl();
    }

    public function createHistoricVariableInstanceQuery(): HistoricVariableInstanceQueryImpl
    {
        return new HistoricVariableInstanceQueryImpl();
    }

    public function createHistoricJobLogQuery(): HistoricJobLogQueryImpl
    {
        return new HistoricJobLogQueryImpl();
    }

    public function createUserQuery(): UserQueryImpl
    {
        return new DbUserQueryImpl();
    }

    public function createGroupQuery(): GroupQueryImpl
    {
        return new DbGroupQueryImpl();
    }

    public function registerOptimisticLockingListener(OptimisticLockingListenerInterface $optimisticLockingListener = null): void
    {
        $this->optimisticLockingListeners[] = $optimisticLockingListener;
    }

    public function getTableNamesPresentInDatabase(): array
    {
        return $this->persistenceSession->getTableNamesPresent();
    }
}
