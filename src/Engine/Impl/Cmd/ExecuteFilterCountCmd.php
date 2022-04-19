<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Query\QueryInterface;

class ExecuteFilterCountCmd extends AbstractExecuteFilterCmd implements CommandInterface
{
    public function __construct(string $filterId, ?QueryInterface $extendingQuery = null)
    {
        parent::__construct($filterId, $extendingQuery);
    }

    public function execute(CommandContext $commandContext)
    {
        $filter = $this->getFilter($commandContext);
        return $filter->getQuery()->count();
    }
}
