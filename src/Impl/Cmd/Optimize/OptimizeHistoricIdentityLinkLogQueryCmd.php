<?php

namespace Jabe\Impl\Cmd\Optimize;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeHistoricIdentityLinkLogQueryCmd implements CommandInterface
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
        return $commandContext->getOptimizeManager()->getHistoricIdentityLinkLogs($this->occurreAfter, $this->occurreAt, $this->maxResults);
    }
}
