<?php

namespace Jabe\Impl\Db\Sql;

use Doctrine\DBAL\Connection;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    FlushResult,
    StatementInterface
};
use Jabe\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbEntityOperation,
    DbOperation,
    DbOperationType
};
use Jabe\Impl\Util\{
    CollectionUtil,
    EnsureUtil,
    ExceptionUtil
};

class BatchDbSqlSession extends DbSqlSession
{
    public function __construct(DbSqlSessionFactory $dbSqlSessionFactory, Connection $connection = null, ?string $catalog = null, ?string $schema = null)
    {
        parent::__construct($dbSqlSessionFactory, $connection, $catalog, $schema);
    }

    public function executeDbOperations(array $operations): FlushResult
    {
        foreach ($operations as $it => $operation) {
            try {
                // stage operation
                $this->executeDbOperation($operation);
            } catch (\Exception $ex) {
                // exception is wrapped later
                throw $ex;
            }
        }

        $batchResults = [];
        try {
            // applies all operations
            $batchResults = $this->flushBatchOperations();
        } catch (\Exception $e) {
            return $this->postProcessBatchFailure($operations, $e);
        }

        return $this->postProcessBatchSuccess($operations, $batchResults);
    }

    protected function postProcessBatchSuccess(array $operations, array $batchResults): FlushResult
    {
        $operationsIt = new \ArrayIterator($operations);
        $failedOperations = [];
        foreach ($batchResults as $key => $successfulBatch) {
            // even if all batches are successful, there can be concurrent modification failures
            // (e.g. 0 rows updated)
            $this->postProcessDbalBatchResult($operationsIt, $successfulBatch->getUpdateCounts(), null, $failedOperations);
        }
        // there should be no more operations remaining
        if ($operationsIt->valid()) {
            //throw LOG.wrongBatchResultsSizeException(operations);
            throw new \Exception("wrongBatchResultsSizeException");
        }

        return FlushResult::withFailures($failedOperations);
    }

    protected function postProcessBatchFailure(array $operations, \Exception $exception): FlushResult
    {
        $batchExecutorException = ExceptionUtil::findBatchExecutorException($exception);

        if ($batchExecutorException === null) {
            // Unexpected exception
            throw $exception;
        }

        $successfulBatches = $batchExecutorException->getSuccessfulBatchResults();
        $cause = $batchExecutorException->getBatchUpdateException();

        $operationsIt = new \ArrayIterator($operations);
        $failedOperations = [];

        foreach ($successfulBatches as $successfulBatch) {
            $this->postProcessDbalBatchResult($operationsIt, $successfulBatch->getUpdateCounts(), null, $failedOperations);
        }

        $failedBatchUpdateCounts = $cause->getUpdateCounts();
        $this->postProcessDbalBatchResult($operationsIt, $failedBatchUpdateCounts, $exception, $failedOperations);

        $remainingOperations = CollectionUtil::collectInList($operationsIt);
        return FlushResult::withFailuresAndRemaining($failedOperations, $remainingOperations);
    }

    /**
     * <p>This method can be called with three cases:
     *
     * <ul>
     * <li>Case 1: Success. statementResults contains the number of
     * affected rows for all operations.
     * <li>Case 2: Failure. statementResults contains the number of
     * affected rows for all successful operations that were executed
     * before the failed operation.
     * <li>Case 3: Failure. statementResults contains the number of
     * affected rows for all operations of the batch, i.e. further
     * statements were executed after the first failed statement.
     * </ul>
     *
     * <p>See BatchUpdateException#getUpdateCounts() for the specification
     * of cases 2 and 3.
     *
     * @return all failed operations
     */
    protected function postProcessDbalBatchResult(
        \ArrayIterator $operationsIt,
        array $statementResults,
        \Exception $failure,
        array &$failedOperations
    ): void {
        $failureHandled = false;

        foreach ($statementResults as $statementResult) {
            EnsureUtil::ensureTrue("More batch results than scheduled operations detected. This indicates a bug", $operationsIt->valid());

            $operation = $operationsIt->current();
            $operationsIt->next();

            if ($statementResult == StatementInterface::SUCCESS_NO_INFO) {
                if ($this->requiresAffectedRows($operation->getOperationType())) {
                    //throw LOG.batchingNotSupported(operation);
                    throw new \Exception("batchingNotSupported");
                } else {
                    $this->postProcessOperationPerformed($operation, 1, null);
                }
            } elseif ($statementResult == StatementInterface::EXECUTE_FAILED) {
                /*
                * All operations are marked with the root failure exception; this is not quite
                * correct and leads to the situation that we treat all failed operations in the
                * same way, whereas they might fail for different reasons.
                *
                * More precise would be to use BatchUpdateException#getNextException.
                * E.g. if we have three failed statements in a batch, #getNextException can be used to
                * access each operation's individual failure. However, this behavior is not
                * guaranteed by the java.sql javadocs (it doesn't specify that the number
                * and order of next exceptions matches the number of failures, unlike for row counts),
                * so we decided to not rely on it.
                */
                $this->postProcessOperationPerformed($operation, 0, $failure);
                $failureHandled = true;
            } else { // it is the number of affected rows
                $this->postProcessOperationPerformed($operation, $statementResult, null);
            }

            if ($operation->isFailed()) {
                $failedOperations[] = $operation; // the operation is added to the list only if it's marked as failed
            }
        }

        /*
        * case 2: The next operation is the one that failed
        */
        if ($failure !== null && !$failureHandled) {
            EnsureUtil::ensureTrue("More batch results than scheduled operations detected. This indicates a bug", $operationsIt->valid());

            $failedOperation = $operationsIt->current();
            $operationsIt->next();
            $this->postProcessOperationPerformed($failedOperation, 0, $failure);
            if ($failedOperation->isFailed()) {
                $failedOperations[] = $failedOperation; // the operation is added to the list only if it's marked as failed
            }
        }
    }

    protected function requiresAffectedRows(?string $operationType): bool
    {
        /*
        * Affected rows required:
        * - UPDATE and DELETE: optimistic locking
        * - BULK DELETE: history cleanup
        * - BULK UPDATE: not required currently, but we'll require it for consistency with deletes
        *
        * Affected rows not required:
        * - INSERT: not required for any functionality and some databases
        *   have performance optimizations that sacrifice this (e.g. Postgres with reWriteBatchedInserts)
        */
        return $operationType != DbOperationType::INSERT;
    }

    protected function postProcessOperationPerformed(
        DbOperation $operation,
        int $rowsAffected,
        \Exception $failure
    ): void {
        switch ($operation->getOperationType()) {
            case DbOperationType::INSERT:
                $this->entityInsertPerformed($operation, $rowsAffected, $failure);
                break;
            case DbOperationType::DELETE:
                $this->entityDeletePerformed($operation, $rowsAffected, $failure);
                break;
            case DbOperationType::DELETE_BULK:
                $this->bulkDeletePerformed($operation, $rowsAffected, $failure);
                break;
            case DbOperationType::UPDATE:
                $this->entityUpdatePerformed($operation, $rowsAffected, $failure);
                break;
            case DbOperationType::UPDATE_BULK:
                $this->bulkUpdatePerformed($operation, $rowsAffected, $failure);
                break;
        }
    }

    protected function updateEntity(DbEntityOperation $operation): void
    {
        $dbEntity = $operation->getEntity();

        $updateStatement = $this->dbSqlSessionFactory->getUpdateStatement($dbEntity);
        EnsureUtil::ensureNotNull("no update statement for " . get_class($dbEntity) . " in the ibatis mapping files", "updateStatement", $updateStatement);

        //LOG.executeDatabaseOperation("UPDATE", dbEntity);
        $this->executeUpdate($updateStatement, $dbEntity);
    }

    protected function updateBulk(DbBulkOperation $operation): void
    {
        $statement = $operation->getStatement();
        $parameter = $operation->getParameter();

        //LOG.executeDatabaseBulkOperation("UPDATE", statement, parameter);

        $this->executeUpdate($statement, $parameter);
    }

    protected function deleteBulk(DbBulkOperation $operation): void
    {
        $statement = $operation->getStatement();
        $parameter = $operation->getParameter();

        //LOG.executeDatabaseBulkOperation("DELETE", statement, parameter);

        $this->executeDelete($statement, $parameter);
    }

    protected function deleteEntity(DbEntityOperation $operation): void
    {
        $dbEntity = $operation->getEntity();

        // get statement
        $deleteStatement = $this->dbSqlSessionFactory->getDeleteStatement(get_class($dbEntity));
        EnsureUtil::ensureNotNull("no delete statement for " . get_class($dbEntity) . " in the ibatis mapping files", "deleteStatement", $deleteStatement);

        //LOG.executeDatabaseOperation("DELETE", dbEntity);

        // execute the delete
        $this->executeDelete($deleteStatement, $dbEntity);
    }

    protected function executeSelectForUpdate(?string $statement, $parameter = null): void
    {
        $this->executeSelectList($statement, $parameter);
    }
}
