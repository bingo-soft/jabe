<?php

namespace Jabe\Impl\Db\Sql;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\PersistenceSessionInterface;
use Jabe\Impl\Interceptor\{
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
