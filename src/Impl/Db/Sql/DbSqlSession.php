<?php

namespace Jabe\Impl\Db\Sql;

use Doctrine\DBAL\Connection;
use Jabe\ProcessEngineInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    AbstractPersistenceSession,
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbEntityOperation,
    DbOperation,
    DbOperationState,
    DbOperationType
};
use Jabe\Impl\Interceptor\SessionFactoryInterface;
use Jabe\Impl\Util\{
    DatabaseUtil,
    EnsureUtil,
    ExceptionUtil,
    IoUtil,
    ReflectUtil
};
use MyBatis\Session\SqlSessionInterface;

abstract class DbSqlSession extends AbstractPersistenceSession
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    public const DBC_METADATA_TABLE_TYPES = [ "TABLE" ];
    public const PG_DBC_METADATA_TABLE_TYPES = [ "TABLE", "PARTITIONED TABLE" ];

    protected $sqlSession;
    protected $dbSqlSessionFactory;

    protected $connectionMetadataDefaultCatalog = null;
    protected $connectionMetadataDefaultSchema = null;

    public function __construct(DbSqlSessionFactory $dbSqlSessionFactory, Connection $connection = null, string $catalog = null, string $schema = null)
    {
        $this->dbSqlSessionFactory = $dbSqlSessionFactory;
        $sqlSessionFactory = $this->dbSqlSessionFactory->getSqlSessionFactory();
        if ($connection !== null) {
            $this->sqlSession = ExceptionUtil::doWithExceptionWrapper(function () use ($sqlSessionFactory, $connection) {
                return $sqlSessionFactory->openSession($connection);
            });
            $this->connectionMetadataDefaultCatalog = $catalog;
            $this->connectionMetadataDefaultSchema = $schema;
        } else {
            $this->sqlSession = ExceptionUtil::doWithExceptionWrapper(function () use ($sqlSessionFactory) {
                return $sqlSessionFactory->openSession();
            });
        }
    }

    // select ////////////////////////////////////////////

    public function selectList(string $statement, array $params = [], array $types = [])
    {
        $statement = $this->dbSqlSessionFactory->mapStatement($statement);
        $resultList = $this->executeSelectList($statement, $params, $types);
        foreach ($resultList as $object) {
            $this->fireEntityLoaded($object);
        }
        return $resultList;
    }

    public function executeSelectList(string $statement, array $params = [], array $types = []): array
    {
        $scope = $this;
        return ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $statement, $params, $types) {
            return $scope->sqlSession->selectList($statement, $params, $types);
        });
    }

    public function selectById(string $type, string $id)
    {
        $selectStatement = $this->dbSqlSessionFactory->getSelectStatement($type);
        $mappedSelectStatement = $this->dbSqlSessionFactory->mapStatement($selectStatement);
        EnsureUtil::ensureNotNull("no select statement for " . $type . " in the ibatis mapping files", "selectStatement", $selectStatement);

        $scope = $this;
        $result = ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $mappedSelectStatement, $id) {
            return $scope->sqlSession->selectOne($mappedSelectStatement, $id);
        });
        $this->fireEntityLoaded($result);
        return $result;
    }

    public function selectOne(string $statement, array $params = [], array $types = [])
    {
        $scope = $this;
        $mappedStatement = $this->dbSqlSessionFactory->mapStatement($statement);
        $result = ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $mappedStatement, $params, $types) {
            return $scope->sqlSession->selectOne($mappedStatement, $params, $types);
        });
        $this->fireEntityLoaded($result);
        return $result;
    }

    // lock ////////////////////////////////////////////

    public function lock(string $statement, array $params = [], array $types = []): void
    {
        // do not perform locking if H2 database is used. H2 uses table level locks
        // by default which may cause deadlocks if the deploy command needs to get a new
        // Id using the DbIdGenerator while performing a deployment.
        //
        // On CockroachDB, pessimistic locks are disabled since this database uses
        // a stricter, SERIALIZABLE transaction isolation which ensures a serialized
        // manner of transaction execution, making our use-case of pessimistic locks
        // redundant.
        /*if (!DatabaseUtil.checkDatabaseType(DbSqlSessionFactory.CRDB, DbSqlSessionFactory.H2)) {
            String mappedStatement = dbSqlSessionFactory.mapStatement(statement);
            executeSelectForUpdate(mappedStatement, parameter);
        } else {
            LOG.debugDisabledPessimisticLocks();
        }*/
        $mappedStatement = $this->dbSqlSessionFactory->mapStatement($statement);
        $this->executeSelectForUpdate($mappedStatement, $params, $types);
    }

    abstract protected function executeSelectForUpdate(string $statement, array $params = [], array $types = []): void;

    protected function entityUpdatePerformed(
        DbEntityOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        if ($failure !== null) {
            $this->configureFailedDbEntityOperation($operation, $failure);
        } else {
            $dbEntity = $operation->getEntity();

            if ($dbEntity instanceof HasDbRevisionInterface) {
                if ($rowsAffected != 1) {
                    // failed with optimistic locking
                    $operation->setState(DbOperationState::FAILED_CONCURRENT_MODIFICATION);
                } else {
                    // increment revision of our copy
                    $versionedObject = $dbEntity;
                    $versionedObject->setRevision($versionedObject->getRevisionNext());
                    $operation->setState(DbOperationState::APPLIED);
                }
            } else {
                $operation->setState(DbOperationState::APPLIED);
            }
        }
    }

    protected function bulkUpdatePerformed(
        DbBulkOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        $this->bulkOperationPerformed($operation, $rowsAffected, $failure);
    }

    protected function bulkDeletePerformed(
        DbBulkOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        bulkOperationPerformed($operation, $rowsAffected, $failure);
    }

    protected function bulkOperationPerformed(
        DbBulkOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        if ($failure !== null) {
            $operation->setFailure($failure);
            $failedState = DbOperationState::FAILED_ERROR;
            /*if (isCrdbConcurrencyConflict(failure)) {
                failedState = State.FAILED_CONCURRENT_MODIFICATION_CRDB;
            }*/
            $operation->setState($failedState);
        } else {
            $operation->setRowsAffected($rowsAffected);
            $operation->setState(DbOperationState::APPLIED);
        }
    }

    protected function entityDeletePerformed(
        DbEntityOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        if ($failure !== null) {
            $this->configureFailedDbEntityOperation($operation, $failure);
        } else {
            $operation->setRowsAffected($rowsAffected);

            $dbEntity = $operation->getEntity();

            // It only makes sense to check for optimistic locking exceptions for objects that actually have a revision
            if ($dbEntity instanceof HasDbRevisionInterface && $rowsAffected == 0) {
                $operation->setState(DbOperationState::FAILED_CONCURRENT_MODIFICATION);
            } else {
                $operation->setState(DbOperationState::APPLIED);
            }
        }
    }

    protected function configureFailedDbEntityOperation(DbEntityOperation $operation, \Exception $failure = null): void
    {
        $operation->setRowsAffected(0);
        $operation->setFailure($failure);

        $operationType = $operation->getOperationType();
        $dependencyOperation = $operation->getDependentOperation();

        $failedState = null;
        /*if (isCrdbConcurrencyConflict(failure)) {
            failedState = State.FAILED_CONCURRENT_MODIFICATION_CRDB;
        } else*/
        if ($this->isConcurrentModificationException($operation, $failure)) {
            $failedState = DbOperationState::FAILED_CONCURRENT_MODIFICATION;
        } elseif (
            DbOperationType::DELETE == $operationType
            && $dependencyOperation !== null
            && $dependencyOperation->getState() !== null
            && $dependencyOperation->getState() != DbOperationState::APPLIED
        ) {
            // the owning operation was not successful, so the prerequisite for this operation was not given
            //LOG.ignoreFailureDuePreconditionNotMet(operation, "Parent database operation failed", dependencyOperation);
            $failedState = DbOperationState::NOT_APPLIED;
        } else {
            $failedState = DbOperationState::FAILED_ERROR;
        }
        $operation->setState($failedState);
    }

    protected function isConcurrentModificationException(
        DbOperation $failedOperation,
        \Exception $cause = null
    ): bool {
        $isConstraintViolation = ExceptionUtil::checkForeignKeyConstraintViolation($cause, true);
        $isVariableIntegrityViolation = ExceptionUtil::checkVariableIntegrityViolation($cause);

        if ($isVariableIntegrityViolation) {
            return true;
        } elseif (
            $isConstraintViolation
            && $failedOperation instanceof DbEntityOperation
            && $failedOperation->getEntity() instanceof HasDbReferencesInterface
            && ($failedOperation->getOperationType() == DbOperationType::INSERT
            || $failedOperation->getOperationType() == DbOperationType::UPDATE)
        ) {
            $entity = $failedOperation->getEntity();
            foreach ($entity->getReferencedEntitiesIdAndClass() as $key => $value) {
                $referencedEntity = $this->selectById($value, $key);
                if ($referencedEntity === null) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * In cases where CockroachDB is used, and a failed operation is detected,
     * the method checks if the exception was caused by a CockroachDB
     * <code>TransactionRetryException</code>.
     *
     * @param cause for which an operation failed
     * @return bool true if the failure was due to a CRDB <code>TransactionRetryException</code>.
     *          Otherwise, it's false.
     */
    /*public static function isCrdbConcurrencyConflict(Throwable cause): bool
    {
        // only check when CRDB is used
        if (DatabaseUtil.checkDatabaseType(DbSqlSessionFactory.CRDB)) {
            boolean isCrdbTxRetryException = ExceptionUtil.checkCrdbTransactionRetryException(cause);
            if (isCrdbTxRetryException) {
                return true;
            }
        }

        return false;
    }*/

    /**
     * In cases where CockroachDB is used, and a failed operation is detected,
     * the method checks if the exception was caused by a CockroachDB
     * <code>TransactionRetryException</code>. This method may be used when a
     * CRDB Error occurs on commit, and a Command Context is not available, as
     * it has already been closed. This is the case with Spring/JTA transaction
     * interceptors.
     *
     * @param cause for which an operation failed
     * @param configuration of the Process Engine
     * @return bool true if the failure was due to a CRDB <code>TransactionRetryException</code>.
     *          Otherwise, it's false.
     */
    /*public static boolean isCrdbConcurrencyConflictOnCommit(Throwable cause, ProcessEngineConfigurationImpl configuration) {
      // only check when CRDB is used
      if (DatabaseUtil.checkDatabaseType(configuration, DbSqlSessionFactory.CRDB)) {
        // with Java EE (JTA) transactions, the real cause is suppressed,
        // and replaced with a RollbackException. We need to look into the
        // suppressed exceptions to find the CRDB TransactionRetryError.
        List<Throwable> causes = new ArrayList<>(Arrays.asList(cause.getSuppressed()));
        causes.add(cause);
        for (Throwable throwable : causes) {
          if (ExceptionUtil.checkCrdbTransactionRetryException(throwable)) {
            return true;
          }
        }
      }
      return false;
    }*/

    // insert //////////////////////////////////////////

    protected function insertEntity(DbEntityOperation $operation): void
    {
        $dbEntity = $operation->getEntity();

        // get statement
        $insertStatement = $this->dbSqlSessionFactory->getInsertStatement($dbEntity);
        $insertStatement = $this->dbSqlSessionFactory->mapStatement($insertStatement);
        EnsureUtil::ensureNotNull("no insert statement for " . get_class($dbEntity) . " in the mapping files", "insertStatement", $insertStatement);

        // execute the insert
        $this->executeInsertEntity($insertStatement, $dbEntity);
    }

    protected function executeInsertEntity(string $insertStatement, $parameter): void
    {
        //LOG.executeDatabaseOperation("INSERT", parameter);
        try {
            $this->sqlSession->insert($insertStatement, $parameter);
        } catch (\Exception $e) {
            // exception is wrapped later
            throw $e;
        }
    }

    protected function entityInsertPerformed(
        DbEntityOperation $operation,
        int $rowsAffected,
        \Exception $failure = null
    ): void {
        $entity = $operation->getEntity();

        if ($failure !== null) {
            $this->configureFailedDbEntityOperation($operation, $failure);
        } else {
            // set revision of our copy to 1
            if ($entity instanceof HasDbRevisionInterface) {
                $versionedObject = $entity;
                $versionedObject->setRevision(1);
            }

            $operation->setState(DbOperationState::APPLIED);
        }
    }

    // delete ///////////////////////////////////////////

    protected function executeDelete(string $deleteStatement, $parameter)
    {
        // map the statement
        $mappedDeleteStatement = $this->dbSqlSessionFactory->mapStatement($deleteStatement);
        try {
            return $this->sqlSession->delete($mappedDeleteStatement, $parameter);
        } catch (\Exception $e) {
            // Exception is wrapped later
            throw $e;
        }
    }

    // update ////////////////////////////////////////

    public function executeUpdate(string $updateStatement, $parameter)
    {
        $mappedUpdateStatement = $this->dbSqlSessionFactory->mapStatement($updateStatement);
        try {
            return $this->sqlSession->update($mappedUpdateStatement, $parameter);
        } catch (\Exception $e) {
            // Exception is wrapped later
            throw $e;
        }
    }

    public function update(string $updateStatement, $parameter)
    {
        $scope = $this;
        return ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $updateStatement, $parameter) {
            return $this->sqlSession->update($updateStatement, $parameter);
        });
    }

    public function executeNonEmptyUpdateStmt(string $updateStmt, $parameter): int
    {
        $mappedUpdateStmt = $this->dbSqlSessionFactory->mapStatement($updateStmt);

        //if mapped statement is empty, which can happens for some databases, we have no need to execute it
        /*$isMappedStmtEmpty = ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $mappedUpdateStmt, $parameter) {
            $configuration = $scope->sqlSession->getConfiguration();
            $mappedStatement = $configuration->getMappedStatement($mappedUpdateStmt);
            $boundSql = $mappedStatement->getBoundSql($parameter);
            $sql = $boundSql->getSql();
            return $sql->isEmpty();
        });

        if (isMappedStmtEmpty) {
            return 0;
        }*/

        return $this->update($mappedUpdateStmt, $parameter);
    }

    // flush ////////////////////////////////////////////////////////////////////

    public function flush(): void
    {
    }

    public function flushOperations(): void
    {
        $scope = $this;
        ExceptionUtil::doWithExceptionWrapper(function () use ($scope) {
            $scope->flushBatchOperations();
        });
    }

    public function flushBatchOperations(): array
    {
        try {
            return $this->sqlSession->flushStatements();
        } catch (\Exception $ex) {
            // exception is wrapped later
            throw $ex;
        }
    }

    public function close(): void
    {
        $scope = $this;
        ExceptionUtil::doWithExceptionWrapper(function () use ($scope) {
            $scope->sqlSession->close();
            return null;
        });
    }

    public function commit(): void
    {
        $scope = $this;
        ExceptionUtil::doWithExceptionWrapper(function () use ($scope) {
            $scope->sqlSession->commit();
            return null;
        });
    }

    public function rollback(): void
    {
        $scope = $this;
        ExceptionUtil::doWithExceptionWrapper(function () use ($scope) {
            $scope->sqlSession->rollback();
            return null;
        });
    }

    // schema operations ////////////////////////////////////////////////////////

    public function dbSchemaCheckVersion(): void
    {
        try {
            $dbVersion = $this->getDbVersion();
            if (!ProcessEngineInterface::VERSION == $dbVersion) {
                //throw LOG.wrongDbVersionException(ProcessEngine.VERSION, dbVersion);
                throw new \Exception("wrongDbVersionException");
            }

            /*$missingComponents = [];
            if (!$this->isEngineTablePresent()) {
                $missingComponents[] = "engine";
            }
            if ($this->dbSqlSessionFactory->isDbHistoryUsed() && !$this->isHistoryTablePresent()) {
                $missingComponents[] = "history";
            }
            if ($this->dbSqlSessionFactory->isDbIdentityUsed() && !$this->isIdentityTablePresent()) {
                $missingComponents[] = "identity";
            }
            if ($this->dbSqlSessionFactory.isCmmnEnabled() && !isCmmnTablePresent()) {
                missingComponents.add("case.engine");
            }
            if (dbSqlSessionFactory.isDmnEnabled() && !isDmnTablePresent()) {
                missingComponents.add("decision.engine");
            }

            if (!empty($missingComponents)) {
                //throw LOG.missingTableException(missingComponents);
                throw new \Exception("missingTableException");
            }*/
        } catch (\Exception $e) {
            throw $e;
            /*if ($this->isMissingTablesException($e)) {
                //throw LOG.missingActivitiTablesException();
                throw new \Exception("missingActivitiTablesException");
            } else {
                throw $e;
                if ($e instanceof RuntimeException) {
                    throw (RuntimeException) e;
                } else {
                    throw LOG.unableToFetchDbSchemaVersion(e);
                }
            }*/
        }
    }

    protected function getDbVersion(): string
    {
        $selectSchemaVersionStatement = $this->dbSqlSessionFactory->mapStatement("selectDbSchemaVersion");
        $scope = $this;
        return ExceptionUtil::doWithExceptionWrapper(function () use ($scope, $selectSchemaVersionStatement) {
            return $scope->sqlSession->selectOne($selectSchemaVersionStatement);
        });
    }

    /*protected function dbSchemaCreateIdentity(): void
    {
        $this->executeMandatorySchemaResource("create", "identity");
    }

    protected function dbSchemaCreateHistory(): void
    {
        $this->executeMandatorySchemaResource("create", "history");
    }

    protected function dbSchemaCreateEngine(): void
    {
        $this->executeMandatorySchemaResource("create", "engine");
    }

    protected function dbSchemaCreateCmmn(): void
    {
        $this->executeMandatorySchemaResource("create", "case.engine");
    }

    protected function dbSchemaCreateCmmnHistory(): void
    {
        $this->executeMandatorySchemaResource("create", "case.history");
    }

    protected function dbSchemaCreateDmn(): void
    {
        executeMandatorySchemaResource("create", "decision.engine");
    }

    protected void dbSchemaCreateDmnHistory() {
      executeMandatorySchemaResource("create", "decision.history");
    }

    protected function dbSchemaDropIdentity(): void
    {
        $this->executeMandatorySchemaResource("drop", "identity");
    }

    protected function dbSchemaDropHistory(): void
    {
        $this->executeMandatorySchemaResource("drop", "history");
    }

    protected function dbSchemaDropEngine(): void
    {
        $this->executeMandatorySchemaResource("drop", "engine");
    }

    protected function dbSchemaDropCmmn(): void
    {
        executeMandatorySchemaResource("drop", "case.engine");
    }

    protected void dbSchemaDropCmmnHistory() {
        executeMandatorySchemaResource("drop", "case.history");
    }

    protected function dbSchemaDropDmn(): void
    {
        executeMandatorySchemaResource("drop", "decision.engine");
    }

    protected void dbSchemaDropDmnHistory() {
      executeMandatorySchemaResource("drop", "decision.history");
    }

    public function executeMandatorySchemaResource(string $operation, string $component): void
    {
        $this->executeSchemaResource($operation, $component, $this->getResourceForDbOperation($operation, $operation, $component), false);
    }*/

    /*public function isEngineTablePresent(): bool
    {
        return $this->isTablePresent("ACT_RU_EXECUTION");
    }

    public function isHistoryTablePresent(): bool
    {
        return $this->isTablePresent("ACT_HI_PROCINST");
    }

    public function isIdentityTablePresent(): bool
    {
        return $this->isTablePresent("ACT_ID_USER");
    }

    public function isCmmnTablePresent(): bool
    {
        return isTablePresent("ACT_RE_CASE_DEF");
    }

    public boolean isCmmnHistoryTablePresent() {
      return isTablePresent("ACT_HI_CASEINST");
    }

    public boolean isDmnTablePresent() {
      return isTablePresent("ACT_RE_DECISION_DEF");
    }

    public boolean isDmnHistoryTablePresent() {
      return isTablePresent("ACT_HI_DECINST");
    }*/

    public function isTablePresent(string $tableName): bool
    {
        $connection = $this->sqlSession->getConnection();
        $schemaManager = $connection->getSchemaManager();
        return $schemaManager->tablesExist([$tableName]);
    }

    public function getTableNamesPresent(): array
    {
        $connection = $this->sqlSession->getConnection();
        $schemaManager = $connection->getSchemaManager();
        return $schemaManager->listTableNames();
    }

    /*public String getResourceForDbOperation(String directory, String operation, String component) {
        String databaseType = dbSqlSessionFactory.getDatabaseType();
        return "org/camunda/bpm/engine/db/" + directory + "/activiti." + databaseType + "." + operation + "."+component+".sql";
    }

    public void executeSchemaResource(String operation, String component, String resourceName, boolean isOptional) {
        InputStream inputStream = null;
        try {
            inputStream = ReflectUtil.getResourceAsStream(resourceName);
            if (inputStream === null) {
            if (isOptional) {
                LOG.missingSchemaResource(resourceName, operation);
            } else {
                throw LOG.missingSchemaResourceException(resourceName, operation);
            }
            } else {
            executeSchemaResource(operation, component, resourceName, inputStream);
            }

        } finally {
            IoUtil.closeSilently(inputStream);
        }
    }

    public void executeSchemaResource(String schemaFileResourceName) {
        FileInputStream inputStream = null;
        try {
            inputStream = new FileInputStream(new File(schemaFileResourceName));
            executeSchemaResource("schema operation", "process engine", schemaFileResourceName, inputStream);
        } catch (FileNotFoundException e) {
            throw LOG.missingSchemaResourceFileException(schemaFileResourceName, e);
        } finally {
            IoUtil.closeSilently(inputStream);
        }
    }

    private void executeSchemaResource(String operation, String component, String resourceName, InputStream inputStream) {
        String sqlStatement = null;
        String exceptionSqlStatement = null;
        try {
            Connection connection = ExceptionUtil.doWithExceptionWrapper(() -> $this->sqlSession->getConnection());
            Exception exception = null;
            byte[] bytes = IoUtil.readInputStream(inputStream, resourceName);
            String ddlStatements = new String(bytes);
            BufferedReader reader = new BufferedReader(new StringReader(ddlStatements));
            String line = readNextTrimmedLine(reader);

            List<String> logLines = new ArrayList<>();

            while (line !== null) {
                if (line.startsWith("# ")) {
                    logLines.add(line.substring(2));
                } else if (line.startsWith("-- ")) {
                    logLines.add(line.substring(3));
                } else if (line.length()>0) {
                    if (line.endsWith(";")) {
                        sqlStatement = addSqlStatementPiece(sqlStatement, line.substring(0, line.length()-1));
                        try {
                            Statement jdbcStatement = connection.createStatement();
                            // no logging needed as the connection will log it
                            logLines.add(sqlStatement);
                            jdbcStatement.execute(sqlStatement);
                            jdbcStatement.close();
                        } catch (Exception e) {
                            if (exception === null) {
                                exception = e;
                                exceptionSqlStatement = sqlStatement;
                            }
                            LOG.failedDatabaseOperation(operation, sqlStatement, e);
                        } finally {
                            sqlStatement = null;
                        }
                    } else {
                        sqlStatement = addSqlStatementPiece(sqlStatement, line);
                    }
                }
                line = readNextTrimmedLine(reader);
            }
            LOG.performingDatabaseOperation(operation, component, resourceName);
            LOG.executingDDL(logLines);

            if (exception !== null) {
            throw exception;
            }

            LOG.successfulDatabaseOperation(operation, component);
        } catch (Exception e) {
            throw LOG.performDatabaseOperationException(operation, exceptionSqlStatement, e);
        }
    }

    protected String addSqlStatementPiece(String sqlStatement, String line) {
        if (sqlStatement==null) {
            return line;
        }
        return sqlStatement + " \n" + line;
    }

    protected function readNextTrimmedLine(BufferedReader reader): string
    {
        String line = reader.readLine();
        if (line!=null) {
            line = line.trim();
        }
        return line;
    }*/

    protected function isMissingTablesException(\Exception $e): bool
    {
        $cause = method_exists($e, "getCause") ? $e->getCause() : null;
        if ($cause !== null) {
            $exceptionMessage = $cause->getMessage();
            if ($exceptionMessage !== null) {
                // Matches message returned from H2
                /*if (str_contains($exceptionMessage, "Table") && (exceptionMessage.contains("not found"))) {
                    return true;
                }

                // Message returned from MySQL and Oracle
                if ((exceptionMessage.contains("Table") || exceptionMessage.contains("table")) && (exceptionMessage.contains("doesn't exist"))) {
                    return true;
                }*/

                // Message returned from Postgres
                return (str_contains($exceptionMessage, "relation") || str_contains($exceptionMessage, "table")) && str_contains($exceptionMessage, "does not exist");
            }
        }
        return false;
    }

    protected function getTableTypes(): array
    {
        // the PostgreSQL JDBC API changed in 42.2.11 and partitioned tables
        // are not detected unless the corresponding table type flag is added.
        /*if (DatabaseUtil.checkDatabaseType(DbSqlSessionFactory.POSTGRES)) {
            return PG_DBC_METADATA_TABLE_TYPES;
        }
        return DBC_METADATA_TABLE_TYPES;*/
        return self::PG_DBC_METADATA_TABLE_TYPES;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getSqlSession(): SqlSessionInterface
    {
        return $this->sqlSession;
    }

    public function getDbSqlSessionFactory(): SessionFactoryInterface
    {
        return $this->dbSqlSessionFactory;
    }
}
