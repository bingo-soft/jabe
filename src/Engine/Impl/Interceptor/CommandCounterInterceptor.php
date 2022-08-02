<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Util\ClassNameUtil;

class CommandCounterInterceptor extends CommandInterceptor
{
    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration)
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function execute(CommandInterface $command)
    {
        try {
            return $this->next->execute($command);
        } finally {
            $telemetryRegistry = $this->processEngineConfiguration->getTelemetryRegistry();
            if ($telemetryRegistry !== null && $telemetryRegistry->isCollectingTelemetryDataEnabled()) {
                $class = get_class($command);
                $className = ClassNameUtil::getClassNameWithoutPackage($class);
                $ref = new \ReflectionClass($class);
                // anonymous class/lambda implementations of the Command interface are excluded
                if (!$ref->isAnonymous()) {
                    $telemetryRegistry->markOccurrence($className);
                }
            }
        }
    }
}
