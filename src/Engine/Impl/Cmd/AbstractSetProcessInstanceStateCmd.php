<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Delegate\DelegateExecutionInterface;
use BpmPlatform\Engine\History\HistoricProcessInstanceInterface;
use BpmPlatform\Engine\Impl\ProcessInstanceQueryImpl;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricProcessInstanceEventEntity,
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;
use BpmPlatform\Engine\Runtime\ProcessInstanceInterface;

abstract class AbstractSetProcessInstanceStateCmd extends AbstractSetStateCmd
{
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;

    protected $processDefinitionTenantId;
    protected $isProcessDefinitionTenantIdSet = false;

    public function __construct(UpdateProcessInstanceSuspensionStateBuilderImpl $builder)
    {
        parent::__construct(true, null);
        $this->processInstanceId = $builder->getProcessInstanceId();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->processDefinitionKey = $builder->getProcessDefinitionKey();
        $this->processDefinitionTenantId = $builder->getProcessDefinitionTenantId();
        $this->isProcessDefinitionTenantIdSet = $builder->isProcessDefinitionTenantIdSet();
    }

    protected function checkParameters(CommandContext $commandContext): void
    {
        if ($this->processInstanceId == null && $this->processDefinitionId == null && $this->processDefinitionKey == null) {
            throw new ProcessEngineException("ProcessInstanceId, ProcessDefinitionId nor ProcessDefinitionKey cannot be null.");
        }
    }

    protected function checkAuthorization(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            if ($this->processInstanceId != null) {
                $checker->checkUpdateProcessInstanceSuspensionStateById($this->processInstanceId);
            } elseif ($this->processDefinitionId != null) {
                $checker->checkUpdateProcessInstanceSuspensionStateByProcessDefinitionId($this->processDefinitionId);
            } elseif ($this->processDefinitionKey != null) {
                $checker->checkUpdateProcessInstanceSuspensionStateByProcessDefinitionKey($this->processDefinitionKey);
            }
        }
    }

    protected function updateSuspensionState(CommandContext $commandContext, SuspensionState $suspensionState): void
    {
        $executionManager = $commandContext->getExecutionManager();
        $taskManager = $commandContext->getTaskManager();
        $externalTaskManager = $commandContext->getExternalTaskManager();

        if ($this->processInstanceId != null) {
            $executionManager->updateExecutionSuspensionStateByProcessInstanceId($this->processInstanceId, $suspensionState);
            $taskManager->updateTaskSuspensionStateByProcessInstanceId($this->processInstanceId, $suspensionState);
            $externalTaskManager->updateExternalTaskSuspensionStateByProcessInstanceId($this->processInstanceId, $suspensionState);
        } elseif ($this->processDefinitionId != null) {
            $executionManager->updateExecutionSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
            $taskManager->updateTaskSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
            $externalTaskManager->updateExternalTaskSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
        } elseif ($this->isProcessDefinitionTenantIdSet) {
            $executionManager->updateExecutionSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
            $taskManager->updateTaskSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
            $externalTaskManager->updateExternalTaskSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
        } else {
            $executionManager->updateExecutionSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
            $taskManager->updateTaskSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
            $externalTaskManager->updateExternalTaskSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
        }
    }

    protected function triggerHistoryEvent(CommandContext $commandContext): void
    {
        $historyLevel = $commandContext->getProcessEngineConfiguration()->getHistoryLevel();
        $updatedProcessInstances = $this->obtainProcessInstances($commandContext);
        //suspension state is not updated synchronously
        if ($this->getNewSuspensionState() != null && !empty($updatedProcessInstances)) {
            foreach ($updatedProcessInstances as $processInstance) {
                if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceUpdate(), $processInstance)) {
                    $scope = $this;
                    HistoryEventProcessor::processHistoryEvents(new class ($scope, $processInstance) extends HistoryEventCreator {
                        private $scope;
                        private $processInstance;

                        public function __construct($scope, $processInstance)
                        {
                            $this->scope = $scope;
                            $this->processInstance = $processInstance;
                        }

                        public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                        {
                            $processInstanceUpdateEvt = $producer->createProcessInstanceUpdateEvt($this->processInstance);
                            if (SuspensionState::suspended()->getStateCode() == $this->scope->getNewSuspensionState()->getStateCode()) {
                                $processInstanceUpdateEvt->setState(HistoricProcessInstanceInterface::STATE_SUSPENDED);
                            } else {
                                $processInstanceUpdateEvt->setState(HistoricProcessInstanceInterface::STATE_ACTIVE);
                            }
                            return $processInstanceUpdateEvt;
                        }
                    });
                }
            }
        }
    }

    protected function obtainProcessInstances(CommandContext $commandContext): array
    {
        $query = new ProcessInstanceQueryImpl();
        if ($this->processInstanceId != null) {
            $query->processInstanceId($this->processInstanceId);
        } elseif ($this->processDefinitionId != null) {
            $query->processDefinitionId($this->processDefinitionId);
        } elseif ($this->isProcessDefinitionTenantIdSet) {
            $query->processDefinitionKey($this->processDefinitionKey);
            if ($this->processDefinitionTenantId != null) {
                $query->tenantIdIn($this->processDefinitionTenantId);
            } else {
                $query->withoutTenantId();
            }
        } else {
            $query->processDefinitionKey($this->processDefinitionKey);
        }
        $result = $commandContext->getExecutionManager()->findProcessInstancesByQueryCriteria($query, null);
        return $result;
    }

    protected function logUserOperation(CommandContext $commandContext): void
    {
        $propertyChange = new PropertyChange(self::SUSPENSION_STATE_PROPERTY, null, $this->getNewSuspensionState()->getName());
        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                $this->getLogEntryOperation(),
                $this->processInstanceId,
                $this->processDefinitionId,
                $this->processDefinitionKey,
                [$propertyChange]
            );
    }

    protected function createJobCommandBuilder(): UpdateJobSuspensionStateBuilderImpl
    {
        $builder = new UpdateJobSuspensionStateBuilderImpl();

        if ($this->processInstanceId != null) {
            $builder->byProcessInstanceId($this->processInstanceId);
        } elseif ($this->processDefinitionId != null) {
            $builder->byProcessDefinitionId($this->processDefinitionId);
        } elseif ($this->processDefinitionKey != null) {
            $builder->byProcessDefinitionKey($this->processDefinitionKey);

            if ($this->isProcessDefinitionTenantIdSet && $this->processDefinitionTenantId != null) {
                return $builder->processDefinitionTenantId($this->processDefinitionTenantId);
            } elseif ($this->isProcessDefinitionTenantIdSet) {
                return $builder->processDefinitionWithoutTenantId();
            }
        }
        return $builder;
    }

    protected function getNextCommand($jobCommandBuilder = null)
    {
        if ($jobCommandBuilder == null) {
            $jobCommandBuilder = $this->createJobCommandBuilder();
            return $this->getNextCommand($jobCommandBuilder);
        }
        return null;
    }
}
