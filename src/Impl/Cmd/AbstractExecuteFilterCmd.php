<?php

namespace Jabe\Impl\Cmd;

use Jabe\Filter\FilterInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Query\QueryInterface;
use Jabe\Task\TaskQueryInterface;

abstract class AbstractExecuteFilterCmd
{
    protected $filterId;
    protected $extendingQuery;

    public function __construct(?string $filterId, ?QueryInterface $extendingQuery = null)
    {
        $this->filterId = $filterId;
        $this->extendingQuery = $extendingQuery;
    }

    public function __serialize(): array
    {
        return [
            'filterId' => $this->filterId,
            'extendingQuery' => serialize($this->extendingQuery)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->filterId = $data['filterId'];
        $this->extendingQuery = unserialize($data['extendingQuery']);
    }

    protected function getFilter(CommandContext $commandContext): FilterInterface
    {
        EnsureUtil::ensureNotNull("No filter id given to execute", "filterId", $this->filterId);
        $filter = $commandContext
            ->getFilterManager()
            ->findFilterById($this->filterId);

        EnsureUtil::ensureNotNull("No filter found for id '" . $this->filterId . "'", "filter", $filter);

        if ($this->extendingQuery !== null) {
            $this->extendingQuery->validate();
            $filter = $filter->extend($this->extendingQuery);
        }

        return $filter;
    }

    protected function getFilterQuery(CommandContext $commandContext): QueryInterface
    {
        $filter = $this->getFilter($commandContext);
        $query = $filter->getQuery();
        if ($query instanceof TaskQueryInterface) {
            $query->initializeFormKeys();
        }
        return $query;
    }
}
