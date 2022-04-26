<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\AbstractQuery;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Query\QueryInterface;

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
        return $query->listPage($firstResult, $maxResults);
    }
}
