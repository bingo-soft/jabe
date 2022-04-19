<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTelemetryDataCmd implements CommandInterface
{
    private $configuration;

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTelemetryData");

        $this->configuration = $commandContext->getProcessEngineConfiguration();

        $telemetryReporter = $this->configuration->getTelemetryReporter();
        if ($telemetryReporter != null) {
            return $telemetryReporter->getTelemetrySendingTask()->updateAndSendData(false, false);
        } else {
            //throw ProcessEngineLogger.TELEMETRY_LOGGER.exceptionWhileRetrievingTelemetryDataRegistryNull();
            throw new \Exception("exceptionWhileRetrievingTelemetryDataRegistryNull");
        }
    }
}
