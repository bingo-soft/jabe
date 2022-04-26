<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
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
