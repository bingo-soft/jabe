<?php

namespace Jabe\Impl\Metrics;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class MetricsQuerySumCmd implements CommandInterface
{
    protected $metricsQuery;

    public function __construct(MetricsQueryImpl $metricsQuery)
    {
        $this->metricsQuery = $metricsQuery;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getMeterLogManager()
            ->executeSelectSum($this->metricsQuery);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
