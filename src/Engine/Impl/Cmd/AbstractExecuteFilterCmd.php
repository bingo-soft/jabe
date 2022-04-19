<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Filter\FilterInterface;
use BpmPlatform\Engine\Impl\AbstractQuery;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\FilterEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Query\QueryInterface;
use BpmPlatform\Engine\Task\TaskQueryInterface;

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

        if ($this->extendingQuery != null) {
            $extendingQuery->validate();
            $filter = $filter->extend($extendingQuery);
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
