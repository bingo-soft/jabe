<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\AbstractQuery;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Query\QueryInterface;

class ExecuteFilterListCmd extends AbstractExecuteFilterCmd implements CommandInterface
{
    public function __construct(string $filterId, ?QueryInterface $extendingQuery)
    {
        parent::__construct($filterId, $extendingQuery);
    }

    public function execute(CommandContext $commandContext)
    {
        $query = $this->getFilterQuery($commandContext);
        $query->enableMaxResultsLimit();
        return $query->list();
    }
}
