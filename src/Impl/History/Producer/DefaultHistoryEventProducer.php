<?php

namespace Jabe\Impl\History\Producer;

use Jabe\{
    ProcessEngineException,
    ProcessEngineConfiguration
};
use Jabe\Batch\BatchInterface;
use Jabe\Delegate\{
    DelegateExecutionInterface,
    DelegateTaskInterface,
    VariableScopeInterface
};
use Jabe\ExternalTask\ExternalTaskInterface;
use Jabe\History\{
    ExternalTaskStateImpl,
    ExternalTaskStateInterface,
    HistoricProcessInstanceInterface,
    IncidentStateImpl,
    JobStateImpl
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Batch\BatchEntity;
use Jabe\Impl\Batch\History\HistoricBatchEntity;
use Jabe\Impl\Cfg\{
    ConfigurationLogger,
    IdGenerator
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\History\DefaultHistoryRemovalTimeProvider;
use Jabe\Impl\History\Event\{
    HistoricActivityInstanceEventEntity,
    HistoricExternalTaskLogEntity,
    HistoricFormPropertyEventEntity,
    HistoricIdentityLinkLogEventEntity,
    HistoricIncidentEventEntity,
    HistoricProcessInstanceEventEntity,
    HistoricTaskInstanceEventEntity,
    HistoricVariableUpdateEventEntity,
    HistoryEvent,
    HistoryEventTypeInterface,
    HistoryEventTypes,
    UserOperationLogEntryEventEntity
};
//@TODO
use Jabe\Impl\JobExecutor\HistoryCleanup\HistoryCleanupJobHandler;
use Jabe\Impl\Migration\Instance\MigratingActivityInstance;
use Jabe\Impl\OpLog\{
    UserOperationLogContext,
    UserOperationLogContextEntry
};
use Jabe\Impl\Persistence\Entity\{
    ByteArrayEntity,
    ExecutionEntity,
    ExternalTaskEntity,
    HistoricJobLogEventEntity,
    IncidentEntity,
    JobEntity,
    ProcessDefinitionEntity,
    PropertyChange,
    TaskEntity,
    VariableInstanceEntity
};
use Jabe\Impl\Pvm\PvmScopeInterface;
use Jabe\Impl\Pvm\Runtime\CompensationBehavior;
use Jabe\Impl\Util\{
    ClockUtil,
    ExceptionUtil,
    ParseUtil,
    StringUtil
};
use Jabe\Repository\{
    ProcessDefinitionInterface,
    ResourceTypes
};
use Jabe\Runtime\{
    IncidentInterface,
    JobInterface
};
use Jabe\Task\IdentityLinkInterface;

class DefaultHistoryEventProducer implements HistoryEventProducerInterface
{
    //protected final static ConfigurationLogger LOG = ProcessEngineLogger.CONFIG_LOGGER;

    protected function initActivityInstanceEvent(
        HistoricActivityInstanceEventEntity $evt,
        ExecutionEntity $instance,
        HistoryEventTypeInterface $eventType
    ): void {
        $eventSource = $instance->getActivity();
        if ($eventSource == null) {
            $eventSource = $instance->getEventSource();
        }
        $activityInstanceId = $instance->getActivityInstanceId();

        $parentActivityInstanceId = null;
        $parentExecution = $instance->getParent();

        if ($parentExecution !== null && CompensationBehavior::isCompensationThrowing($parentExecution) && $instance->getActivity() !== null) {
            $parentActivityInstanceId = CompensationBehavior::getParentActivityInstanceId($instance);
        } else {
            $parentActivityInstanceId = $instance->getParentActivityInstanceId();
        }

        $this->initActivityInstanceEventWithIds(
            $evt,
            $instance,
            $eventSource,
            $activityInstanceId,
            $parentActivityInstanceId,
            $eventType
        );
    }

    protected function initActivityInstanceEventWithMigration(
        HistoricActivityInstanceEventEntity $evt,
        MigratingActivityInstance $migratingActivityInstance,
        HistoryEventTypeInterface $eventType
    ): void {
        $eventSource = $migratingActivityInstance->getTargetScope();
        $activityInstanceId = $migratingActivityInstance->getActivityInstanceId();

        $parentInstance = $migratingActivityInstance->getParent();
        $parentActivityInstanceId = null;
        if ($parentInstance !== null) {
            $parentActivityInstanceId = $parentInstance->getActivityInstanceId();
        }

        $execution = $migratingActivityInstance->resolveRepresentativeExecution();

        $this->initActivityInstanceEventWithIds(
            $evt,
            $execution,
            $eventSource,
            $activityInstanceId,
            $parentActivityInstanceId,
            $eventType
        );
    }

    protected function initActivityInstanceEventWithIds(
        HistoricActivityInstanceEventEntity $evt,
        ExecutionEntity $execution,
        PvmScopeInterface $eventSource,
        ?string $activityInstanceId,
        ?string $parentActivityInstanceId,
        HistoryEventTypeInterface $eventType
    ): void {

        $evt->setId($activityInstanceId);
        $evt->setEventType($eventType->getEventName());
        $evt->setActivityInstanceId($activityInstanceId);
        $evt->setParentActivityInstanceId($parentActivityInstanceId);
        $evt->setProcessDefinitionId($execution->getProcessDefinitionId());
        $evt->setProcessInstanceId($execution->getProcessInstanceId());
        $evt->setExecutionId($execution->getId());
        $evt->setTenantId($execution->getTenantId());
        $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($evt);
        }

        $definition = $execution->getProcessDefinition();
        if ($definition !== null) {
            $evt->setProcessDefinitionKey($definition->getKey());
        }

        $evt->setActivityId($eventSource->getId());
        $evt->setActivityName($eventSource->getProperty("name"));
        $evt->setActivityType($eventSource->getProperty("type"));

        // update sub process reference
        $subProcessInstance = $execution->getSubProcessInstance();
        if ($subProcessInstance !== null) {
            $evt->setCalledProcessInstanceId($subProcessInstance->getId());
        }

        // update sub case reference
        /*CaseExecutionEntity subCaseInstance = $execution->getSubCaseInstance();
        if (subCaseInstance !== null) {
            $evt->setCalledCaseInstanceId(subCaseInstance->getId());
        }*/
    }

    protected function initProcessInstanceEvent(
        HistoricProcessInstanceEventEntity $evt,
        ExecutionEntity $execution,
        HistoryEventTypeInterface $eventType
    ): void {
        $processDefinitionId = $execution->getProcessDefinitionId();
        $processInstanceId = $execution->getProcessInstanceId();
        $executionId = $execution->getId();
        // the given execution is the process instance!
        //String caseInstanceId = $execution->getCaseInstanceId();
        $tenantId = $execution->getTenantId();

        $definition = $execution->getProcessDefinition();
        $processDefinitionKey = null;
        if ($definition !== null) {
            $processDefinitionKey = $definition->getKey();
        }

        $evt->setId($processInstanceId);
        $evt->setEventType($eventType->getEventName());
        $evt->setProcessDefinitionKey($processDefinitionKey);
        $evt->setProcessDefinitionId($processDefinitionId);
        $evt->setProcessInstanceId($processInstanceId);
        $evt->setExecutionId($executionId);
        $evt->setBusinessKey($execution->getProcessBusinessKey());
        //$evt->setCaseInstanceId(caseInstanceId);
        $evt->setTenantId($tenantId);
        $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

        /*if ($execution->getSuperCaseExecution() !== null) {
            $evt->setSuperCaseInstanceId($execution->getSuperCaseExecution()->getCaseInstanceId());
        }*/
        if ($execution->getSuperExecution() !== null) {
            $evt->setSuperProcessInstanceId($execution->getSuperExecution()->getProcessInstanceId());
        }
    }

    protected function initTaskInstanceEvent(
        HistoricTaskInstanceEventEntity $evt,
        TaskEntity $taskEntity,
        HistoryEventTypeInterface $eventType
    ): void {
        $processDefinitionKey = null;
        $definition = $taskEntity->getProcessDefinition();
        if ($definition !== null) {
            $processDefinitionKey = $definition->getKey();
        }

        $processDefinitionId = $taskEntity->getProcessDefinitionId();
        $processInstanceId = $taskEntity->getProcessInstanceId();
        $executionId = $taskEntity->getExecutionId();

        /*String caseDefinitionKey = null;
        CaseDefinitionEntity caseDefinition = $taskEntity->getCaseDefinition();
        if (caseDefinition !== null) {
            caseDefinitionKey = caseDefinition->getKey();
        }

        String caseDefinitionId = $taskEntity->getCaseDefinitionId();
        String caseExecutionId = $taskEntity->getCaseExecutionId();
        String caseInstanceId = $taskEntity->getCaseInstanceId();*/
        $tenantId = $taskEntity->getTenantId();

        $evt->setId($taskEntity->getId());
        $evt->setEventType($eventType->getEventName());
        $evt->setTaskId($taskEntity->getId());

        $evt->setProcessDefinitionKey($processDefinitionKey);
        $evt->setProcessDefinitionId($processDefinitionId);
        $evt->setProcessInstanceId($processInstanceId);
        $evt->setExecutionId($executionId);

        /*$evt->setCaseDefinitionKey(caseDefinitionKey);
        $evt->setCaseDefinitionId(caseDefinitionId);
        $evt->setCaseExecutionId(caseExecutionId);
        $evt->setCaseInstanceId(caseInstanceId);*/

        $evt->setAssignee($taskEntity->getAssignee());
        $evt->setDescription($taskEntity->getDescription());
        $evt->setDueDate($taskEntity->getDueDate());
        $evt->setFollowUpDate($taskEntity->getFollowUpDate());
        $evt->setName($taskEntity->getName());
        $evt->setOwner($taskEntity->getOwner());
        $evt->setParentTaskId($taskEntity->getParentTaskId());
        $evt->setPriority($taskEntity->getPriority());
        $evt->setTaskDefinitionKey($taskEntity->getTaskDefinitionKey());
        $evt->setTenantId($tenantId);

        $execution = $taskEntity->getExecution();
        if ($execution !== null) {
            $evt->setActivityInstanceId($execution->getActivityInstanceId());
            $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $this->provideRemovalTime($evt);
            }
        }
    }

    protected function initHistoricVariableUpdateEvt(
        HistoricVariableUpdateEventEntity $evt,
        VariableInstanceEntity $variableInstance,
        HistoryEventTypeInterface $eventType
    ): void {

        // init properties
        $evt->setEventType($eventType->getEventName());
        $evt->setTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $evt->setVariableInstanceId($variableInstance->getId());
        $evt->setProcessInstanceId($variableInstance->getProcessInstanceId());
        $evt->setExecutionId($variableInstance->getExecutionId());
        //$evt->setCaseInstanceId($variableInstance->getCaseInstanceId());
        //$evt->setCaseExecutionId($variableInstance->getCaseExecutionId());
        $evt->setTaskId($variableInstance->getTaskId());
        $evt->setRevision($variableInstance->getRevision());
        $evt->setVariableName($variableInstance->getName());
        $evt->setSerializerName($variableInstance->getSerializerName());
        $evt->setTenantId($variableInstance->getTenantId());
        $evt->setUserOperationId(Context::getCommandContext()->getOperationId());

        $execution = $variableInstance->getExecution();
        if ($execution !== null) {
            $definition = $execution->getProcessDefinition();
            if ($definition !== null) {
                $evt->setProcessDefinitionId($definition->getId());
                $evt->setProcessDefinitionKey($definition->getKey());
            }
            $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $this->provideRemovalTime($evt);
            }
        }

        /*CaseExecutionEntity caseExecution = variableInstance->getCaseExecution();
        if (caseExecution !== null) {
            CaseDefinitionEntity definition = (CaseDefinitionEntity) caseExecution->getCaseDefinition();
            if (definition !== null) {
            $evt->setCaseDefinitionId(definition->getId());
            $evt->setCaseDefinitionKey(definition->getKey());
            }
        }*/

        // copy value
        $evt->setTextValue($variableInstance->getTextValue());
        $evt->setTextValue2($variableInstance->getTextValue2());
        $evt->setDoubleValue($variableInstance->getDoubleValue());
        $evt->setLongValue($variableInstance->getLongValue());
        if ($variableInstance->getByteArrayValueId() !== null) {
            $evt->setByteValue($variableInstance->getByteArrayValue());
        }
    }

    protected function initUserOperationLogEvent(
        UserOperationLogEntryEventEntity $evt,
        UserOperationLogContext $context,
        UserOperationLogContextEntry $contextEntry,
        PropertyChange $propertyChange
    ): void {
        // init properties
        $evt->setDeploymentId($contextEntry->getDeploymentId());
        $evt->setEntityType($contextEntry->getEntityType());
        $evt->setOperationType($contextEntry->getOperationType());
        $evt->setOperationId($context->getOperationId());
        $evt->setUserId($context->getUserId());
        $evt->setProcessDefinitionId($contextEntry->getProcessDefinitionId());
        $evt->setProcessDefinitionKey($contextEntry->getProcessDefinitionKey());
        $evt->setProcessInstanceId($contextEntry->getProcessInstanceId());
        $evt->setExecutionId($contextEntry->getExecutionId());
        //$evt->setCaseDefinitionId($contextEntry->getCaseDefinitionId());
        //$evt->setCaseInstanceId($contextEntry->getCaseInstanceId());
        //$evt->setCaseExecutionId($contextEntry->getCaseExecutionId());
        $evt->setTaskId($contextEntry->getTaskId());
        $evt->setJobId($contextEntry->getJobId());
        $evt->setJobDefinitionId($contextEntry->getJobDefinitionId());
        $evt->setBatchId($contextEntry->getBatchId());
        $evt->setCategory($contextEntry->getCategory());
        $evt->setTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $evt->setRootProcessInstanceId($contextEntry->getRootProcessInstanceId());
        $evt->setExternalTaskId($contextEntry->getExternalTaskId());
        $evt->setAnnotation($contextEntry->getAnnotation());

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($evt);
        }

        // init property value
        $evt->setProperty($propertyChange->getPropertyName());
        $evt->setOrgValue($propertyChange->getOrgValueString());
        $evt->setNewValue($propertyChange->getNewValueString());
    }

    protected function initHistoricIncidentEvent(
        HistoricIncidentEventEntity $evt,
        IncidentInterface $incident,
        HistoryEventTypeInterface $eventType
    ): void {
        // init properties
        $evt->setId($incident->getId());
        $evt->setProcessDefinitionId($incident->getProcessDefinitionId());
        $evt->setProcessInstanceId($incident->getProcessInstanceId());
        $evt->setExecutionId($incident->getExecutionId());
        $evt->setCreateTime($incident->getIncidentTimestamp());
        $evt->setIncidentType($incident->getIncidentType());
        $evt->setActivityId($incident->getActivityId());
        $evt->setCauseIncidentId($incident->getCauseIncidentId());
        $evt->setRootCauseIncidentId($incident->getRootCauseIncidentId());
        $evt->setConfiguration($incident->getConfiguration());
        $evt->setIncidentMessage($incident->getIncidentMessage());
        $evt->setTenantId($incident->getTenantId());
        $evt->setJobDefinitionId($incident->getJobDefinitionId());
        $evt->setHistoryConfiguration($incident->getHistoryConfiguration());
        $evt->setFailedActivityId($incident->getFailedActivityId());
        $evt->setAnnotation($incident->getAnnotation());

        $jobId = $incident->getConfiguration();
        if ($jobId !== null && $this->isHistoryRemovalTimeStrategyStart()) {
            $historicBatch = $this->getHistoricBatchByJobId($jobId);
            if ($historicBatch !== null) {
                $evt->setRemovalTime($historicBatch->getRemovalTime());
            }
        }

        $incidentEntity = $incident;
        $definition = $incidentEntity->getProcessDefinition();
        if ($definition !== null) {
            $evt->setProcessDefinitionKey($definition->getKey());
        }

        $execution = $incidentEntity->getExecution();
        if ($execution !== null) {
            $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $this->provideRemovalTime($evt);
            }
        }

        // init event type
        $evt->setEventType($eventType->getEventName());

        // init state
        $incidentState = IncidentStateImpl::default();
        if (HistoryEventTypes::incidentDelete()->equals($eventType)) {
            $incidentState = IncidentStateImpl::deleted();
        } elseif (HistoryEventTypes::incidentResolve()->equals($eventType)) {
            $incidentState = IncidentStateImpl::resolved();
        }
        $evt->setIncidentState($incidentState->getStateCode());
    }

    protected function createHistoricVariableEvent(
        VariableInstanceEntity $variableInstance,
        VariableScopeInterface $sourceVariableScope,
        HistoryEventTypeInterface $eventType
    ): HistoryEvent {
        $scopeActivityInstanceId = null;
        $sourceActivityInstanceId = null;

        if ($variableInstance->getExecutionId() !== null) {
            $scopeExecution = Context::getCommandContext()
            ->getDbEntityManager()
            ->selectById(ExecutionEntity::class, $variableInstance->getExecutionId());

            if (
                $variableInstance->getTaskId() == null
                && !$variableInstance->isConcurrentLocal()
            ) {
                $scopeActivityInstanceId = $scopeExecution->getParentActivityInstanceId();
            } else {
                $scopeActivityInstanceId = $scopeExecution->getActivityInstanceId();
            }
        } /*elseif ($variableInstance->getCaseExecutionId() !== null) {
            $scopeActivityInstanceId = $variableInstance->getCaseExecutionId();
        }*/

        $sourceExecution = null;
        //CaseExecutionEntity sourceCaseExecution = null;
        if ($sourceVariableScope instanceof ExecutionEntity) {
            $sourceExecution = $sourceVariableScope;
            $sourceActivityInstanceId = $sourceExecution->getActivityInstanceId();
        } elseif ($sourceVariableScope instanceof TaskEntity) {
            $sourceExecution = $sourceVariableScope->getExecution();
            if ($sourceExecution !== null) {
                $sourceActivityInstanceId = $sourceExecution->getActivityInstanceId();
            }/* else {
                sourceCaseExecution = ((TaskEntity) sourceVariableScope)->getCaseExecution();
                if (sourceCaseExecution !== null) {
                    sourceActivityInstanceId = sourceCaseExecution->getId();
                }
            }*/
        } /*elseif (sourceVariableScopeInterface $instanceof CaseExecutionEntity) {
            sourceCaseExecution = (CaseExecutionEntity) sourceVariableScope;
            sourceActivityInstanceId = sourceCaseExecution->getId();
        }*/

        // create event
        $evt = $this->newVariableUpdateEventEntity($sourceExecution);
        // initialize
        $this->initHistoricVariableUpdateEvt($evt, $variableInstance, $eventType);
        // initialize sequence counter
        $this->initSequenceCounter($variableInstance, $evt);

        // set scope activity instance id
        $evt->setScopeActivityInstanceId($scopeActivityInstanceId);

        // set source activity instance id
        $evt->setActivityInstanceId($sourceActivityInstanceId);

        // mark initial variables on process start
        if (
            $sourceExecution !== null
            && $sourceExecution->isProcessInstanceStarting()
            && HistoryEventTypes::variableInstanceCreate()->equals($eventType)
        ) {
            if ($variableInstance->getSequenceCounter() == 1) {
                $evt->setInitial(true);
            }

            if ($sourceActivityInstanceId == null && $sourceExecution->getActivity() !== null && $sourceExecution->getTransition() == null) {
                $evt->setActivityInstanceId($sourceExecution->getProcessInstanceId());
            }
        }

        return $evt;
    }

    // event instance factory ////////////////////////

    protected function newProcessInstanceEventEntity(ExecutionEntity $execution): HistoricProcessInstanceEventEntity
    {
        return new HistoricProcessInstanceEventEntity();
    }

    protected function newActivityInstanceEventEntity(ExecutionEntity $execution): HistoricActivityInstanceEventEntity
    {
        return new HistoricActivityInstanceEventEntity();
    }

    protected function newTaskInstanceEventEntity(DelegateTaskInterface $task): HistoricTaskInstanceEventEntity
    {
        return new HistoricTaskInstanceEventEntity();
    }

    protected function newVariableUpdateEventEntity(ExecutionEntity $execution): HistoricVariableUpdateEventEntity
    {
        return new HistoricVariableUpdateEventEntity();
    }

    protected function newHistoricFormPropertyEvent(): HistoricFormPropertyEventEntity
    {
        return new HistoricFormPropertyEventEntity();
    }

    protected function newIncidentEventEntity(IncidentInterface $incident): HistoricIncidentEventEntity
    {
        return new HistoricIncidentEventEntity();
    }

    protected function newHistoricJobLogEntity(JobInterface $job): HistoricJobLogEventEntity
    {
        return new HistoricJobLogEventEntity();
    }

    protected function newBatchEventEntity(BatchEntity $batch): HistoricBatchEntity
    {
        return new HistoricBatchEntity();
    }

    protected function loadProcessInstanceEventEntity(ExecutionEntity $execution): HistoricProcessInstanceEventEntity
    {
        return $this->newProcessInstanceEventEntity($execution);
    }

    protected function loadActivityInstanceEventEntity(ExecutionEntity $execution): HistoricActivityInstanceEventEntity
    {
        return $this->newActivityInstanceEventEntity($execution);
    }

    protected function loadTaskInstanceEvent(DelegateTaskInterface $task): HistoricTaskInstanceEventEntity
    {
        return $this->newTaskInstanceEventEntity($task);
    }

    protected function loadIncidentEvent(IncidentInterface $incident): HistoricIncidentEventEntity
    {
        return $this->newIncidentEventEntity($incident);
    }

    protected function loadBatchEntity(BatchEntity $batch): HistoricBatchEntity
    {
        return $this->newBatchEventEntity($batch);
    }

    // Implementation ////////////////////////////////

    public function createProcessInstanceStartEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->newProcessInstanceEventEntity($executionEntity);

        // initialize event
        $this->initProcessInstanceEvent($evt, $executionEntity, HistoryEventTypes::processInstanceStart());

        $evt->setStartActivityId($executionEntity->getActivityId());
        $evt->setStartTime(ClockUtil::getCurrentTime()->format('c'));

        // set super process instance id
        $superExecution = $executionEntity->getSuperExecution();
        if ($superExecution !== null) {
            $evt->setSuperProcessInstanceId($superExecution->getProcessInstanceId());
        }

        //state
        $evt->setState(HistoricProcessInstanceInterface::STATE_ACTIVE);

        // set start user Id
        $evt->setStartUserId(Context::getCommandContext()->getAuthenticatedUserId());

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            if ($this->isRootProcessInstance($evt)) {
                $removalTime = $this->calculateRemovalTime($evt);
                $evt->setRemovalTime($removalTime);
            } else {
                $this->provideRemovalTime($evt);
            }
        }

        return $evt;
    }

    public function createProcessInstanceUpdateEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->loadProcessInstanceEventEntity($executionEntity);

        // initialize event
        $this->initProcessInstanceEvent($evt, $executionEntity, HistoryEventTypes::processInstanceUpdate());

        if ($executionEntity->isSuspended()) {
            $evt->setState(HistoricProcessInstanceInterface::STATE_SUSPENDED);
        } else {
            $evt->setState(HistoricProcessInstanceInterface::STATE_ACTIVE);
        }

        return $evt;
    }

    public function createProcessInstanceMigrateEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->newProcessInstanceEventEntity($executionEntity);

        // initialize event
        $this->initProcessInstanceEvent($evt, $executionEntity, HistoryEventTypes::processInstanceMigrate());

        if ($executionEntity->isSuspended()) {
            $evt->setState(HistoricProcessInstanceInterface::STATE_SUSPENDED);
        } else {
            $evt->setState(HistoricProcessInstanceInterface::STATE_ACTIVE);
        }

        return $evt;
    }

    public function createProcessInstanceEndEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->loadProcessInstanceEventEntity($executionEntity);

        // initialize event
        $this->initProcessInstanceEvent($evt, $executionEntity, HistoryEventTypes::processInstanceEnd());

        $this->determineEndState($executionEntity, $evt);

        // set end activity id
        $evt->setEndActivityId($executionEntity->getActivityId());
        $evt->setEndTime(ClockUtil::getCurrentTime()->format('c'));

        if ($evt->getStartTime() !== null) {
            $et = new \DateTime($evt->getEndTime());
            $endTimeUt = $et->getTimestamp();

            $st = new \DateTime($evt->getStartTime());
            $startTimeUt = $st->getTimestamp();

            $evt->setDurationInMillis($endTimeUt * 1000 - $startTimeUt * 1000);
        }

        if ($this->isRootProcessInstance($evt) && $this->isHistoryRemovalTimeStrategyEnd()) {
            $removalTime = $this->calculateRemovalTime($evt);

            if ($removalTime !== null) {
                $this->addRemovalTimeToHistoricProcessInstances($evt->getRootProcessInstanceId(), $removalTime);
                /*if ($this->isDmnEnabled()) {
                    $this->addRemovalTimeToHistoricDecisions($evt->getRootProcessInstanceId(), $removalTime);
                }*/
            }
        }

        // set delete reason (if applicable).
        if ($executionEntity->getDeleteReason() !== null) {
            $evt->setDeleteReason($executionEntity->getDeleteReason());
        }

        return $evt;
    }

    protected function addRemovalTimeToHistoricDecisions(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        Context::getCommandContext()
            ->getHistoricDecisionInstanceManager()
            ->addRemovalTimeToDecisionsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);
    }

    protected function addRemovalTimeToHistoricProcessInstances(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        Context::getCommandContext()
            ->getHistoricProcessInstanceManager()
            ->addRemovalTimeToProcessInstancesByRootProcessInstanceId($rootProcessInstanceId, $removalTime);
    }

    /*protected boolean isDmnEnabled() {
        return Context::getCommandContext()
            ->getProcessEngineConfiguration()
            ->isDmnEnabled();
    }*/

    protected function determineEndState(ExecutionEntity $executionEntity, HistoricProcessInstanceEventEntity $evt): void
    {
        //determine state
        if ($executionEntity->getActivity() !== null) {
            $evt->setState(HistoricProcessInstanceInterface::STATE_COMPLETED);
        } else {
            if ($executionEntity->isExternallyTerminated()) {
                $evt->setState(HistoricProcessInstanceInterface::STATE_EXTERNALLY_TERMINATED);
            } elseif (!$executionEntity->isExternallyTerminated()) {
                $evt->setState(HistoricProcessInstanceInterface::STATE_INTERNALLY_TERMINATED);
            }
        }
    }

    public function createActivityInstanceStartEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->newActivityInstanceEventEntity($executionEntity);

        // initialize event
        $this->initActivityInstanceEvent($evt, $executionEntity, HistoryEventTypes::activityInstanceStart());

        // initialize sequence counter
        $this->initSequenceCounter($executionEntity, $evt);

        $evt->setStartTime(ClockUtil::getCurrentTime()->format('c'));

        return $evt;
    }

    public function createActivityInstanceUpdateEvt(DelegateExecutionInterface $execution, ?DelegateTaskInterface $task = null): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->loadActivityInstanceEventEntity($executionEntity);

        // initialize event
        $this->initActivityInstanceEvent($evt, $executionEntity, HistoryEventTypes::activityInstanceUpdate());

        // update task assignment
        if ($task !== null) {
            $evt->setTaskId($task->getId());
            $evt->setTaskAssignee($task->getAssignee());
        }

        return $evt;
    }

    public function createActivityInstanceMigrateEvt(MigratingActivityInstance $actInstance): HistoryEvent
    {

        // create event instance
        $evt = $this->loadActivityInstanceEventEntity($actInstance->resolveRepresentativeExecution());

        // initialize event
        $this->initActivityInstanceEvent($evt, $actInstance, HistoryEventTypes::activityInstanceMigrate());

        return $evt;
    }

    public function createActivityInstanceEndEvt(DelegateExecutionInterface $execution): HistoryEvent
    {
        $executionEntity = $execution;

        // create event instance
        $evt = $this->loadActivityInstanceEventEntity($executionEntity);
        $evt->setActivityInstanceState($executionEntity->getActivityInstanceState());

        // initialize event
        $this->initActivityInstanceEvent($evt, $execution, HistoryEventTypes::activityInstanceEnd());

        $evt->setEndTime(ClockUtil::getCurrentTime()->format('c'));
        if ($evt->getStartTime() !== null) {
            $evt->setDurationInMillis((new \DateTime($evt->getEndTime()))->getTimestamp() * 1000 - (new \DateTime($evt->getStartTime()))->getTimestamp() * 1000);
        }

        return $evt;
    }

    public function createTaskInstanceCreateEvt(DelegateTaskInterface $task): HistoryEvent
    {
        // create event instance
        $evt = $this->newTaskInstanceEventEntity($task);

        // initialize event
        $this->initTaskInstanceEvent($evt, $task, HistoryEventTypes::taskInstanceCreate());

        $evt->setStartTime(ClockUtil::getCurrentTime()->format('c'));

        return $evt;
    }

    public function createTaskInstanceUpdateEvt(DelegateTaskInterface $task): HistoryEvent
    {

        // create event instance
        $evt = $this->loadTaskInstanceEvent($task);

        // initialize event
        $this->initTaskInstanceEvent($evt, $task, HistoryEventTypes::taskInstanceUpdate());

        return $evt;
    }

    public function createTaskInstanceMigrateEvt(DelegateTaskInterface $task): HistoryEvent
    {
        // create event instance
        $evt = $this->loadTaskInstanceEvent($task);

        // initialize event
        $this->initTaskInstanceEvent($evt, $task, HistoryEventTypes::taskInstanceMigrate());

        return $evt;
    }

    public function createTaskInstanceCompleteEvt(DelegateTaskInterface $task, ?string $deleteReason): HistoryEvent
    {
        // create event instance
        $evt = $this->loadTaskInstanceEvent($task);

        // initialize event
        $this->initTaskInstanceEvent($evt, $task, HistoryEventTypes::taskInstanceComplete());

        // set end time
        $evt->setEndTime(ClockUtil::getCurrentTime()->format('c'));
        if ($evt->getStartTime() !== null) {
            $evt->setDurationInMillis((new \DateTime($evt->getEndTime()))->getTimestamp() * 1000 - (new \DateTime($evt->getStartTime()))->getTimestamp() * 1000);
        }

        // set delete reason
        $evt->setDeleteReason($deleteReason);

        return $evt;
    }

    // User Operation Logs ///////////////////////////

    public function createUserOperationLogEvents(UserOperationLogContext $context): array
    {
        $historyEvents = [];

        $operationId = Context::getCommandContext()->getOperationId();
        $context->setOperationId($operationId);

        foreach ($context->getEntries() as $entry) {
            foreach ($entry->getPropertyChanges() as $propertyChange) {
                $evt = new UserOperationLogEntryEventEntity();

                $this->initUserOperationLogEvent($evt, $context, $entry, $propertyChange);

                $historyEvents[] = $evt;
            }
        }

        return $historyEvents;
    }

    // variables /////////////////////////////////

    public function createHistoricVariableCreateEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent
    {
        return $this->createHistoricVariableEvent($variableInstance, $sourceVariableScope, HistoryEventTypes::variableInstanceCreate());
    }

    public function createHistoricVariableDeleteEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent
    {
        return $this->createHistoricVariableEvent($variableInstance, $sourceVariableScope, HistoryEventTypes::variableInstanceDelete());
    }

    public function createHistoricVariableUpdateEvt(VariableInstanceEntity $variableInstance, VariableScopeInterface $sourceVariableScope): HistoryEvent
    {
        return $this->createHistoricVariableEvent($variableInstance, $sourceVariableScope, HistoryEventTypes::variableInstanceUpdate());
    }

    public function createHistoricVariableMigrateEvt(VariableInstanceEntity $variableInstance): HistoryEvent
    {
        return $this->createHistoricVariableEvent($variableInstance, null, HistoryEventTypes::variableInstanceMigrate());
    }

    // form Properties ///////////////////////////

    public function createFormPropertyUpdateEvt(ExecutionEntity $execution, ?string $propertyId, ?string $propertyValue, ?string $taskId): HistoryEvent
    {
        $idGenerator = Context::getProcessEngineConfiguration()->getIdGenerator();

        $historicFormPropertyEntity = $this->newHistoricFormPropertyEvent();
        $historicFormPropertyEntity->setId($idGenerator->getNextId());
        $historicFormPropertyEntity->setEventType(HistoryEventTypes::formPropertyUpdate()->getEventName());
        $historicFormPropertyEntity->setTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $historicFormPropertyEntity->setExecutionId($execution->getId());
        $historicFormPropertyEntity->setProcessDefinitionId($execution->getProcessDefinitionId());
        $historicFormPropertyEntity->setProcessInstanceId($execution->getProcessInstanceId());
        $historicFormPropertyEntity->setPropertyId($propertyId);
        $historicFormPropertyEntity->setPropertyValue($propertyValue);
        $historicFormPropertyEntity->setTaskId($taskId);
        $historicFormPropertyEntity->setTenantId($execution->getTenantId());
        $historicFormPropertyEntity->setUserOperationId(Context::getCommandContext()->getOperationId());
        $historicFormPropertyEntity->setRootProcessInstanceId($execution->getRootProcessInstanceId());

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($historicFormPropertyEntity);
        }

        $definition = $execution->getProcessDefinition();
        if ($definition !== null) {
            $historicFormPropertyEntity->setProcessDefinitionKey($definition->getKey());
        }

        // initialize sequence counter
        $this->initSequenceCounter($execution, $historicFormPropertyEntity);

        if ($execution->isProcessInstanceStarting()) {
            // instantiate activity instance id as process instance id when starting a process instance
            // via a form
            $historicFormPropertyEntity->setActivityInstanceId($execution->getProcessInstanceId());
        } else {
            $historicFormPropertyEntity->setActivityInstanceId($execution->getActivityInstanceId());
        }

        return $historicFormPropertyEntity;
    }

    // Incidents //////////////////////////////////

    public function createHistoricIncidentCreateEvt(IncidentInterface $incident): HistoryEvent
    {
        return $this->createHistoricIncidentEvt($incident, HistoryEventTypes::incidentCreate());
    }

    public function createHistoricIncidentUpdateEvt(IncidentInterface $incident): HistoryEvent
    {
        return $this->createHistoricIncidentEvt($incident, HistoryEventTypes::incidentUpdate());
    }

    public function createHistoricIncidentResolveEvt(IncidentInterface $incident): HistoryEvent
    {
        return $this->createHistoricIncidentEvt($incident, HistoryEventTypes::incidentResolve());
    }

    public function createHistoricIncidentDeleteEvt(IncidentInterface $incident): HistoryEvent
    {
        return $this->createHistoricIncidentEvt($incident, HistoryEventTypes::incidentDelete());
    }

    public function createHistoricIncidentMigrateEvt(IncidentInterface $incident): HistoryEvent
    {
        return $this->createHistoricIncidentEvt($incident, HistoryEventTypes::incidentMigrate());
    }

    protected function createHistoricIncidentEvt(IncidentInterface $incident, HistoryEventTypes $eventType): HistoryEvent
    {
        // create event
        $evt = $this->loadIncidentEvent($incident);
        // initialize
        $this->initHistoricIncidentEvent($evt, $incident, $eventType);

        if (HistoryEventTypes::incidentResolve()->equals($eventType) || HistoryEventTypes::incidentDelete()->equals($eventType)) {
            $evt->setEndTime(ClockUtil::getCurrentTime()->format('c'));
        }

        return $evt;
    }

    public function createHistoricIdentityLinkAddEvent(IdentityLinkInterface $identityLink): HistoryEvent
    {
        return $this->createHistoricIdentityLinkEvt($identityLink, HistoryEventTypes::identityLinkAdd());
    }

    public function createHistoricIdentityLinkDeleteEvent(IdentityLinkInterface $identityLink): HistoryEvent
    {
        return $this->createHistoricIdentityLinkEvt($identityLink, HistoryEventTypes::identityLinkDelete());
    }

    protected function createHistoricIdentityLinkEvt(IdentityLinkInterface $identityLink, HistoryEventTypes $eventType): HistoryEvent
    {
        // create historic identity link event
        $evt = $this->newIdentityLinkEventEntity();
        // Mapping all the values of identity link to HistoricIdentityLinkEvent
        $this->initHistoricIdentityLinkEvent($evt, $identityLink, $eventType);
        return $evt;
    }

    protected function newIdentityLinkEventEntity(): HistoricIdentityLinkLogEventEntity
    {
        return new HistoricIdentityLinkLogEventEntity();
    }

    protected function initHistoricIdentityLinkEvent(HistoricIdentityLinkLogEventEntity $evt, IdentityLinkInterface $identityLink, HistoryEventTypeInterface $eventType): void
    {
        if ($identityLink->getTaskId() !== null) {
            $task = Context::getCommandContext()
                ->getTaskManager()
                ->findTaskById($identityLink->getTaskId());

            $evt->setProcessDefinitionId($task->getProcessDefinitionId());

            if ($task->getProcessDefinition() !== null) {
                $evt->setProcessDefinitionKey($task->getProcessDefinition()->getKey());
            }

            $execution = $task->getExecution();
            if ($execution !== null) {
                $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

                if ($this->isHistoryRemovalTimeStrategyStart()) {
                    $this->provideRemovalTime($evt);
                }
            }
        }

        if ($identityLink->getProcessDefId() !== null) {
            $evt->setProcessDefinitionId($identityLink->getProcessDefId());

            $definition = Context::getProcessEngineConfiguration()
                ->getDeploymentCache()
                ->findProcessDefinitionFromCache($identityLink->getProcessDefId());
            $evt->setProcessDefinitionKey($definition->getKey());
        }

        $evt->setTime(ClockUtil::getCurrentTime()->format('c'));
        $evt->setType($identityLink->getType());
        $evt->setUserId($identityLink->getUserId());
        $evt->setGroupId($identityLink->getGroupId());
        $evt->setTaskId($identityLink->getTaskId());
        $evt->setTenantId($identityLink->getTenantId());
        // There is a conflict in HistoryEventTypes for 'delete' keyword,
        // So HistoryEventTypes.IDENTITY_LINK_ADD /
        // HistoryEventTypes.IDENTITY_LINK_DELETE is provided with the event name
        // 'add-identity-link' /'delete-identity-link'
        // and changed to 'add'/'delete' (While inserting it into the database) on
        // Historic identity link add / delete event
        $operationType = "add";
        if ($eventType->getEventName() == HistoryEventTypes::identityLinkDelete()->getEventName()) {
            $operationType = "delete";
        }

        $evt->setOperationType($operationType);
        $evt->setEventType($eventType->getEventName());
        $evt->setAssignerId(Context::getCommandContext()->getAuthenticatedUserId());
    }
    // Batch

    public function createBatchStartEvent(BatchInterface $batch): HistoryEvent
    {
        $historicBatch = $this->createBatchEvent($batch, HistoryEventTypes::batchStart());

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($historicBatch);
        }

        return $historicBatch;
    }

    public function createBatchEndEvent(BatchInterface $batch): HistoryEvent
    {
        $historicBatch = $this->createBatchEvent($batch, HistoryEventTypes::batchEnd());

        if ($this->isHistoryRemovalTimeStrategyEnd()) {
            $this->provideRemovalTime($historicBatch);

            $this->addRemovalTimeToHistoricJobLog($historicBatch);
            $this->addRemovalTimeToHistoricIncidents($historicBatch);
        }

        return $historicBatch;
    }

    protected function createBatchEvent(BatchEntity $batch, HistoryEventTypes $eventType): HistoryEvent
    {
        $event = $this->loadBatchEntity($batch);

        $event->setId($batch->getId());
        $event->setType($batch->getType());
        $event->setTotalJobs($batch->getTotalJobs());
        $event->setBatchJobsPerSeed($batch->getBatchJobsPerSeed());
        $event->setInvocationsPerBatchJob($batch->getInvocationsPerBatchJob());
        $event->setSeedJobDefinitionId($batch->getSeedJobDefinitionId());
        $event->setMonitorJobDefinitionId($batch->getMonitorJobDefinitionId());
        $event->setBatchJobDefinitionId($batch->getBatchJobDefinitionId());
        $event->setTenantId($batch->getTenantId());
        $event->setEventType($eventType->getEventName());

        if (HistoryEventTypes::batchStart()->equals($eventType)) {
            $event->setStartTime(ClockUtil::getCurrentTime()->format('c'));
            $event->setCreateUserId(Context::getCommandContext()->getAuthenticatedUserId());
        }

        if (HistoryEventTypes::batchEnd()->equals($eventType)) {
            $event->setEndTime(ClockUtil::getCurrentTime()->format('c'));
        }

        return $event;
    }

    // Job Log

    public function createHistoricJobLogCreateEvt(JobInterface $job): HistoryEvent
    {
        return $this->createHistoricJobLogEvt($job, HistoryEventTypes::jobCreate());
    }

    public function createHistoricJobLogFailedEvt(JobInterface $job, ?\Throwable $exception = null): HistoryEvent
    {
        $event = $this->createHistoricJobLogEvt($job, HistoryEventTypes::jobFail());

        if ($exception !== null) {
            // exception message
            $event->setJobExceptionMessage($exception->getMessage());

            // stacktrace
            $exceptionStacktrace = ExceptionUtil::getExceptionStacktrace($exception);
            $exceptionBytes = StringUtil::toByteArray($exceptionStacktrace);

            $byteArray = ExceptionUtil::createJobExceptionByteArray($exceptionBytes, ResourceTypes::history());
            $byteArray->setRootProcessInstanceId($event->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $byteArray->setRemovalTime($event->getRemovalTime());
            }

            $event->setExceptionByteArrayId($byteArray->getId());
        }

        return $event;
    }

    public function createHistoricJobLogSuccessfulEvt(JobInterface $job): HistoryEvent
    {
        return $this->createHistoricJobLogEvt($job, HistoryEventTypes::jobSuccess());
    }

    public function createHistoricJobLogDeleteEvt(JobInterface $job): HistoryEvent
    {
        return $this->createHistoricJobLogEvt($job, HistoryEventTypes::jobDelete());
    }

    protected function createHistoricJobLogEvt(JobInterface $job, HistoryEventTypeInterface $eventType): HistoryEvent
    {
        $event = $this->newHistoricJobLogEntity($job);
        $this->initHistoricJobLogEvent($event, $job, $eventType);
        return $event;
    }

    protected function initHistoricJobLogEvent(HistoricJobLogEventEntity $evt, JobInterface $job, HistoryEventTypeInterface $eventType): void
    {
        $currentTime = ClockUtil::getCurrentTime()->format('c');
        $evt->setTimestamp($currentTime);

        $evt->setJobId($job->getId());
        $evt->setJobDueDate($job->getDuedate());
        $evt->setJobRetries($job->getRetries());
        $evt->setJobPriority($job->getPriority());

        $hostName = Context::getCommandContext()->getProcessEngineConfiguration()->getHostname();
        $evt->setHostname($hostName);

        //@TODO
        /*if (HistoryCleanupJobHandler::TYPE == $jobEntity->getJobHandlerType()) {
            $timeToLive = Context::getProcessEngineConfiguration()->getHistoryCleanupJobLogTimeToLive();
            if ($timeToLive !== null) {
                try {
                    $timeToLiveDays = ParseUtil::parseHistoryTimeToLive($timeToLive);
                    $removalTime = DefaultHistoryRemovalTimeProvider::determineRemovalTime($currentTime, $timeToLiveDays);
                    $evt->setRemovalTime($removalTime);
                } catch (ProcessEngineException $e) {
                    //$wrappedException = LOG.invalidPropertyValue("historyCleanupJobLogTimeToLive", timeToLive, e);
                    //LOG.invalidPropertyValue(wrappedException);
                    throw $e;
                }
            }
        }*/
        $jobDefinition = $job->getJobDefinition();
        if ($jobDefinition !== null) {
            $evt->setJobDefinitionId($jobDefinition->getId());
            $evt->setJobDefinitionType($jobDefinition->getJobType());
            $evt->setJobDefinitionConfiguration($jobDefinition->getJobConfiguration());

            $historicBatchId = $jobDefinition->getJobConfiguration();
            if ($historicBatchId !== null && $this->isHistoryRemovalTimeStrategyStart()) {
                $historicBatch = $this->getHistoricBatchById($historicBatchId);
                if ($historicBatch !== null) {
                    $evt->setRemovalTime($historicBatch->getRemovalTime());
                }
            }
        } else {
            // in case of async signal there does not exist a job definition
            // but we use the jobHandlerType as jobDefinitionType
            $evt->setJobDefinitionType($job->getJobHandlerType());
        }

        $evt->setActivityId($job->getActivityId());
        $evt->setFailedActivityId($job->getFailedActivityId());
        $evt->setExecutionId($job->getExecutionId());
        $evt->setProcessInstanceId($job->getProcessInstanceId());
        $evt->setProcessDefinitionId($job->getProcessDefinitionId());
        $evt->setProcessDefinitionKey($job->getProcessDefinitionKey());
        $evt->setDeploymentId($job->getDeploymentId());
        $evt->setTenantId($job->getTenantId());

        $execution = $job->getExecution();
        if ($execution !== null) {
            $evt->setRootProcessInstanceId($execution->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $this->provideRemovalTime($evt);
            }
        }

        // initialize sequence counter
        $this->initSequenceCounter($job, $evt);

        $state = null;
        if (HistoryEventTypes::jobCreate()->equals($eventType)) {
            $state = JobStateImpl::created();
        } elseif (HistoryEventTypes::jobFail()->equals($eventType)) {
            $state = JobStateImpl::failed();
        } elseif (HistoryEventTypes::jobSuccess()->equals($eventType)) {
            $state = JobStateImpl::successful();
        } elseif (HistoryEventTypes::jobDelete()->equals($eventType)) {
            $state = JobStateImpl::deleted();
        }
        $evt->setState($state->getStateCode());
    }

    public function createHistoricExternalTaskLogCreatedEvt(ExternalTaskInterface $task): HistoryEvent
    {
        return initHistoricExternalTaskLog($task, ExternalTaskStateImpl::created());
    }

    public function createHistoricExternalTaskLogFailedEvt(ExternalTaskInterface $task): HistoryEvent
    {
        $event = $this->initHistoricExternalTaskLog($task, ExternalTaskStateImpl::failed());
        $event->setErrorMessage($task->getErrorMessage());
        $errorDetails = $task->getErrorDetails();
        if ($errorDetails !== null) {
            $event->setErrorDetails($errorDetails);
        }
        return $event;
    }

    public function createHistoricExternalTaskLogSuccessfulEvt(ExternalTaskInterface $task): HistoryEvent
    {
        return $this->initHistoricExternalTaskLog($task, ExternalTaskStateImpl::successful());
    }

    public function createHistoricExternalTaskLogDeletedEvt(ExternalTaskInterface $task): HistoryEvent
    {
        return $this->initHistoricExternalTaskLog($task, ExternalTaskStateImpl::deleted());
    }

    protected function initHistoricExternalTaskLog(ExternalTaskEntity $entity, ExternalTaskStateInterface $state): HistoricExternalTaskLogEntity
    {
        $event = new HistoricExternalTaskLogEntity();
        $event->setTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $event->setExternalTaskId($entity->getId());
        $event->setTopicName($entity->getTopicName());
        $event->setWorkerId($entity->getWorkerId());

        $event->setPriority($entity->getPriority());
        $event->setRetries($entity->getRetries());

        $event->setActivityId($entity->getActivityId());
        $event->setActivityInstanceId($entity->getActivityInstanceId());
        $event->setExecutionId($entity->getExecutionId());

        $event->setProcessInstanceId($entity->getProcessInstanceId());
        $event->setProcessDefinitionId($entity->getProcessDefinitionId());
        $event->setProcessDefinitionKey($entity->getProcessDefinitionKey());
        $event->setTenantId($entity->getTenantId());
        $event->setState($state->getStateCode());

        $execution = $entity->getExecution();
        if ($execution !== null) {
            $event->setRootProcessInstanceId($execution->getRootProcessInstanceId());

            if ($this->isHistoryRemovalTimeStrategyStart()) {
                $this->provideRemovalTime($event);
            }
        }

        return $event;
    }

    protected function isRootProcessInstance(HistoricProcessInstanceEventEntity $evt): bool
    {
        return $evt->getProcessInstanceId() == $evt->getRootProcessInstanceId();
    }

    protected function isHistoryRemovalTimeStrategyStart(): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_START == $this->getHistoryRemovalTimeStrategy();
    }

    protected function isHistoryRemovalTimeStrategyEnd(): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_END == $this->getHistoryRemovalTimeStrategy();
    }

    protected function getHistoryRemovalTimeStrategy(): ?string
    {
        return Context::getProcessEngineConfiguration()
            ->getHistoryRemovalTimeStrategy();
    }

    protected function calculateRemovalTime($event): ?string
    {
        if ($event instanceof HistoryEvent) {
            $processDefinitionId = $event->getProcessDefinitionId();
            $processDefinition = $this->findProcessDefinitionById($processDefinitionId);

            return Context::getProcessEngineConfiguration()
                ->getHistoryRemovalTimeProvider()
                ->calculateRemovalTime($event, $processDefinition);
        } elseif ($event instanceof HistoricBatchEntity) {
            return Context::getProcessEngineConfiguration()
            ->getHistoryRemovalTimeProvider()
            ->calculateRemovalTime($event);
        }
        return null;
    }

    protected function provideRemovalTime($event): void
    {
        if ($event instanceof HistoryEvent) {
            $rootProcessInstanceId = $event->getRootProcessInstanceId();
            if ($rootProcessInstanceId !== null) {
                $historicRootProcessInstance = $this->getHistoricRootProcessInstance($rootProcessInstanceId);

                if ($historicRootProcessInstance !== null) {
                    $removalTime = $historicRootProcessInstance->getRemovalTime();
                    $event->setRemovalTime($removalTime);
                }
            }
        } elseif ($event instanceof HistoricBatchEntity) {
            $removalTime = $this->calculateRemovalTime($event);
            if ($removalTime !== null) {
                $event->setRemovalTime($removalTime);
            }
        }
    }

    protected function getHistoricRootProcessInstance(?string $rootProcessInstanceId): ?HistoricProcessInstanceEventEntity
    {
        return Context::getCommandContext()
            ->getDbEntityManager()
            ->selectById(HistoricProcessInstanceEventEntity::class, $rootProcessInstanceId);
    }

    protected function findProcessDefinitionById(?string $processDefinitionId): ?ProcessDefinitionInterface
    {
        return Context::getCommandContext()
            ->getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId);
    }

    protected function getHistoricBatchById(?string $batchId): ?HistoricBatchEntity
    {
        return Context::getCommandContext()
            ->getHistoricBatchManager()
            ->findHistoricBatchById($batchId);
    }

    protected function getHistoricBatchByJobId(?string $jobId): ?HistoricBatchEntity
    {
        return Context::getCommandContext()
            ->getHistoricBatchManager()
            ->findHistoricBatchByJobId($jobId);
    }

    protected function addRemovalTimeToHistoricJobLog(HistoricBatchEntity $historicBatch): void
    {
        $removalTime = $historicBatch->getRemovalTime();
        if ($removalTime !== null) {
            Context::getCommandContext()
            ->getHistoricJobLogManager()
            ->addRemovalTimeToJobLogByBatchId($historicBatch->getId(), $removalTime);
        }
    }

    protected function addRemovalTimeToHistoricIncidents(HistoricBatchEntity $historicBatch): void
    {
        $removalTime = $historicBatch->getRemovalTime();
        if ($removalTime !== null) {
            Context::getCommandContext()
            ->getHistoricIncidentManager()
            ->addRemovalTimeToHistoricIncidentsByBatchId($historicBatch->getId(), $removalTime);
        }
    }

    // sequence counter //////////////////////////////////////////////////////

    protected function initSequenceCounter($obj, HistoryEvent $event): void
    {
        if (is_int($obj)) {
            $event->setSequenceCounter($obj);
        } elseif (method_exists($obj, 'getSequenceCounter')) {
            $this->initSequenceCounter($obj->getSequenceCounter(), $event);
        }
    }
}
