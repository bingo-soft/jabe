<?php

namespace Jabe\Engine\Impl\Cmd\Optimize;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class OptimizeOpenHistoricIncidentsQueryCmd implements CommandInterface
{
    protected $createdAfter;
    protected $createdAt;
    protected $maxResults;

    public function __construct(string $createdAfter, string $createdAt, int $maxResults)
    {
        $this->createdAfter = $createdAfter;
        $this->createdAt = $createdAt;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getOptimizeManager()->getOpenHistoricIncidents($this->createdAfter, $this->createdAt, $this->maxResults);
    }
}
