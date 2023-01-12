<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Management\UpdateJobSuspensionStateBuilderImpl;
use Jabe\Impl\Persistence\Entity\{
    JobDefinitionEntity,
    JobDefinitionManager,
    JobEntity,
    JobManager,
    PropertyChange,
    SuspensionState
};

abstract class AbstractSetJobStateCmd extends AbstractSetStateCmd
{
    protected $jobId;
    protected $jobDefinitionId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;

    protected $processDefinitionTenantId;
    protected bool $processDefinitionTenantIdSet = false;

    public function __construct(UpdateJobSuspensionStateBuilderImpl $builder)
    {
        parent::__construct(false, null);
        $this->jobId = $builder->getJobId();
        $this->jobDefinitionId = $builder->getJobDefinitionId();
        $this->processInstanceId = $builder->getProcessInstanceId();
        $this->processDefinitionId = $builder->getProcessDefinitionId();
        $this->processDefinitionKey = $builder->getProcessDefinitionKey();

        $this->processDefinitionTenantIdSet = $builder->isProcessDefinitionTenantIdSet();
        $this->processDefinitionTenantId = $builder->getProcessDefinitionTenantId();
    }

    protected function checkParameters(CommandContext $commandContext): void
    {
        if (
            $this->jobId === null
            && $this->jobDefinitionId === null
            && $this->processInstanceId === null
            && $this->rocessDefinitionId === null
            && $this->processDefinitionKey === null
        ) {
            throw new ProcessEngineException("Job id, job definition id, process instance id, process definition id nor process definition key cannot be null");
        }
    }

    protected function checkAuthorization(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            if ($this->jobId !== null) {
                $jobManager = $commandContext->getJobManager();
                $job = $jobManager->findJobById($this->jobId);
                if ($job !== null) {
                    $processInstanceId = $job->getProcessInstanceId();
                    if ($processInstanceId !== null) {
                        $checker->checkUpdateProcessInstanceById($processInstanceId);
                    } else {
                        // start timer job is not assigned to a specific process
                        // instance, that's why we have to check whether there
                        // exists a UPDATE_INSTANCES permission on process definition or
                        // a UPDATE permission on any process instance
                        $processDefinitionKey = $job->getProcessDefinitionKey();
                        if ($processDefinitionKey !== null) {
                            $checker->checkUpdateProcessInstanceByProcessDefinitionKey($processDefinitionKey);
                        }
                    }
                    // if (processInstanceId === null && processDefinitionKey === null):
                    // job is not assigned to any process instance nor process definition
                    // then it is always possible to activate/suspend the corresponding job
                    // -> no authorization check necessary
                }
            } elseif ($this->jobDefinitionId !== null) {
                $jobDefinitionManager = $commandContext->getJobDefinitionManager();
                $jobDefinition = $jobDefinitionManager->findById($this->jobDefinitionId);
                if ($jobDefinition !== null) {
                    $processDefinitionKey = $jobDefinition->getProcessDefinitionKey();
                    $checker->checkUpdateProcessInstanceByProcessDefinitionKey($processDefinitionKey);
                }
            } elseif ($this->processInstanceId !== null) {
                $checker->checkUpdateProcessInstanceById($this->processInstanceId);
            } elseif ($this->processDefinitionId !== null) {
                $checker->checkUpdateProcessInstanceByProcessDefinitionId($this->processDefinitionId);
            } elseif ($this->processDefinitionKey !== null) {
                $checker->checkUpdateProcessInstanceByProcessDefinitionKey($this->processDefinitionKey);
            }
        }
    }

    protected function updateSuspensionState(CommandContext $commandContext, SuspensionState $suspensionState): void
    {
        $jobManager = $commandContext->getJobManager();
        if ($this->jobId !== null) {
            $jobManager->updateJobSuspensionStateById($this->jobId, $suspensionState);
        } elseif ($this->jobDefinitionId !== null) {
            $jobManager->updateJobSuspensionStateByJobDefinitionId($this->jobDefinitionId, $suspensionState);
        } elseif ($this->processInstanceId !== null) {
            $jobManager->updateJobSuspensionStateByProcessInstanceId($this->processInstanceId, $suspensionState);
        } elseif ($this->processDefinitionId !== null) {
            $jobManager->updateJobSuspensionStateByProcessDefinitionId($this->processDefinitionId, $suspensionState);
        } elseif ($this->processDefinitionKey !== null) {
            if (!$this->processDefinitionTenantIdSet) {
                $jobManager->updateJobSuspensionStateByProcessDefinitionKey($this->processDefinitionKey, $suspensionState);
            } else {
                $jobManager->updateJobSuspensionStateByProcessDefinitionKeyAndTenantId($this->processDefinitionKey, $this->processDefinitionTenantId, $suspensionState);
            }
        }
    }

    protected function logUserOperation(CommandContext $commandContext): void
    {
        $propertyChange = new PropertyChange(self::SUSPENSION_STATE_PROPERTY, null, $this->getNewSuspensionState()->getName());
        $commandContext->getOperationLogManager()->logJobOperation(
            $this->getLogEntryOperation(),
            $this->jobId,
            $this->jobDefinitionId,
            $this->processInstanceId,
            $this->processDefinitionId,
            $this->processDefinitionKey,
            $propertyChange
        );
    }
}
