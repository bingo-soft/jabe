<?php

namespace Jabe\Engine\Impl\Metrics;

use Jabe\Engine\Impl\Interceptor\{
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

    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getMeterLogManager()
            ->executeSelectInterval($metricsQuery);
    }
}
