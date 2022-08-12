<?php

namespace Jabe\Impl;

use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Management\{
    TablePage,
    TablePageQueryInterface
};

class TablePageQueryImpl extends ListQueryParameterObject implements TablePageQueryInterface, CommandInterface, \Serializable
{
    protected $commandExecutor;
    protected $tableName;
    protected $order;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function tableName(string $tableName): TablePageQueryImpl
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function orderAsc(string $column): TablePageQueryImpl
    {
        $this->orderingProperties[] = new QueryOrderingProperty(new QueryPropertyImpl($column), Direction::ascending());
        return $this;
    }

    public function orderDesc(string $column): TablePageQueryImpl
    {
        $this->orderingProperties[] = new QueryOrderingProperty(new QueryPropertyImpl($column), Direction::descending());
        return $this;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function listPage(int $firstResult, int $maxResults): TablePage
    {
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
        return $this->commandExecutor->execute($this);
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdmin();
        return $commandContext
            ->getTableDataManager()
            ->getTablePage($this);
    }

    public function getOrder(): string
    {
        return $this->order;
    }
}
