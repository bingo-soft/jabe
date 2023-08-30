<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Telemetry\TelemetryLogger;
use Jabe\Impl\Util\TelemetryUtil;

class TelemetryConfigureCmd implements CommandInterface
{
    //protected static final TelemetryLogger LOG = ProcessEngineLogger.TELEMETRY_LOGGER;

    protected const TELEMETRY_PROPERTY = "telemetry.enabled";

    protected bool $telemetryEnabled = false;

    public function __construct(bool $telemetryEnabled)
    {
        $this->telemetryEnabled = $telemetryEnabled;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $authorizationManager = $commandContext->getAuthorizationManager();
        $authorizationManager->checkAdminOrPermission("checkConfigureTelemetry");

        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $scope->toggleTelemetry($commandContext);
            return null;
        });

        return null;
    }

    protected function toggleTelemetry(CommandContext $commandContext)
    {
        $currentValue = (new IsTelemetryEnabledCmd())->execute($commandContext);

        (new SetPropertyCmd(self::TELEMETRY_PROPERTY, strval($this->telemetryEnabled)))->execute($commandContext);

        $processEngineConfiguration = $commandContext->getProcessEngineConfiguration();

        $isReportedActivated = $processEngineConfiguration->isTelemetryReporterActivate();
        $telemetryReporter = $processEngineConfiguration->getTelemetryReporter();

        // telemetry enabled or set for the first time
        if ($currentValue === null || (!$currentValue->booleanValue() && $this->telemetryEnabled)) {
            if ($isReportedActivated) {
                $telemetryReporter->reschedule();
            }
        }

        // reset collected data when telemetry is enabled
        // we don't want to send data that has been collected before consent was given
        TelemetryUtil::toggleLocalTelemetry(
            $this->telemetryEnabled,
            $processEngineConfiguration->getTelemetryRegistry(),
            $processEngineConfiguration->getMetricsRegistry()
        );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
