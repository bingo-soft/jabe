<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Db\EntityManager\Operation\{
    DbBulkOperation,
    DbOperationType
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Management\{
    DatabasePurgeReport,
    PurgeReport
};
use Jabe\Impl\Persistence\Entity\ByteArrayEntity;

class PurgeDatabaseAndCacheCmd implements CommandInterface
{
    protected const DELETE_TABLE_DATA = "deleteTableData";
    protected const SELECT_TABLE_COUNT = "selectTableCount";
    protected const TABLE_NAME = "tableName";
    protected const EMPTY_STRING = "";

    public const TABLENAMES_EXCLUDED_FROM_DB_CLEAN_CHECK = [
        "ACT_GE_PROPERTY",
        "ACT_GE_SCHEMA_LOG"
    ];

    public function execute(CommandContext $commandContext)
    {
        $purgeReport = new PurgeReport();

        // purge the database
        $databasePurgeReport = $this->purgeDatabase($commandContext);
        $purgeReport->setDatabasePurgeReport($databasePurgeReport);

        // purge the deployment cache
        $deploymentCache = $commandContext->getProcessEngineConfiguration()->getDeploymentCache();
        $cachePurgeReport = $deploymentCache->purgeCache();
        $purgeReport->setCachePurgeReport($cachePurgeReport);

        return $purgeReport;
    }

    private function purgeDatabase(CommandContext $commandContext): DatabasePurgeReport
    {
        $dbEntityManager = $commandContext->getDbEntityManager();
        // For MySQL and MariaDB we have to disable foreign key check,
        // to delete the table data as bulk operation (execution, incident etc.)
        // The flag will be reset by the DBEntityManager after flush.
        $dbEntityManager->setIgnoreForeignKeysForNextFlush(true);
        $tablesNames = $dbEntityManager->getTableNamesPresentInDatabase();
        $databaseTablePrefix = $commandContext->getProcessEngineConfiguration()->getDatabaseTablePrefix()->trim();

        // for each table
        $databasePurgeReport = new DatabasePurgeReport();
        foreach ($tablesNames as $tableName) {
            $tableNameWithoutPrefix = str_replace($databaseTablePrefix, self::EMPTY_STRING, $tableName);
            if (!in_array($tableNameWithoutPrefix, self::TABLENAMES_EXCLUDED_FROM_DB_CLEAN_CHECK)) {
                // Check if table contains data
                $param = [];
                $param[self::TABLE_NAME] = $tableName;
                $count = $dbEntityManager->selectOne(self::SELECT_TABLE_COUNT, $param);

                if ($count > 0) {
                    // allow License Key in byte array table
                    if ($tableNameWithoutPrefix == "ACT_GE_BYTEARRAY" && $commandContext->getResourceManager()->findLicenseKeyResource() !== null) {
                        if ($count != 1) {
                            $purgeByteArrayPreserveLicenseKeyBulkOp = new DbBulkOperation(
                                DbOperationType::DELETE_BULK,
                                ByteArrayEntity::class,
                                "purgeTablePreserveLicenseKey",
                                LicenseCmd::LICENSE_KEY_BYTE_ARRAY_ID
                            );
                            $databasePurgeReport->addPurgeInformation($tableName, $count - 1);
                            $dbEntityManager->getDbOperationManager()->addOperation($purgeByteArrayPreserveLicenseKeyBulkOp);
                        }
                        $databasePurgeReport->setDbContainsLicenseKey(true);
                        continue;
                    }
                    $databasePurgeReport->addPurgeInformation($tableName, $count);
                    // Get corresponding entity classes for the table, which contains data
                    $entities = $commandContext->getTableDataManager()->getEntities($tableName);
                    if (empty($entities)) {
                        throw new ProcessEngineException("No mapped implementation of "
                                                        . DbEntity::class
                                                        . " was found for: "
                                                        . $tableName);
                    }
                    // Delete the table data as bulk operation with the first entity
                    $entity = $entities[0];
                    $deleteBulkOp = new DbBulkOperation(DbOperationType::DELETE_BULK, $entity, self::DELETE_TABLE_DATA, $param);
                    $dbEntityManager->getDbOperationManager()->addOperation($deleteBulkOp);
                }
            }
        }
        return $databasePurgeReport;
    }
}
