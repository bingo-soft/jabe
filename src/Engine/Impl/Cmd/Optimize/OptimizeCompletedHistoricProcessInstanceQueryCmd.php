<?php

namespace Jabe\Engine\Impl\Cmd\Optimize;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeCompletedHistoricProcessInstanceQueryCmd implements CommandInterface
{
    protected $finishedAfter;
    protected $finishedAt;
    protected $maxResults;

    public function __construct(string $finishedAfter, string $finishedAt, int $maxResults)
    {
        $this->finishedAfter = $finishedAfter;
        $this->finishedAt = $finishedAt;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getOptimizeManager()->getCompletedHistoricProcessInstances($this->finishedAfter, $this->finishedAt, $this->maxResults);
    }
}
