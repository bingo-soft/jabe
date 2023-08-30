<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class ReportDbMetricsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        $engineConfiguration = Context::getProcessEngineConfiguration();

        if (!$engineConfiguration->isMetricsEnabled()) {
            throw new ProcessEngineException("Metrics reporting is disabled");
        }

        if (!$engineConfiguration->isDbMetricsReporterActivate()) {
            throw new ProcessEngineException("Metrics reporting to database is disabled");
        }

        $dbMetricsReporter = $engineConfiguration->getDbMetricsReporter();
        $dbMetricsReporter->reportNow();
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
