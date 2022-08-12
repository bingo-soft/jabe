<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Query\QueryInterface;

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
