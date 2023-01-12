<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class SchemaOperationProcessEngineClose implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $databaseSchemaUpdate = Context::getProcessEngineConfiguration()->getDatabaseSchemaUpdate();
        if (ProcessEngineConfiguration::DB_SCHEMA_UPDATE_CREATE_DROP == $databaseSchemaUpdate) {
            $commandContext->getSession(PersistenceSession::class)->dbSchemaDrop();
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
