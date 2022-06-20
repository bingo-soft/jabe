<?php

namespace Jabe\Engine\Impl\Db\Sql;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\PersistenceSessionInterface;
use Jabe\Engine\Impl\Interceptor\{
    SessionInterface,
    SessionFactoryInterface
};

class DbSqlPersistenceProviderFactory implements SessionFactoryInterface
{

    public function getSessionType(): string
    {
        return PersistenceSessionInterface::class;
    }

    public function openSession(): SessionInterface
    {
        return Context::getCommandContext()->getDbSqlSession();
    }
}
