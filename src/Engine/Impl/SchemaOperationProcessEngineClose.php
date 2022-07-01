<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\PersistenceSessionInterface;
use Jabe\Engine\Impl\Interceptor\{
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
}
