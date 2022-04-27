<?php

namespace Jabe\Engine\Impl\Context;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Application\ProcessApplicationLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;
use Jabe\Engine\Impl\JobExecutor\RunnableInterface;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity,
    TaskEntity
};
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntity;

class ProcessApplicationContextUtil
{
    //private final static ProcessApplicationLogger LOG = ProcessApplicationLogger.PROCESS_APPLICATION_LOGGER;

    public static function getTargetProcessApplication($execution = null): ?ProcessApplicationReferenceInterface
    {
        if ($execution == null) {
            return null;
        }

        if ($execution instanceof ExecutionEntity) {
            $processApplicationForDeployment = self::getTargetProcessApplication($execution->getProcessDefinition());
            return $processApplicationForDeployment;
        } elseif ($execution instanceof TaskEntity) {
            if ($execution->getProcessDefinition() != null) {
                return self::getTargetProcessApplication($execution->getProcessDefinition());
            }
            return null;
        } elseif ($execution instanceof ResourceDefinitionEntity) {
            $reference = self::getTargetProcessApplication($execution->getDeploymentId());

            if ($reference == null && self::areProcessApplicationsRegistered()) {
                $previous = $execution->getPreviousDefinition();

                // do it in a iterative way instead of recursive to avoid
                // a possible StackOverflowException in cases with a lot
                // of versions of a definition
                while ($previous != null) {
                    $reference = self::getTargetProcessApplication($previous->getDeploymentId());
                    if ($reference == null) {
                        $previous = $previous->getPreviousDefinition();
                    } else {
                        return $reference;
                    }
                }
            }
            return $reference;
        } elseif (is_string($execution)) {
            $processEngineConfiguration = Context::getProcessEngineConfiguration();
            $processApplicationManager = $processEngineConfiguration->getProcessApplicationManager();
            $processApplicationForDeployment = $processApplicationManager->getProcessApplicationForDeployment($execution);
            return $processApplicationForDeployment;
        }
        return null;
    }

    public static function areProcessApplicationsRegistered(): bool
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $processApplicationManager = $processEngineConfiguration->getProcessApplicationManager();
        return $processApplicationManager->hasRegistrations();
    }

    public static function requiresContextSwitch(ProcessApplicationReferenceInterface $processApplicationReference): bool
    {
        $currentProcessApplication = Context::getCurrentProcessApplication();

        if ($processApplicationReference == null) {
            return false;
        }

        if ($currentProcessApplication == null) {
            return true;
        }
        //@TODO
        return false;
    }

    public static function doContextSwitch(RunnableInterface $runnable, ProcessDefinitionEntity $contextDefinition): void
    {
        $processApplication = $this->getTargetProcessApplication($contextDefinition);
        if ($this->requiresContextSwitch($processApplication)) {
            Context::executeWithinProcessApplication(function () use ($runnable) {
                $runnable->run();
                return null;
            }, $processApplication);
        } else {
            $runnable->run();
        }
    }
}
