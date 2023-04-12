<?php

namespace Jabe\Impl\Metrics;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class MetricsQueryIntervalCmd implements CommandInterface
{
    protected $metricsQuery;

    public function __construct(MetricsQueryImpl $metricsQuery)
    {
        $this->metricsQuery = $metricsQuery;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getMeterLogManager()
            ->executeSelectInterval($metricsQuery);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
