<?php

namespace Jabe\Impl\Cmd\Optimize;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeCompletedHistoricActivityInstanceQueryCmd implements CommandInterface
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
        return $commandContext->getOptimizeManager()->getCompletedHistoricActivityInstances($this->finishedAfter, $this->finishedAt, $this->maxResults);
    }
}
