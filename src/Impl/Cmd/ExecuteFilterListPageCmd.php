<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Query\QueryInterface;

class ExecuteFilterListPageCmd extends AbstractExecuteFilterCmd implements CommandInterface
{
    protected int $firstResult = 0;
    protected int $maxResults = 0;

    public function __construct(?string $filterId, ?QueryInterface $extendingQuery, int $firstResult, int $maxResults)
    {
        parent::__construct($filterId, $extendingQuery);
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext, ...$args): array
    {
        $query = $this->getFilterQuery($commandContext);
        $query->enableMaxResultsLimit();
        return $query->listPage($this->firstResult, $this->maxResults);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
