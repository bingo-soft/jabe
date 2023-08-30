<?php

namespace Jabe\Impl\Metrics\Parser;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Impl\Context\Context;

class MetricsExecutionListener implements ExecutionListenerInterface
{
    protected $metricsName;
    protected $condition;

    public function __construct(?string $metricsName, callable $condition = null)
    {
        $this->metricsName = $metricsName;
        if ($condition == null) {
            $condition = function ($arg) {
                return true;
            };
        }
        $this->condition = $condition;
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        $condition = $this->condition;
        if ($condition($execution)) {
            Context::getProcessEngineConfiguration()
                ->getMetricsRegistry()
                ->markOccurrence($this->metricsName);
        }
    }
}
