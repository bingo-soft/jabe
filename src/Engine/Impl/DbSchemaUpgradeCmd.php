<?php

namespace Jabe\Engine\Impl;

use Doctrine\DBAL\Connection;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};

class DbSchemaUpgradeCmd implements CommandInterface
{
    protected $connection;
    protected $catalog;
    protected $schema;

    public function __construct(Connection $connection, string $catalog, string $schema)
    {
        $this->connection = $connection;
        $this->catalog = $catalog;
        $this->schema = $schema;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdmin();
        $dbSqlSessionFactory = $commandContext->getSessionFactories()[DbSqlSessionInterface::class];
        $dbSqlSession = $dbSqlSessionFactory->openSession($this->connection, $this->catalog, $this->schema);
        $commandContext->addSession(DbSqlSessionInterface::class, $dbSqlSession);
        $dbSqlSession->dbSchemaUpdate();
        return "";
    }
}
