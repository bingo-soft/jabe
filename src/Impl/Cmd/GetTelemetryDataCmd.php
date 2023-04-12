<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTelemetryDataCmd implements CommandInterface
{
    private $configuration;

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTelemetryData");

        $this->configuration = $commandContext->getProcessEngineConfiguration();

        $telemetryReporter = $this->configuration->getTelemetryReporter();
        if ($telemetryReporter !== null) {
            return $telemetryReporter->getTelemetrySendingTask()->updateAndSendData(false, false);
        } else {
            //throw ProcessEngineLogger.TELEMETRY_LOGGER.exceptionWhileRetrievingTelemetryDataRegistryNull();
            throw new \Exception("exceptionWhileRetrievingTelemetryDataRegistryNull");
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
