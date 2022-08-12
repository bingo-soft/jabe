<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class IsTelemetryEnabledCmd implements CommandInterface
{
    //protected static final TelemetryLogger LOG = ProcessEngineLogger.TELEMETRY_LOGGER;

    public function execute(CommandContext $commandContext)
    {
        $authorizationManager = $commandContext->getAuthorizationManager();
        $authorizationManager->checkAdminOrPermission("checkReadTelemetryCollectionStatusData");

        $telemetryProperty = $commandContext->getPropertyManager()->findPropertyById("telemetry.enabled");
        if ($telemetryProperty !== null) {
            if (strtolower($telemetryProperty->getValue()) == "null") {
                return null;
            } else {
                return boolval($telemetryProperty->getValue());
            }
        } else {
            //LOG.databaseTelemetryPropertyMissingInfo();
            return null;
        }
    }
}
