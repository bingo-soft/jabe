<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\JobExecutor\{
    JobHandlerInterface,
    JobHandlerConfigurationInterface,
    JobDefinitionSuspensionStateConfiguration
};
use Jabe\Impl\Management\{
    UpdateJobDefinitionSuspensionStateBuilderImpl,
    UpdateJobSuspensionStateBuilderImpl
};
use Jabe\Impl\Persistence\Entity\{
    JobDefinitionEntity,
    JobDefinitionManager,
    JobManager,
    PropertyChange,
    SuspensionState
};

abstract class AbstractSetJobDefinitionStateCmd extends AbstractSetStateCmd
{
    protected $jobDefinitionId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $executionDate;

    protected $processDefinitionTenantId;
    protected bool $isProcessDefinitionTenantIdSet = false;

    public function __construct(UpdateJobDefinitionSuspensionStateBuilderImpl $builder)
    {
        parent::__construct(
            $builder->isIncludeJobs(),
            $builder->getExecutionDate()
        );

        $this->jobDefinitionId = $builder->getJobDefinitionId();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->processDefinitionKey = $builder->getProcessDefinitionKey();

        $this->isProcessDefinitionTenantIdSet = $builder->isProcessDefinitionTenantIdSet();
        $this->processDefinitionTenantId = $builder->getProcessDefinitionTenantId();
    }

    protected function checkParameters(CommandContext $commandContext): void
    {
        if ($this->jobDefinitionId === null && $this->processDefinitionId === null && $this->processDefinitionKey === null) {
            throw new ProcessEngineException("Job definition id, process definition id nor process definition key cannot be null");
        }
    }

    protected function checkAuthorization(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            if ($this->jobDefinitionId !== null) {
                $jobDefinitionManager = $commandContext->getJobDefinitionManager();
                $jobDefinition = $jobDefinitionManager->findById($this->jobDefinitionId);

                if ($jobDefinition !== null && $jobDefinition->getProcessDefinitionKey() !== null) {
                    $processDefinitionKey = $jobDefinition->getProcessDefinitionKey();
                    $checker->checkUpdateProcessDefinitionByKey($processDefinitionKey);

                    if ($this->includeSubResources) {
                        $checker->checkUpdateProcessInstanceByProcessDefinitionKey($processDefinitionKey);
                    }
                }
            } elseif ($this->processDefinitionId !== null) {
                $checker->checkUpdateProcessDefinitionById($this->processDefinitionId);
                if ($this->includeSubResources) {
                    $checker->checkUpdateProcessInstanceByProcessDefinitionId($this->processDefinitionId);
                }
            } elseif ($this->processDefinitionKey !== null) {
                $checker->checkUpdateProcessDefinitionByKey($this->processDefinitionKey);
                if ($this->includeSubResources) {
                    $checker->checkUpdateProcessInstanceByProcessDefinitionKey($this->processDefinitionKey);
                }
            }
        }
    }

    protected function updateSuspensionState(CommandContext $commandContext, SuspensionState $suspensionState): void
    {
        $jobDefinitionManager = $commandContext->getJobDefinitionManager();
        $jobManager = $commandContext->getJobManager();
        if ($this->jobDefinitionId !== null) {
            $jobDefinitionManager->updateJobDefinitionSuspensionStateById($this->jobDefinitionId, $suspensionState);
        } elseif ($this->processDefinitionId !== null) {
            $jobDefinitionManager->updateJobDefinitionSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
            $jobManager->updateStartTimerJobSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
        } elseif ($this->processDefinitionKey !== null) {
            if (!$this->isProcessDefinitionTenantIdSet) {
                $jobDefinitionManager->updateJobDefinitionSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
                $jobManager->updateStartTimerJobSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
            } else {
                $jobDefinitionManager->updateJobDefinitionSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
                $jobManager->updateStartTimerJobSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
            }
        }
    }

    protected function getJobHandlerConfiguration(): ?JobHandlerConfigurationInterface
    {
        if ($this->jobDefinitionId !== null) {
            return JobDefinitionSuspensionStateConfiguration::byJobDefinitionId($this->jobDefinitionId, $this->isIncludeSubResources());
        } elseif ($this->processDefinitionId !== null) {
            return JobDefinitionSuspensionStateConfiguration::byProcessDefinitionId($this->processDefinitionId, $this->isIncludeSubResources());
        } else {
            if (!$this->isProcessDefinitionTenantIdSet) {
                return JobDefinitionSuspensionStateConfiguration::byProcessDefinitionKey($this->processDefinitionKey, $this->isIncludeSubResources());
            } else {
                return JobDefinitionSuspensionStateConfiguration::byProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $this->isIncludeSubResources());
            }
        }
    }

    protected function logUserOperation(CommandContext $commandContext): void
    {
        $propertyChange = new PropertyChange(self::SUSPENSION_STATE_PROPERTY, null, $this->getNewSuspensionState()->getName());
        $commandContext->getOperationLogManager()->logJobDefinitionOperation(
            $this->getLogEntryOperation(),
            $this->jobDefinitionId,
            $this->processDefinitionId,
            $this->processDefinitionKey,
            $propertyChange
        );
    }

    protected function createJobCommandBuilder(): UpdateJobSuspensionStateBuilderImpl
    {
        $builder = new UpdateJobSuspensionStateBuilderImpl();
        if ($this->jobDefinitionId !== null) {
            $builder->byJobDefinitionId($this->jobDefinitionId);
        } elseif ($this->processDefinitionId !== null) {
            $builder->byProcessDefinitionId($this->processDefinitionId);
        } elseif ($this->processDefinitionKey !== null) {
            $builder->byProcessDefinitionKey($this->processDefinitionKey);
            if ($this->isProcessDefinitionTenantIdSet && $this->processDefinitionTenantId !== null) {
                $builder->processDefinitionTenantId($this->processDefinitionTenantId);
            } elseif ($this->isProcessDefinitionTenantIdSet) {
                $builder->processDefinitionWithoutTenantId();
            }
        }
        return $builder;
    }

    /**
     * Subclasses should return the type of the JobHandler here. it will be used when
     * the user provides an execution date on which the actual state change will happen.
     */
    //abstract protected function getDelayedExecutionJobHandlerType(): ?string;

    protected function getNextCommand($jobCommandBuilder = null)
    {
        if ($jobCommandBuilder === null) {
            $jobCommandBuilder = $this->createJobCommandBuilder();
            return $this->getNextCommand($jobCommandBuilder);
        }
        return null;
    }

    protected function getDeploymentId(CommandContext $commandContext): ?string
    {
        if ($this->jobDefinitionId !== null) {
            return $this->getDeploymentIdByJobDefinition($commandContext, $this->jobDefinitionId);
        } elseif ($this->processDefinitionId !== null) {
            return $this->getDeploymentIdByProcessDefinition($commandContext, $this->processDefinitionId);
        } elseif ($this->processDefinitionKey !== null) {
            return $this->getDeploymentIdByProcessDefinitionKey($commandContext, $this->processDefinitionKey, $this->isProcessDefinitionTenantIdSet, $this->processDefinitionTenantId);
        }
        return null;
    }
}
