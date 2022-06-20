<?php

namespace Jabe\Engine\Impl\Db\Sql;

use Doctrine\DBAL\Connection;
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    FlushResult
};
use Jabe\Engine\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbEntityOperation,
    DbOperation
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SimpleDbSqlSession extends DbSqlSession
{
    public function __construct(DbSqlSessionFactory $dbSqlSessionFactory, Connection $connection = null, string $catalog = null, string $schema = null)
    {
        parent::__construct($dbSqlSessionFactory, $connection, $catalog, $schema);
    }

    // lock ////////////////////////////////////////////

    protected function executeSelectForUpdate(string $statement, array $parameters = []): void
    {
        $this->update($statement, $parameters);
    }

    public function executeDbOperations(array $operations): FlushResult
    {
        for ($i = 0; $i < count($operations); $i += 1) {
            $operation = $operations[$i];

            $this->executeDbOperation($operation);

            if ($operation->isFailed()) {
                $remainingOperations = array_slice($operations, $i + 1, count($operations));
                return FlushResult::withFailuresAndRemaining([$operation], $remainingOperations);
            }
        }

        return FlushResult::allApplied();
    }

    // insert //////////////////////////////////////////

    protected function insertEntity(DbEntityOperation $operation): void
    {
        //TODO. Use Doctrine ORM instead!!!
        $dbEntity = $operation->getEntity();

        // get statement
        $insertStatement = $this->dbSqlSessionFactory->getInsertStatement($dbEntity);
        $insertStatement = $this->dbSqlSessionFactory->mapStatement($insertStatement);
        EnsureUtil::ensureNotNull("no insert statement for " . get_class($dbEntity) . " in mapping files", "insertStatement", $insertStatement);

        // execute the insert
        try {
            $this->executeInsertEntity($insertStatement, $dbEntity);
            $this->entityInsertPerformed($operation, 1, null);
        } catch (\Exception $e) {
            $this->entityInsertPerformed($operation, 0, $e);
        }
    }

    // delete ///////////////////////////////////////////

    protected function deleteEntity(DbEntityOperation $operation): void
    {
        $dbEntity = $operation->getEntity();

        // get statement
        $deleteStatement = $dbSqlSessionFactory->getDeleteStatement(get_class($dbEntity));
        EnsureUtil::ensureNotNull("no delete statement for " . get_class($dbEntity) . " in mapping files", "deleteStatement", $deleteStatement);
        //LOG.executeDatabaseOperation("DELETE", dbEntity);
        try {
            $nrOfRowsDeleted = $this->executeDelete($deleteStatement, $dbEntity);
            $this->entityDeletePerformed($operation, $nrOfRowsDeleted, null);
        } catch (\Exception $e) {
            $this->entityDeletePerformed($operation, 0, $e);
        }
    }

    protected function deleteBulk(DbBulkOperation $operation): void
    {
        $statement = $operation->getStatement();
        $parameter = $operation->getParameter();
        //LOG.executeDatabaseBulkOperation("DELETE", statement, parameter);

        try {
            $rowsAffected = $this->executeDelete($statement, $parameter);
            $this->bulkDeletePerformed($operation, $rowsAffected, null);
        } catch (\Exception $e) {
            $this->bulkDeletePerformed($operation, 0, $e);
        }
    }

    // update ////////////////////////////////////////

    protected function updateEntity(DbEntityOperation $operation): void
    {
        $dbEntity = $operation->getEntity();

        $updateStatement = $dbSqlSessionFactory->getUpdateStatement($dbEntity);
        EnsureUtil::ensureNotNull("no update statement for " . get_class($dbEntity) . " in mapping files", "updateStatement", $updateStatement);

        //LOG.executeDatabaseOperation("UPDATE", dbEntity);

        try {
            $rowsAffected = $this->executeUpdate($updateStatement, $dbEntity);
            $this->entityUpdatePerformed($operation, $rowsAffected, null);
        } catch (\Exception $e) {
            $this->entityUpdatePerformed($operation, 0, $e);
        }
    }

    protected function updateBulk(DbBulkOperation $operation): void
    {
        $statement = $operation->getStatement();
        $parameter = $operation->getParameter();
        //LOG.executeDatabaseBulkOperation("UPDATE", statement, parameter);
        try {
            $rowsAffected = $this->executeUpdate($statement, $parameter);
            $this->bulkUpdatePerformed($operation, $rowsAffected, null);
        } catch (\Exception $e) {
            $this->bulkUpdatePerformed($operation, 0, $e);
        }
    }
}
