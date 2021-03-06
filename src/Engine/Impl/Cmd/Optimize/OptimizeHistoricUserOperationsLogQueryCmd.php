<?php

namespace Jabe\Engine\Impl\Cmd\Optimize;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeHistoricUserOperationsLogQueryCmd implements CommandInterface
{
    protected $occurreAfter;
    protected $occurreAt;
    protected $maxResults;

    public function __construct(string $occurreAfter, string $occurreAt, int $maxResults)
    {
        $this->occurreAfter = $occurreAfter;
        $this->occurreAt = $occurreAt;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getOptimizeManager()->getHistoricUserOperationLogs($this->occurreAfter, $this->occurreAt, $this->maxResults);
    }
}
