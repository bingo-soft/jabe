<?php

namespace Jabe\Impl;

use Jabe\{
    ProcessEngineConfiguration,
    SchemaOperationsCommandInterface
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    EnginePersistenceLogger,
    PersistenceSessionInterface
};
use Jabe\Impl\Interceptor\CommandContext;

class SchemaOperationsProcessEngineBuild implements SchemaOperationsCommandInterface
{
    //private final static EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public function execute(CommandContext $commandContext, ...$args)
    {
        $databaseSchemaUpdate = Context::getProcessEngineConfiguration()->getDatabaseSchemaUpdate();
        $persistenceSession = $commandContext->getSession(PersistenceSessionInterface::class);
        if (ProcessEngineConfigurationImpl::DB_SCHEMA_UPDATE_DROP_CREATE == $databaseSchemaUpdate) {
            try {
                $persistenceSession->dbSchemaDrop();
            } catch (\Throwable $e) {
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
