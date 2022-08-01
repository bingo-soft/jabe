<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Query\QueryInterface;

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
