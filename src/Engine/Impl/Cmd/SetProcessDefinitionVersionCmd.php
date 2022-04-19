<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventProcessor,
    HistoryEventCreator,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    IncidentEntity,
    JobDefinitionEntity,
    JobEntity,
    ProcessDefinitionEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SetProcessDefinitionVersionCmd implements CommandInterface, \Serializable
{
    private $processInstanceId;
    private $processDefinitionVersion;

    public function __construct(string $processInstanceId, int $processDefinitionVersion)
    {
        EnsureUtil::ensureNotEmpty("The process instance id is mandatory", "processInstanceId", $processInstanceId);
        EnsureUtil::ensureNotNull("The process definition version is mandatory", "processDefinitionVersion", $processDefinitionVersion);
        EnsureUtil::ensurePositive("The process definition version must be positive", "processDefinitionVersion", $processDefinitionVersion);
        $this->processInstanceId = $processInstanceId;
        $this->processDefinitionVersion = $processDefinitionVersion;
    }

    public function execute(CommandContext $commandContext)
    {
        $configuration = $commandContext->getProcessEngineConfiguration();

        // check that the new process definition is just another version of the same
        // process definition that the process instance is using
        $executionManager = $commandContext->getExecutionManager();
        $processInstance = $executionManager->findExecutionById($this->processInstanceId);
        if ($processInstance == null) {
            throw new ProcessEngineException("No process instance found for id = '" . $this->processInstanceId . "'.");
        } elseif (!$processInstance->isProcessInstanceExecution()) {
            throw new ProcessEngineException(
                "A process instance id is required, but the provided id " .
                "'" . $this->processInstanceId . "' " .
                "points to a child execution of process instance " +
                "'" . $processInstance->getProcessInstanceId() . "'. " .
                "Please invoke the " . get_class($this) . " with a root execution id."
            );
        }
        $currentProcessDefinitionImpl = $processInstance->getProcessDefinition();

        $deploymentCache = $configuration->getDeploymentCache();
        $currentProcessDefinition = null;
        if ($currentProcessDefinitionImpl instanceof ProcessDefinitionEntity) {
            $currentProcessDefinition = $currentProcessDefinitionImpl;
        } else {
            $currentProcessDefinition = $deploymentCache->findDeployedProcessDefinitionById($currentProcessDefinitionImpl->getId());
        }

        $newProcessDefinition = $deploymentCache
            ->findDeployedProcessDefinitionByKeyVersionAndTenantId(
                $currentProcessDefinition->getKey(),
                $this->processDefinitionVersion,
                $currentProcessDefinition->getTenantId()
            );

        $this->validateAndSwitchVersionOfExecution($commandContext, $processInstance, $newProcessDefinition);

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceUpdate(), $processInstance)) {
            HistoryEventProcessor::processHistoryEvents(new class ($processInstance) extends HistoryEventCreator {
                private $processInstance;

                public function __construct($processInstance)
                {
                    $this->processInstance = $processInstance;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createProcessInstanceUpdateEvt($this->processInstance);
                }
            });
        }

        // switch all sub-executions of the process instance to the new process definition version
        $childExecutions = $executionManager
            ->findExecutionsByProcessInstanceId($this->processInstanceId);
        foreach ($childExecutions as $executionEntity) {
            $this->validateAndSwitchVersionOfExecution($commandContext, $executionEntity, $newProcessDefinition);
        }

        // switch all jobs to the new process definition version
        $jobs = $commandContext->getJobManager()->findJobsByProcessInstanceId($this->processInstanceId);
        $currentJobDefinitions =
            $commandContext->getJobDefinitionManager()->findByProcessDefinitionId($currentProcessDefinition->getId());
        $newVersionJobDefinitions =
            $commandContext->getJobDefinitionManager()->findByProcessDefinitionId($newProcessDefinition->getId());

        $jobDefinitionMapping = $this->getJobDefinitionMapping($currentJobDefinitions, $newVersionJobDefinitions);
        foreach ($jobs as $jobEntity) {
            $this->switchVersionOfJob($jobEntity, $newProcessDefinition, $jobDefinitionMapping);
        }

        // switch all incidents to the new process definition version
        $incidents = $commandContext->getIncidentManager()->findIncidentsByProcessInstance($this->processInstanceId);
        foreach ($incidents as $incidentEntity) {
            $this->switchVersionOfIncident($commandContext, $incidentEntity, $newProcessDefinition);
        }

        // add an entry to the op log
        $change = new PropertyChange("processDefinitionVersion", $currentProcessDefinition->getVersion(), $this->processDefinitionVersion);
        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_MODIFY_PROCESS_INSTANCE,
            $this->processInstanceId,
            null,
            null,
            [$change]
        );

        return null;
    }

    protected function getJobDefinitionMapping(array $currentJobDefinitions, array $newVersionJobDefinitions): array
    {
        $mapping = [];

        foreach ($currentJobDefinitions as $currentJobDefinition) {
            foreach ($newVersionJobDefinitions as $newJobDefinition) {
                if ($this->jobDefinitionsMatch($currentJobDefinition, $newJobDefinition)) {
                    $mapping[$currentJobDefinition->getId()] = $newJobDefinition->getId();
                    break;
                }
            }
        }

        return $mapping;
    }

    protected function jobDefinitionsMatch(JobDefinitionEntity $currentJobDefinition, JobDefinitionEntity $newJobDefinition): bool
    {
        $activitiesMatch = $currentJobDefinition->getActivityId() == $newJobDefinition->getActivityId();

        $typesMatch =
            ($currentJobDefinition->getJobType() == null && $newJobDefinition->getJobType() == null)
                ||
            ($currentJobDefinition->getJobType() != null
                && $currentJobDefinition->getJobType() == $newJobDefinition->getJobType());

        $configurationsMatch =
            ($currentJobDefinition->getJobConfiguration() == null && $newJobDefinition->getJobConfiguration() == null)
                ||
            ($currentJobDefinition->getJobConfiguration() != null
                && $currentJobDefinition->getJobConfiguration() == $newJobDefinition->getJobConfiguration());

        return $activitiesMatch && $typesMatch && $configurationsMatch;
    }

    protected function switchVersionOfJob(JobEntity $jobEntity, ProcessDefinitionEntity $newProcessDefinition, array $jobDefinitionMapping): void
    {
        $jobEntity->setProcessDefinitionId($newProcessDefinition->getId());
        $jobEntity->setDeploymentId($newProcessDefinition->getDeploymentId());

        $newJobDefinitionId = $jobDefinitionMapping[$jobEntity->getJobDefinitionId()];
        $jobEntity->setJobDefinitionId($newJobDefinitionId);
    }

    protected function switchVersionOfIncident(CommandContext $commandContext, IncidentEntity $incidentEntity, ProcessDefinitionEntity $newProcessDefinition): void
    {
        $incidentEntity->setProcessDefinitionId($newProcessDefinition->getId());
    }

    protected function validateAndSwitchVersionOfExecution(CommandContext $commandContext, ExecutionEntity $execution, ProcessDefinitionEntity $newProcessDefinition): void
    {
        // check that the new process definition version contains the current activity
        if ($execution->getActivity() != null) {
            $activityId = $execution->getActivity()->getId();
            $newActivity = $newProcessDefinition->findActivity($activityId);

            if ($newActivity == null) {
                throw new ProcessEngineException(
                    "The new process definition " .
                    "(key = '" . $newProcessDefinition->getKey() . "') " .
                    "does not contain the current activity " .
                    "(id = '" . $activityId . "') " .
                    "of the process instance " .
                    "(id = '" . $this->processInstanceId . "')."
                );
            }
            // clear cached activity so that outgoing transitions are refreshed
            $execution->setActivity($newActivity);
        }

        // switch the process instance to the new process definition version
        $execution->setProcessDefinition($newProcessDefinition);

        // and change possible existing tasks (as the process definition id is stored there too)
        $tasks = $commandContext->getTaskManager()->findTasksByExecutionId($execution->getId());
        foreach ($tasks as $taskEntity) {
            $taskEntity->setProcessDefinitionId($newProcessDefinition->getId());
        }
    }
}
