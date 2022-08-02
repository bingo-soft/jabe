<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Filter\FilterInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Query\QueryInterface;
use Jabe\Engine\Task\TaskQueryInterface;

abstract class AbstractExecuteFilterCmd implements \Serializable
{
    protected $filterId;
    protected $extendingQuery;

    public function __construct(string $filterId, ?QueryInterface $extendingQuery = null)
    {
        $this->filterId = $filterId;
        $this->extendingQuery = $extendingQuery;
    }

    public function serialize()
    {
        return json_encode([
            'filterId' => $this->filterId,
            'extendingQuery' => serialize($this->extendingQuery)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->filterId = $json->filterId;
        $this->extendingQuery = unserialize($json->extendingQuery);
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
