<?php

namespace Jabe\Impl\ExternalTask;

use Jabe\ProcessEngineException;
use Jabe\Impl\{
    DefaultPriorityProvider,
    ProcessEngineLogger
};
use Jabe\Impl\Bpmn\Behavior\ExternalTaskActivityBehavior;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;

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
        if ($priorityProvider !== null) {
            return $this->evaluateValueProvider($priorityProvider, $execution, "");
        }
        return null;
    }

    protected function getProcessDefinitionPriority(ExecutionEntity $execution, ExternalTaskActivityBehavior $param): int
    {
        return $this->getProcessDefinedPriority($execution->getProcessDefinition(), BpmnParse::PROPERTYNAME_TASK_PRIORITY, $execution, "");
    }
}
