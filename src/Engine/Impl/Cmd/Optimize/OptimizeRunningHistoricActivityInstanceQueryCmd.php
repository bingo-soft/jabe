<?php

namespace Jabe\Engine\Impl\Cmd\Optimize;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeRunningHistoricActivityInstanceQueryCmd implements CommandInterface
{
    protected $startedAfter;
    protected $startedAt;
    protected $maxResults;

    public function __construct(string $startedAfter, string $startedAt, int $maxResults)
    {
        $this->startedAfter = $startedAfter;
        $this->startedAt = $startedAt;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getOptimizeManager()->getRunningHistoricActivityInstances($this->startedAfter, $this->startedAt, $this->maxResults);
    }
}
