<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\{
    EntityTypes,
    FilterServiceInterface
};
use Jabe\Engine\Filter\{
    FilterInterface,
    FilterQueryInterface
};
use Jabe\Engine\Impl\Cmd\{
    CreateFilterCmd,
    DeleteFilterCmd,
    ExecuteFilterCountCmd,
    ExecuteFilterListCmd,
    ExecuteFilterListPageCmd,
    ExecuteFilterSingleResultCmd,
    GetFilterCmd,
    SaveFilterCmd
};
use Jabe\Engine\Impl\Filter\FilterQueryImpl;
use Jabe\Engine\Query\QueryInterface;

class FilterServiceImpl extends ServiceImpl implements FilterServiceInterface
{
    public function newTaskFilter(string $filterName = null): FilterInterface
    {
        $filter = $this->commandExecutor->execute(new CreateFilterCmd(EntityTypes::TASK));
        if ($filterName !== null) {
            $filter->setName($filterName);
        }
        return $filter;
    }

    public function createFilterQuery(): FilterQueryIntefrace
    {
        return new FilterQueryImpl($this->commandExecutor);
    }

    public function createTaskFilterQuery(): FilterQueryInterface
    {
        return (new FilterQueryImpl($this->commandExecutor))->filterResourceType(EntityTypes::TASK);
    }

    public function saveFilter(FilterInterface $filter): FilterInterface
    {
        return $this->commandExecutor->execute(new SaveFilterCmd($filter));
    }

    public function getFilter(string $filterId): FilterInterface
    {
        return $this->commandExecutor->execute(new GetFilterCmd($filterId));
    }

    public function deleteFilter(string $filterId): void
    {
        $this->commandExecutor->execute(new DeleteFilterCmd($filterId));
    }

    public function list(string $filterId, ?QueryInterface $extendingQuery = null): array
    {
        return $this->commandExecutor->execute(new ExecuteFilterListCmd($filterId, $extendingQuery));
    }

    public function listPage(string $filterId, ?QueryInterface $extendingQuery, int $firstResult, int $maxResults): array
    {
        return $this->commandExecutor->execute(new ExecuteFilterListPageCmd($filterId, $extendingQuery, $firstResult, $maxResults));
    }

    public function singleResult(string $filterId, ?QueryInterface $extendingQuery = null)
    {
        return $this->commandExecutor->execute(new ExecuteFilterSingleResultCmd($filterId, $extendingQuery));
    }

    public function count(string $filterId, ?QueryInterface $extendingQuery = null): int
    {
        return $this->commandExecutor->execute(new ExecuteFilterCountCmd($filterId, $extendingQuery));
    }
}
