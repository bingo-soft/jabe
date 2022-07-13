<?php

namespace Jabe\Engine\Impl\Metrics\Parser;

use Jabe\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Engine\Impl\Context\Context;

class MetricsExecutionListener implements ExecutionListenerInterface
{
    protected $metricsName;
    protected $condition;

    public function __construct(string $metricsName, callable $condition = null)
    {
        $this->metricsName = $metricsName;
        if ($condition == null) {
            $condition = function ($arg) {
                return true;
            };
        }
        $this->condition = $condition;
    }

    public function notify(DelegateExecutionInterface $execution): void
    {
        if ($this->condition($execution)) {
            Context::getProcessEngineConfiguration()
                ->getMetricsRegistry()
                ->markOccurrence($metricsName);
        }
    }
}
