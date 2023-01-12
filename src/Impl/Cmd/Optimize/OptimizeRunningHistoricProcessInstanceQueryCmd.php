<?php

namespace Jabe\Impl\Cmd\Optimize;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeRunningHistoricProcessInstanceQueryCmd implements CommandInterface
{
    protected $startedAfter;
    protected $startedAt;
    protected $maxResults;

    public function __construct(?string $startedAfter, ?string $startedAt, int $maxResults)
    {
        $this->startedAfter = $startedAfter;
        $this->startedAt = $startedAt;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getOptimizeManager()->getRunningHistoricProcessInstances($this->startedAfter, $this->startedAt, $this->maxResults);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
