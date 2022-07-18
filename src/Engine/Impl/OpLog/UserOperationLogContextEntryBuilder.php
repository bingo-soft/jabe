<?php

namespace Jabe\Engine\Impl\OpLog;

use Jabe\Engine\History\{
    HistoricTaskInstanceInterface,
    UserOperationLogEntryInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\History\Event\HistoryEvent;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity,
    HistoricVariableInstanceEntity,
    JobDefinitionEntity,
    JobEntity,
    ProcessDefinitionEntity,
    PropertyChange,
    TaskEntity
};
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntity;

class UserOperationLogContextEntryBuilder
{
    protected $entry;

    public static function entry(string $operationType, string $entityType): UserOperationLogContextEntryBuilder
    {
        $builder = new UserOperationLogContextEntryBuilder();
        $builder->entry = new UserOperationLogContextEntry($operationType, $entityType);
        return $builder;
    }

    public function inContextOf(/*JobEntity|JobDefinitionEntity*/$data, $arg2 = null, $arg3 = null): UserOperationLogContextEntryBuilder
    {
        if ($data instanceof JobEntity) {
            $job = $data;
            $this->entry->setJobDefinitionId($job->getJobDefinitionId());
            $this->entry->setProcessInstanceId($job->getProcessInstanceId());
            $this->entry->setProcessDefinitionId($job->getProcessDefinitionId());
            $this->entry->setProcessDefinitionKey($job->getProcessDefinitionKey());
            $this->entry->setDeploymentId($job->getDeploymentId());

            $execution = $job->getExecution();
            if ($execution !== null) {
                $this->entry->setRootProcessInstanceId($execution->getRootProcessInstanceId());
            }

            return $this;
        } elseif ($data instanceof JobDefinitionEntity) {
            $jobDefinition = $data;
            $this->entry->setJobDefinitionId($jobDefinition->getId());
            $this->entry->setProcessDefinitionId($jobDefinition->getProcessDefinitionId());
            $this->entry->setProcessDefinitionKey($jobDefinition->getProcessDefinitionKey());

            if ($jobDefinition->getProcessDefinitionId() !== null) {
                $processDefinition = Context::getProcessEngineConfiguration()
                    ->getDeploymentCache()
                    ->findDeployedProcessDefinitionById($jobDefinition->getProcessDefinitionId());
                $this->entry->setDeploymentId($processDefinition->getDeploymentId());
            }
            return $this;
        } elseif ($data instanceof ExecutionEntity) {
            if (is_array($arg2)) {
                $processInstance = $data;
                $propertyChanges = $arg2;
                if (empty($propertyChanges)) {
                    if (UserOperationLogEntryInterface::OPERATION_TYPE_CREATE == $this->entry->getOperationType()) {
                        $propertyChanges = PropertyChange::emptyChange();
                    }
                }
                $this->entry->setPropertyChanges($propertyChanges);
                $this->entry->setRootProcessInstanceId($processInstance->getRootProcessInstanceId());
                $this->entry->setProcessInstanceId($processInstance->getProcessInstanceId());
                $this->entry->setProcessDefinitionId($processInstance->getProcessDefinitionId());
                $this->entry->setExecutionId($processInstance->getId());
                //$this->entry->setCaseInstanceId($processInstance->getCaseInstanceId());

                $definition = $processInstance->getProcessDefinition();
                if ($definition != null) {
                    $this->entry->setProcessDefinitionKey($definition->getKey());
                    $this->entry->setDeploymentId($definition->getDeploymentId());
                }

                return $this;
            } else {
                $execution = $data;
                $this->entry->setProcessInstanceId($execution->getProcessInstanceId());
                $this->entry->setRootProcessInstanceId($execution->getRootProcessInstanceId());
                $this->entry->setProcessDefinitionId($execution->getProcessDefinitionId());

                $processDefinition = $execution->getProcessDefinition();
                $this->entry->setProcessDefinitionKey($processDefinition->getKey());
                $this->entry->setDeploymentId($processDefinition->getDeploymentId());

                return $this;
            }
        } elseif ($data instanceof ProcessDefinitionEntity) {
            $processDefinition = $data;
            $this->entry->setProcessDefinitionId($processDefinition->getId());
            $this->entry->setProcessDefinitionKey($processDefinition->getKey());
            $this->entry->setDeploymentId($processDefinition->getDeploymentId());
            return $this;
        } elseif ($data instanceof TaskEntity) {
            $task = $data;
            $propertyChanges = $arg2;

            if (empty($propertyChanges)) {
                if (UserOperationLogEntryInterface::OPERATION_TYPE_CREATE == $this->entry->getOperationType()) {
                    $propertyChanges = PropertyChange::emptyChange();
                }
            }
            $this->entry->setPropertyChanges($propertyChanges);

            $definition = $task->getProcessDefinition();
            if ($definition != null) {
                $this->entry->setProcessDefinitionKey($definition->getKey());
                $this->entry->setDeploymentId($definition->getDeploymentId());
            }/* elseif (task.getCaseDefinitionId() != null) {
                $this->entry->setDeploymentId(task.getCaseDefinition().getDeploymentId());
            }*/

            $this->entry->setProcessDefinitionId($task->getProcessDefinitionId());
            $this->entry->setProcessInstanceId($task->getProcessInstanceId());
            $this->entry->setExecutionId($task->getExecutionId());
            /*$this->entry->setCaseDefinitionId(task.getCaseDefinitionId());
            $this->entry->setCaseInstanceId(task.getCaseInstanceId());
            $this->entry->setCaseExecutionId(task.getCaseExecutionId());*/
            $this->entry->setTaskId($task->getId());

            $execution = $task->getExecution();
            if ($execution != null) {
                $this->entry->setRootProcessInstanceId($execution->getRootProcessInstanceId());
            }

            return $this;
        } elseif ($data instanceof HistoricTaskInstanceInterface) {
            $task = $data;
            $propertyChanges = $arg2;
            if (empty($propertyChanges)) {
                if (UserOperationLogEntryInterface::OPERATION_TYPE_CREATE == $this->entry->getOperationType()) {
                    $propertyChanges = PropertyChange::emptyChange();
                }
            }
            $this->entry->setPropertyChanges($propertyChanges);
            $this->entry->setProcessDefinitionKey($task->getProcessDefinitionKey());
            $this->entry->setProcessDefinitionId($task->getProcessDefinitionId());
            $this->entry->setProcessInstanceId($task->getProcessInstanceId());
            $this->entry->setExecutionId($task->getExecutionId());
            /*$this->entry->setCaseDefinitionId(task.getCaseDefinitionId());
            $this->entry->setCaseInstanceId(task.getCaseInstanceId());
            $this->entry->setCaseExecutionId(task.getCaseExecutionId());*/
            $this->entry->setTaskId($task->getId());
            $this->entry->setRootProcessInstanceId($task->getRootProcessInstanceId());

            return $this;
        } elseif ($data instanceof HistoryEvent) {
            $historyEvent = $data;
            $definition = $arg2;
            $propertyChanges = $arg3;
            if (empty($propertyChanges)) {
                if (UserOperationLogEntryInterface::OPERATION_TYPE_CREATE == $this->entry->getOperationType()) {
                    $propertyChanges = PropertyChange::emptyChange();
                }
            }
            $this->entry->setPropertyChanges($propertyChanges);
            $this->entry->setRootProcessInstanceId($historyEvent->getRootProcessInstanceId());
            $this->entry->setProcessDefinitionId($historyEvent->getProcessDefinitionId());
            $this->entry->setProcessInstanceId($historyEvent->getProcessInstanceId());
            $this->entry->setExecutionId($historyEvent->getExecutionId());
            /*$this->entry->setCaseDefinitionId(historyEvent.getCaseDefinitionId());
            $this->entry->setCaseInstanceId(historyEvent.getCaseInstanceId());
            $this->entry->setCaseExecutionId(historyEvent.getCaseExecutionId());*/

            if ($definition != null) {
                if ($definition instanceof ProcessDefinitionEntity) {
                    $this->entry->setProcessDefinitionKey($definition->getKey());
                }
                $this->entry->setDeploymentId($definition->getDeploymentId());
            }

            return $this;
        } elseif ($data instanceof HistoricVariableInstanceEntity) {
            $variable = $data;
            $definition = $arg2;
            $propertyChanges = $arg3;
            if (empty($propertyChanges)) {
                if (UserOperationLogEntryInterface::OPERATION_TYPE_CREATE == $this->entry->getOperationType()) {
                    $propertyChanges = PropertyChange::emptyChange();
                }
            }
            $this->entry->setPropertyChanges($propertyChanges);
            $this->entry->setRootProcessInstanceId($variable->getRootProcessInstanceId());
            $this->entry->setProcessDefinitionId($variable->getProcessDefinitionId());
            $this->entry->setProcessInstanceId($variable->getProcessInstanceId());
            $this->entry->setExecutionId($variable->getExecutionId());
            /*$this->entry->setCaseDefinitionId(variable.getCaseDefinitionId());
            $this->entry->setCaseInstanceId(variable.getCaseInstanceId());
            $this->entry->setCaseExecutionId(variable.getCaseExecutionId());*/
            $this->entry->setTaskId($variable->getTaskId());

            if ($definition != null) {
                if ($definition instanceof ProcessDefinitionEntity) {
                    $this->entry->setProcessDefinitionKey($definition->getKey());
                }
                $this->entry->setDeploymentId($definition->getDeploymentId());
            }

            return $this;
        } elseif ($data instanceof ExternalTaskEntity) {
            $task = $data;
            $execution = $arg2;
            $definition = $arg3;
            if ($execution !== null) {
                $this->inContextOf($execution);
            } elseif ($definition !== null) {
                $this->inContextOf($definition);
            }
            $this->entry->setExternalTaskId($task->getId());
            return $this;
        }
    }

    public function propertyChanges($propertyChanges): UserOperationLogContextEntryBuilder
    {
        if (is_array($propertyChanges)) {
            $this->entry->setPropertyChanges($propertyChanges);
            return $this;
        } else {
            $propertyChanges = [ $propertyChanges ];
            $this->entry->setPropertyChanges($propertyChanges);
            return $this;
        }
    }

    public function create(): UserOperationLogContextEntry
    {
        return $this->entry;
    }

    public function jobId(string $jobId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setJobId($jobId);
        return $this;
    }

    public function jobDefinitionId(string $jobDefinitionId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setJobDefinitionId($jobDefinitionId);
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setProcessDefinitionId($processDefinitionId);
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): UserOperationLogContextEntryBuilder
    {
        $this->entry->setProcessDefinitionKey($processDefinitionKey);
        return $this;
    }

    public function processInstanceId(string $processInstanceId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setProcessInstanceId($processInstanceId);
        return $this;
    }

    /*public UserOperationLogContextEntryBuilder caseDefinitionId(string $caseDefinitionId) {
        $this->entry->setCaseDefinitionId(caseDefinitionId);
        return $this;
    }*/

    public function deploymentId(string $deploymentId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setDeploymentId($deploymentId);
        return $this;
    }

    public function batchId(string $batchId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setBatchId($batchId);
        return $this;
    }

    public function taskId(string $taskId): UserOperationLogContextEntryBuilder
    {
        $this->entry->setTaskId($taskId);
        return $this;
    }

    /*public UserOperationLogContextEntryBuilder caseInstanceId(string $caseInstanceId) {
        $this->entry->setCaseInstanceId(caseInstanceId);
        return $this;
    }*/

    public function category(string $category): UserOperationLogContextEntryBuilder
    {
        $this->entry->setCategory($category);
        return $this;
    }

    public function annotation(string $annotation): UserOperationLogContextEntryBuilder
    {
        $this->entry->setAnnotation($annotation);
        return $this;
    }
}
