<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\JobExecutor\{
    JobHandlerInterface,
    JobHandlerConfigurationInterface,
    ProcessDefinitionSuspensionStateConfiguration
};
use Jabe\Engine\Impl\Management\UpdateJobDefinitionSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionManager,
    PropertyChange,
    SuspensionState
};
use Jabe\Engine\Impl\Repository\UpdateProcessDefinitionSuspensionStateBuilderImpl;
use Jabe\Engine\Impl\Runtime\UpdateProcessInstanceSuspensionStateBuilderImpl;

abstract class AbstractSetProcessDefinitionStateCmd extends AbstractSetStateCmd
{
    public const INCLUDE_PROCESS_INSTANCES_PROPERTY = "includeProcessInstances";
    protected $processDefinitionId;
    protected $processDefinitionKey;

    protected $tenantId;
    protected $isTenantIdSet = false;

    public function __construct(UpdateProcessDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct(
            $builder->isIncludeProcessInstances(),
            $builder->getExecutionDate()
        );

        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->processDefinitionKey = $builder->getProcessDefinitionKey();

        $this->isTenantIdSet = $builder->isTenantIdSet();
        $this->tenantId = $builder->getProcessDefinitionTenantId();
    }

    protected function checkParameters(CommandContext $commandContext): void
    {
        // Validation of input parameters
        if ($this->processDefinitionId === null && $this->processDefinitionKey === null) {
            throw new ProcessEngineException("Process definition id / key cannot be null");
        }
    }

    protected function checkAuthorization(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            if ($this->processDefinitionId !== null) {
                $checker->checkUpdateProcessDefinitionSuspensionStateById($this->processDefinitionId);
                if ($this->includeSubResources) {
                    $checker->checkUpdateProcessInstanceSuspensionStateByProcessDefinitionId($this->processDefinitionId);
                }
            } elseif ($this->processDefinitionKey !== null) {
                $checker->checkUpdateProcessDefinitionSuspensionStateByKey($this->processDefinitionKey);

                if ($this->includeSubResources) {
                    $checker->checkUpdateProcessInstanceSuspensionStateByProcessDefinitionKey($this->processDefinitionKey);
                }
            }
        }
    }

    protected function updateSuspensionState(CommandContext $commandContext, SuspensionState $suspensionState): void
    {
        $processDefinitionManager = $commandContext->getProcessDefinitionManager();

        if ($this->processDefinitionId !== null) {
            $processDefinitionManager->updateProcessDefinitionSuspensionStateById($this->processDefinitionId, $suspensionState);
        } else if ($this->isTenantIdSet) {
            $processDefinitionManager->updateProcessDefinitionSuspensionStateByKeyAndTenantId($this->processDefinitionKey, $this->tenantId, $suspensionState);
        } else {
            $processDefinitionManager->updateProcessDefinitionSuspensionStateByKey($this->processDefinitionKey, $suspensionState);
        }

        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $jobDefinitionSuspensionStateBuilder = $scope->createJobDefinitionCommandBuilder();
            $jobDefinitionCmd = $scope->getSetJobDefinitionStateCmd($jobDefinitionSuspensionStateBuilder);
            $jobDefinitionCmd->disableLogUserOperation();
            $jobDefinitionCmd->execute($commandContext);
            return null;
        });
    }

    protected function createJobDefinitionCommandBuilder(): UpdateJobDefinitionSuspensionStateBuilderImpl
    {
        $jobDefinitionBuilder = new UpdateJobDefinitionSuspensionStateBuilderImpl();
        if ($this->processDefinitionId !== null) {
            $jobDefinitionBuilder->byProcessDefinitionId($this->processDefinitionId);
        } elseif ($this->processDefinitionKey !== null) {
            $jobDefinitionBuilder->byProcessDefinitionKey($this->processDefinitionKey);
            if ($this->isTenantIdSet && $this->tenantId !== null) {
                $jobDefinitionBuilder->processDefinitionTenantId($this->tenantId);
            } elseif ($this->isTenantIdSet) { //@TODO. May be !$this->isTenantIdSet
                $jobDefinitionBuilder->processDefinitionWithoutTenantId();
            }
        }
        return $jobDefinitionBuilder;
    }

    protected function createProcessInstanceCommandBuilder(): UpdateProcessInstanceSuspensionStateBuilderImpl
    {
        $processInstanceBuilder = new UpdateProcessInstanceSuspensionStateBuilderImpl();
        if ($this->processDefinitionId !== null) {
            $processInstanceBuilder->byProcessDefinitionId($this->processDefinitionId);
        } elseif ($this->processDefinitionKey !== null) {
            $processInstanceBuilder->byProcessDefinitionKey($this->processDefinitionKey);
            if ($this->isTenantIdSet && $this->tenantId !== null) {
                $processInstanceBuilder->processDefinitionTenantId($this->tenantId);
            } elseif ($this->isTenantIdSet) { //@TODO. May be !$this->isTenantIdSet
                $processInstanceBuilder->processDefinitionWithoutTenantId();
            }
        }
        return $processInstanceBuilder;
    }

    protected function getJobHandlerConfiguration(): ?JobHandlerConfigurationInterface
    {
        if ($this->processDefinitionId !== null) {
            return ProcessDefinitionSuspensionStateConfiguration::byProcessDefinitionId($this->processDefinitionId, isIncludeSubResources());
        } elseif ($this->isTenantIdSet) {
            return ProcessDefinitionSuspensionStateConfiguration::byProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->tenantId, $this->isIncludeSubResources());
        } else {
            return ProcessDefinitionSuspensionStateConfiguration::byProcessDefinitionKey($this->processDefinitionKey, $this->isIncludeSubResources());
        }
    }

    protected function logUserOperation(CommandContext $commandContext): void
    {
        $suspensionStateChanged =
            new PropertyChange(self::SUSPENSION_STATE_PROPERTY, null, $this->getNewSuspensionState()->getName());
        $includeProcessInstances =
            new PropertyChange(self::INCLUDE_PROCESS_INSTANCES_PROPERTY, null, $this->isIncludeSubResources());
        $commandContext->getOperationLogManager()
            ->logProcessDefinitionOperation(
                $this->getLogEntryOperation(),
                $this->processDefinitionId,
                $this->processDefinitionKey,
                [$suspensionStateChanged, $includeProcessInstances]
            );
    }
    // ABSTRACT METHODS ////////////////////////////////////////////////////////////////////
    /**
     * Subclasses should return the type of the JobHandler here. it will be used when
     * the user provides an execution date on which the actual state change will happen.
     */
    abstract protected function getDelayedExecutionJobHandlerType(): ?string;

    /**
     * Subclasses should return the type of the AbstractSetJobDefinitionStateCmd here.
     * It will be used to suspend or activate the JobDefinitions.
     * @param jobDefinitionSuspensionStateBuilder
     */
    abstract protected function getSetJobDefinitionStateCmd(UpdateJobDefinitionSuspensionStateBuilderImpl $jobDefinitionSuspensionStateBuilder): AbstractSetJobDefinitionStateCmd;

    protected function getNextCommand($processInstanceCommandBuilder = null)
    {
        if ($processInstanceCommandBuilder === null) {
            $processInstanceCommandBuilder = $this->createProcessInstanceCommandBuilder();
            return $this->getNextCommand($processInstanceCommandBuilder);
        }
        return null;
    }

    protected function getDeploymentId(CommandContext $commandContext): ?string
    {
        if ($this->processDefinitionId !== null) {
            return $this->getDeploymentIdByProcessDefinition($commandContext, $this->processDefinitionId);
        } elseif ($this->processDefinitionKey !== null) {
            return $this->getDeploymentIdByProcessDefinitionKey($commandContext, $this->processDefinitionKey, $this->isTenantIdSet, $this->tenantId);
        }
        return null;
    }
}
