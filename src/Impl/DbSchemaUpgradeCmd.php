<?php

namespace Jabe\Impl;

use Doctrine\DBAL\Connection;
use Jabe\Impl\Db\Sql\DbSqlSession;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};

class DbSchemaUpgradeCmd implements CommandInterface
{
    protected $connection;
    protected $catalog;
    protected $schema;

    public function __construct(Connection $connection, ?string $catalog, ?string $schema)
    {
        $this->connection = $connection;
        $this->catalog = $catalog;
        $this->schema = $schema;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdmin();
        $dbSqlSessionFactory = $commandContext->getSessionFactories()[DbSqlSession::class];
        $dbSqlSession = $dbSqlSessionFactory->openSession($this->connection, $this->catalog, $this->schema);
        $commandContext->addSession(DbSqlSession::class, $dbSqlSession);
        $dbSqlSession->dbSchemaUpdate();
        return "";
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
