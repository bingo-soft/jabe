<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\{
    ProcessEngineConfiguration,
    SchemaOperationsCommandInterface
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    EnginePersistenceLogger,
    PersistenceSessionInterface
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\PropertyEntity;

class SchemaOperationsProcessEngineBuild implements SchemaOperationsCommandInterface
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public function execute(CommandContext $commandContext)
    {
        $databaseSchemaUpdate = Context::getProcessEngineConfiguration()->getDatabaseSchemaUpdate();
        $persistenceSession = $commandContext->getSession(PersistenceSessionInterface::class);
        if (ProcessEngineConfigurationImpl::DB_SCHEMA_UPDATE_DROP_CREATE == $databaseSchemaUpdate) {
            try {
                $persistenceSession->dbSchemaDrop();
            } catch (\Exception $e) {
                // ignore
            }
        }
        if (
            ProcessEngineConfiguration::DB_SCHEMA_UPDATE_CREATE_DROP == $databaseSchemaUpdate
            || ProcessEngineConfigurationImpl::DB_SCHEMA_UPDATE_DROP_CREATE == $databaseSchemaUpdate
            || ProcessEngineConfigurationImpl::DB_SCHEMA_UPDATE_CREATE == $databaseSchemaUpdate
        ) {
            $persistenceSession->dbSchemaCreate();
        } elseif (ProcessEngineConfiguration::DB_SCHEMA_UPDATE_FALSE == $databaseSchemaUpdate) {
            $persistenceSession->dbSchemaCheckVersion();
        } elseif (ProcessEngineConfiguration::DB_SCHEMA_UPDATE_TRUE == $databaseSchemaUpdate) {
            $persistenceSession->dbSchemaUpdate();
        }

        return null;
    }
}
