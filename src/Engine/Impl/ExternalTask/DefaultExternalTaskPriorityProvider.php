<?php

namespace BpmPlatform\Engine\Impl\ExternalTask;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\{
    DefaultPriorityProvider,
    ProcessEngineLogger
};
use BpmPlatform\Engine\Impl\Bpmn\Behavior\ExternalTaskActivityBehavior;
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;

class DefaultExternalTaskPriorityProvider extends DefaultPriorityProvider
{
    //public static final ExternalTaskLogger LOG = ProcessEngineLogger.EXTERNAL_TASK_LOGGER;

    protected function logNotDeterminingPriority(ExecutionEntity $execution, $value, ProcessEngineException $e): void
    {
        //LOG.couldNotDeterminePriority(execution, value, e);
    }

    public function getSpecificPriority(ExecutionEntity $execution, ExternalTaskActivityBehavior $param, string $jobDefinitionId): ?int
    {
        $priorityProvider = $param->getPriorityValueProvider();
        if ($priorityProvider != null) {
            return $this->evaluateValueProvider($priorityProvider, $execution, "");
        }
        return null;
    }

    protected function getProcessDefinitionPriority(ExecutionEntity $execution, ExternalTaskActivityBehavior $param): int
    {
        return $this->getProcessDefinedPriority($execution->getProcessDefinition(), BpmnParse::PROPERTYNAME_TASK_PRIORITY, $execution, "");
    }
}
