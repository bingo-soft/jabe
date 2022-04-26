<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Management\Metrics;

class SuccessfulJobListener implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $this->logJobSuccess($commandContext);
        return null;
    }

    protected function logJobSuccess(CommandContext $commandContext): void
    {
        if ($commandContext->getProcessEngineConfiguration()->isMetricsEnabled()) {
            $commandContext->getProcessEngineConfiguration()
            ->getMetricsRegistry()
            ->markOccurrence(Metrics::JOB_SUCCESSFUL);
        }
    }
}
