<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Management\Metrics;

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
