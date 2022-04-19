<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class ReportDbMetricsCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
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
}
