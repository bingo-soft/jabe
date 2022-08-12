<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Query\QueryInterface;

class ExecuteFilterListPageCmd extends AbstractExecuteFilterCmd implements CommandInterface
{
    protected $firstResult;
    protected $maxResults;

    public function __construct(string $filterId, ?QueryInterface $extendingQuery, int $firstResult, int $maxResults)
    {
        parent::__construct($filterId, $extendingQuery);
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext): array
    {
        $query = $this->getFilterQuery($commandContext);
        $query->enableMaxResultsLimit();
        return $query->listPage($this->firstResult, $this->maxResults);
    }
}
