<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Util\ClassNameUtil;

class CommandCounterInterceptor extends CommandInterceptor
{
    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration)
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function execute(CommandInterface $command, ...$args)
    {
        try {
            if (empty($args) && !empty($this->getState())) {
                $args = $this->getState();
            }
            return $this->next->execute($command, ...$args);
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
