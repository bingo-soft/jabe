<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\{
    DefaultPriorityProvider,
    ProcessEngineLogger
};
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobDefinitionEntity
};
use BpmPlatform\Engine\Impl\Pvm\Process\ProcessDefinitionImpl;

class DefaultJobPriorityProvider extends DefaultPriorityProvider
{
    //private final static JobExecutorLogger LOG = ProcessEngineLogger.JOB_EXECUTOR_LOGGER;

    protected function getSpecificPriority(ExecutionEntity $execution, JobDeclaration $param, string $jobDefinitionId): int
    {
        $specificPriority = null;
        $jobDefinition = $this->getJobDefinitionFor($jobDefinitionId);
        if ($jobDefinition != null) {
            $specificPriority = $jobDefinition->getOverridingJobPriority();
        }

        if ($specificPriority == null) {
            $priorityProvider = $param->getJobPriorityProvider();
            if ($priorityProvider != null) {
                $specificPriority = $this->evaluateValueProvider($priorityProvider, $execution, $this->describeContext($param, $execution));
            }
        }
        return $specificPriority;
    }

    protected function getProcessDefinitionPriority(ExecutionEntity $execution, JobDeclaration $jobDeclaration): int
    {
        $processDefinition = $jobDeclaration->getProcessDefinition();
        return $this->getProcessDefinedPriority($processDefinition, BpmnParse::PROPERTYNAME_JOB_PRIORITY, $execution, $this->describeContext($jobDeclaration, $execution));
    }

    protected function getJobDefinitionFor(?string $jobDefinitionId): ?JobDefinitionEntity
    {
        if ($jobDefinitionId != null) {
            return Context::getCommandContext()
            ->getJobDefinitionManager()
            ->findById($jobDefinitionId);
        } else {
            return null;
        }
    }

    protected function getActivityPriority(ExecutionEntity $execution, ?JobDeclaration $jobDeclaration): int
    {
        if ($jobDeclaration != null) {
            $priorityProvider = $jobDeclaration->getJobPriorityProvider();
            if ($priorityProvider != null) {
                return $this->evaluateValueProvider($priorityProvider, $execution, $this->describeContext($jobDeclaration, $execution));
            }
        }
        return null;
    }

    protected function logNotDeterminingPriority(ExecutionEntity $execution, $value, ProcessEngineException $e): void
    {
        //LOG.couldNotDeterminePriority(execution, value, e);
    }

    protected function describeContext(JobDeclaration $jobDeclaration, ExecutionEntity $executionEntity): string
    {
        return "Job " . $jobDeclaration->getActivityId()
            . "/" . $jobDeclaration->getJobHandlerType() . " instantiated "
            . "in context of " . $executionEntity;
    }
}
