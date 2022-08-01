<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\{
    ProcessEngineInterface,
    ProcessEngineException,
    ProcessEngineServicesInterface
};
use Jabe\Engine\Delegate\{
    BpmnError,
    DelegateTaskInterface,
    ExpressionInterface,
    TaskListenerInterface
};
use Jabe\Engine\Exception\{
    NotFoundException,
    NullValueException
};
use Jabe\Engine\Form\FormRefInterface;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Helper\{
    BpmnExceptionHandler,
    ErrorPropagationException,
    EscalationHandler
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cfg\Auth\ResourceAuthorizationProviderInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceFactoryInterface,
    VariableInstanceLifecycleListenerInterface,
    VariableStore,
    VariablesProviderInterface
};
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface
};
use Jabe\Engine\Impl\Form\FormRefImpl;
use Jabe\Engine\Impl\Form\Handler\DefaultFormHandler;
use Jabe\Engine\Impl\History\Event\HistoryEventTypes;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandContextListenerInterface,
    CommandInvocationContext
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecution;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Engine\Impl\Task\TaskDefinition;
use Jabe\Engine\Impl\Task\Delegate\TaskListenerInvocation;
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil
};
use Jabe\Engine\Management\Metrics;
use Jabe\Engine\Task\{
    DelegationState,
    IdentityLinkInterface,
    IdentityLinkType,
    TaskInterface
};
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\UserTaskInterface;

class TaskEntity extends AbstractVariableScope implements TaskInterface, DelegateTaskInterface, \Serializable, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface, CommandContextListenerInterface, VariablesProviderInterface
{
    protected static $DEFAULT_VARIABLE_LIFECYCLE_LISTENERS;

    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public const DELETE_REASON_COMPLETED = "completed";
    public const DELETE_REASON_DELETED   = "deleted";

    protected $id;
    protected $revision;

    protected $owner;
    protected $assignee;
    protected $delegationState;

    protected $parentTaskId;
    protected $parentTask;

    protected $name;
    protected $description;
    protected $priority = TaskInterface::PRIORITY_NORMAL;
    protected $createTime; // The time when the task has been created
    protected $dueDate;
    protected $followUpDate;
    protected $suspensionState;
    protected $lifecycleState = TaskState::STATE_INIT;
    protected $tenantId;

    protected $isIdentityLinksInitialized = false;
    protected $taskIdentityLinkEntities = [];

    // execution
    protected $executionId;
    protected $execution;

    protected $processInstanceId;
    protected $processInstance;

    protected $processDefinitionId;

    // caseExecution
    /*protected String caseExecutionId;
    protected transient CaseExecutionEntity caseExecution;

    protected String caseInstanceId;
    protected String caseDefinitionId;*/

    // taskDefinition
    protected $taskDefinition;
    protected $taskDefinitionKey;

    protected $isDeleted;
    protected $deleteReason;

    protected $eventName;
    protected $isFormKeyInitialized = false;
    protected $formKey;
    protected $formRef;

    protected $variableStore;

    protected $skipCustomListeners = false;

    /**
     * contains all changed properties of this entity
     */
    protected $propertyChanges = [];

    protected $identityLinkChanges = [];

    protected $customLifecycleListeners;

    // name references of tracked properties
    public const ASSIGNEE = "assignee";
    public const DELEGATION = "delegation";
    public const DELETE = "delete";
    public const DESCRIPTION = "description";
    public const DUE_DATE = "dueDate";
    public const FOLLOW_UP_DATE = "followUpDate";
    public const NAME = "name";
    public const OWNER = "owner";
    public const PARENT_TASK = "parentTask";
    public const PRIORITY = "priority";
    public const CASE_INSTANCE_ID = "caseInstanceId";

    public function __construct($data = null)
    {
        if (is_string($data)) {
            $this->id = $data;
            $this->isIdentityLinksInitialized = true;
            $this->setCreateTime(ClockUtil::getCurrentTime()->format('c'));
            $this->lifecycleState = TaskState::STATE_INIT;
        } elseif (is_int($data)) {
            $this->isIdentityLinksInitialized = true;
            $this->setCreateTime(ClockUtil::getCurrentTime()->format('c'));
            $this->lifecycleState = $data;
        } elseif ($data instanceof ExecutionEntity) {
            $this->isIdentityLinksInitialized = true;
            $this->setCreateTime(ClockUtil::getCurrentTime()->format('c'));
            $this->lifecycleState = TaskState::STATE_INIT;
            $this->setExecution($data);
            $this->skipCustomListeners = $data->isSkipCustomListeners();
            $this->setTenantId($execution->getTenantId());
            $execution->addTask($this);
        } elseif ($data === null) {
            $this->lifecycleState = TaskState::STATE_CREATED;
            $this->variableStore = new VariableStore($this, new TaskEntityReferencer($this));
            $this->suspensionState = SuspensionState::active()->getStateCode();
        }
        if (self::$DEFAULT_VARIABLE_LIFECYCLE_LISTENERS === null) {
            self::$DEFAULT_VARIABLE_LIFECYCLE_LISTENERS = [
                VariableInstanceEntityPersistenceListener::instance(),
                VariableInstanceSequenceCounterListener::instance(),
                VariableInstanceHistoryListener::instance()
            ];
        }
    }

    /**
     * CMMN execution constructor
     */
    /*public TaskEntity(CaseExecutionEntity caseExecution) {
        this(TaskState::STATE_INIT);
        setCaseExecution(caseExecution);
    }*/

    public function insert(): void
    {
        $commandContext = Context::getCommandContext();
        $taskManager = $commandContext->getTaskManager();
        $taskManager->insertTask($this);
    }

    protected function propagateExecutionTenantId(?ExecutionEntity $execution): void
    {
        if ($execution !== null) {
            $this->setTenantId($execution->getTenantId());
        }
    }

    public function propagateParentTaskTenantId(): void
    {
        if ($this->parentTaskId !== null) {
            $parentTask = Context::getCommandContext()
                ->getTaskManager()
                ->findTaskById($this->parentTaskId);

            if ($this->tenantId !== null && !$this->tenantIdIsSame($parentTask)) {
                //throw LOG.cannotSetDifferentTenantIdOnSubtask(parentTaskId, parentTask->getTenantId(), tenantId);
                throw new \Exception("propagateParentTaskTenantId");
            }

            $this->setTenantId($parentTask->getTenantId());
        }
    }

    public function update(): void
    {
        $this->ensureTenantIdNotChanged();

        $this->registerCommandContextCloseListener();

        $commandContext = Context::getCommandContext();
        $dbEntityManger = $commandContext->getDbEntityManager();

        $dbEntityManger->merge($this);
    }

    protected function ensureTenantIdNotChanged(): void
    {
        $persistentTask = Context::getCommandContext()->getTaskManager()->findTaskById($this->id);

        if ($persistentTask !== null) {
            $changed = !$this->tenantIdIsSame($persistentTask);

            if ($changed) {
                //throw LOG.cannotChangeTenantIdOfTask(id, persistentTask.tenantId, tenantId);
                throw new \Exception("ensureTenantIdNotChanged");
            }
        }
    }

    protected function tenantIdIsSame(TaskEntity $otherTask): bool
    {
        $otherTenantId = $otherTask->getTenantId();

        if ($otherTenantId === null) {
            return $this->tenantId === null;
        } else {
            return $otherTenantId == $this->tenantId;
        }
    }

    public function complete(): void
    {
        if (
            TaskState::STATE_COMPLETED == $this->lifecycleState
            || TaskListenerInterface::EVENTNAME_COMPLETE == $this->eventName
            || TaskListenerInterface::EVENTNAME_DELETE == $this->eventName
        ) {
            //throw LOG.invokeTaskListenerException(new IllegalStateException("invalid task state"));
            throw new \Exception("invalid task state");
        }
        // if the task is associated with a case
        // execution then call complete on the
        // associated case execution. The case
        // execution handles the completion of
        // the task.
        /*if (caseExecutionId !== null) {
            getCaseExecution().manualComplete();
            return;
        }*/

        // in the other case:

        // ensure the the Task is not suspended
        $this->ensureTaskActive();

        // trigger TaskListenerInterface::complete event
        $shouldDeleteTask = $this->transitionTo(TaskState::STATE_COMPLETED);

        // shouldn't attempt to delete the task if the COMPLETE Task listener failed,
        // or managed to cancel the Process or Task Instance
        if ($shouldDeleteTask) {
            // delete the task
            // this method call doesn't invoke additional task listeners
            Context::getCommandContext()
            ->getTaskManager()
            ->deleteTask($this, TaskEntity::DELETE_REASON_COMPLETED, false, $this->skipCustomListeners);

            // if the task is associated with a
            // execution (and not a case execution)
            // and it's still in the same activity
            // then call signal an the associated
            // execution.
            if ($this->executionId !== null) {
                $execution = $this->getExecution();
                $execution->removeTask($this);
                $execution->signal(null, null);
            }
        }
    }

    /*public function caseExecutionCompleted(): void
    {
        // ensure the the Task is not suspended
        ensureTaskActive();

        // trigger TaskListenerInterface::complete event for a case execution associated task
        transitionTo(TaskState::STATE_COMPLETED);

        // delete the task
        Context
            ->getCommandContext()
            ->getTaskManager()
            ->deleteTask(this, TaskEntity::DELETE_REASON_COMPLETED, false, false);
    }*/

    public function delete(string $deleteReason, bool $cascade, ?bool $skipCustomListeners = null): void
    {
        if ($skipCustomListeners !== null) {
            $this->skipCustomListeners = $skipCustomListeners;
        }

        $this->deleteReason = $deleteReason;

        // only fire lifecycle events if task is actually cancelled/deleted
        if (
            !TaskEntity::DELETE_REASON_COMPLETED == $deleteReason
            && !TaskState::STATE_DELETED == $this->lifecycleState
        ) {
            $this->transitionTo(TaskState::STATE_DELETED);
        }

        Context::getCommandContext()
            ->getTaskManager()
            ->deleteTask($this, $deleteReason, $cascade, $this->skipCustomListeners);

        if ($this->executionId !== null) {
            $execution = $this->getExecution();
            $execution->removeTask($this);
        }
    }

    public function delegate(string $userId): void
    {
        $this->setDelegationState(DelegationState::PENDING);
        if ($this->getOwner() === null) {
            $this->setOwner($this->getAssignee());
        }
        $this->setAssignee($userId);
    }

    public function resolve(): void
    {
        $this->setDelegationState(DelegationState::RESOLVED);
        $this->setAssignee($this->owner);
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'revision' => $this->revision,
            'assignee' => $this->assignee,
            'owner' => $this->owner,
            'priority' => $this->priority,
            'executionId' => $this->executionId,
            'processDefinitionId' => $this->processDefinitionId,
            'createTime' => $this->createTime,
            'description' => $this->description,
            'dueDate' => $this->dueDate,
            'followUpDate' => $this->followUpDate,
            'parentTaskId' => $this->parentTaskId,
            'delegationState' => $this->delegationState,
            'tenantId' => $this->tenantId,
            'suspensionState' => $this->suspensionState
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->name = $json->name;
        $this->revision = $json->revision;
        $this->assignee = $json->assignee;
        $this->owner = $json->owner;
        $this->priority = $json->priority;
        $this->executionId = $json->executionId;
        $this->processDefinitionId = $json->processDefinitionId;
        $this->createTime = $json->createTime;
        $this->description = $json->description;
        $this->dueDate = $json->dueDate;
        $this->followUpDate = $json->followUpDate;
        $this->parentTaskId = $json->parentTaskId;
        $this->delegationState = $json->delegationState;
        $this->tenantId = $json->tenantId;
        $this->suspensionState = $json->suspensionState;
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["assignee"] = $this->assignee;
        $persistentState["owner"] = $this->owner;
        $persistentState["name"] = $this->name;
        $persistentState["priority"] = $this->priority;
        if ($this->executionId !== null) {
            $persistentState["executionId"] = $this->executionId;
        }
        if ($this->processDefinitionId !== null) {
            $persistentState["processDefinitionId"] = $this->processDefinitionId;
        }
        /*if (caseExecutionId !== null) {
            persistentState.put("caseExecutionId", $this->caseExecutionId);
        }
        if (caseInstanceId !== null) {
            persistentState.put("caseInstanceId", $this->caseInstanceId);
        }
        if (caseDefinitionId !== null) {
            persistentState.put("caseDefinitionId", $this->caseDefinitionId);
        }*/
        if ($this->createTime !== null) {
            $persistentState["createTime"] = $this->createTime;
        }
        if ($this->description !== null) {
            $persistentState["description"] = $this->description;
        }
        if ($this->dueDate !== null) {
            $persistentState["dueDate"] = $this->dueDate;
        }
        if ($this->followUpDate !== null) {
            $persistentState["followUpDate"] = $this->followUpDate;
        }
        if ($this->parentTaskId !== null) {
            $persistentState["parentTaskId"] = $this->parentTaskId;
        }
        if ($this->delegationState !== null) {
            $persistentState["delegationState"] = $this->delegationState;
        }
        if ($this->tenantId !== null) {
            $persistentState["tenantId"] = $this->tenantId;
        }

        $persistentState["suspensionState"] = $this->suspensionState;

        return $persistentState;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function ensureParentTaskActive(): void
    {
        if ($this->parentTaskId !== null) {
            $parentTask = Context::getCommandContext()
                ->getTaskManager()
                ->findTaskById($this->parentTaskId);

            EnsureUtil::ensureNotNull(\Exception::class, "Parent task with id '" . $this->parentTaskId . "' does not exist", "parentTask", $parentTask);

            if ($parentTask->suspensionState == SuspensionState::suspended()->getStateCode()) {
                //throw LOG.suspendedEntityException("parent task", id);
                throw new \Exception("ensureParentTaskActive");
            }
        }
    }

    protected function ensureTaskActive(): void
    {
        if ($this->suspensionState == SuspensionState::suspended()->getStateCode()) {
            //throw LOG.suspendedEntityException("task", id);
            throw new \Exception("ensureTaskActive");
        }
    }

    public function getBpmnModelElementInstance(): ?UserTaskInterface
    {
        $bpmnModelInstance = $this->getBpmnModelInstance();
        if ($bpmnModelInstance !== null) {
            $modelElementInstance = $bpmnModelInstance->getModelElementById($this->taskDefinitionKey);
            try {
                return $modelElementInstance;
            } catch (\Exception $e) {
                $elementType = $modelElementInstance->getElementType();
                //throw LOG.castModelInstanceException(modelElementInstance, "UserTask", elementType->getTypeName(),
                //    elementType->getTypeNamespace(), e);
                throw $e;
            }
        } else {
            return null;
        }
    }

    public function getBpmnModelInstance(): ?BpmnModelInstanceInterface
    {
        if ($this->processDefinitionId !== null) {
            return Context::getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->findBpmnModelInstanceForProcessDefinition($this->processDefinitionId);
        } else {
            return null;
        }
    }

    // variables ////////////////////////////////////////////////////////////////

    protected function getVariableStore(): VariableStore
    {
        return $this->variableStore;
    }

    protected function getVariableInstanceFactory(): VariableInstanceFactoryInterface
    {
        return VariableInstanceEntityFactory::instance();
    }

    protected function getVariableInstanceLifecycleListeners(): array
    {
        if (empty($this->customLifecycleListeners)) {
            return self::$DEFAULT_VARIABLE_LIFECYCLE_LISTENERS;
        } else {
            $listeners = array_merge([], self::$DEFAULT_VARIABLE_LIFECYCLE_LISTENERS);
            $listeners = array_merge($listeners, $this->customLifecycleListeners);
            return $listeners;
        }
    }

    public function addCustomLifecycleListener(VariableInstanceLifecycleListenerInterface $customLifecycleListener): void
    {
        if ($this->customLifecycleListeners === null) {
            $this->customLifecycleListeners = [];
        }

        $this->customLifecycleListeners[] = $customLifecycleListener;
    }

    public function removeCustomLifecycleListener(
        VariableInstanceLifecycleListenerInterface $customLifecycleListener
    ): VariableInstanceLifecycleListenerInterface {
        if ($this->customLifecycleListeners !== null) {
            foreach ($this->customLifecycleListeners as $key => $listener) {
                if ($listener == $customLifecycleListener) {
                    unset($this->customLifecycleListeners[$key]);
                }
            }
        }

        return $customLifecycleListener;
    }

    public function dispatchEvent(VariableEvent $variableEvent): void
    {
        if ($this->execution !== null && $variableEvent->getVariableInstance()->getTaskId() === null) {
            $this->execution->handleConditionalEventOnVariableChange($variableEvent);
        }
    }

    public function provideVariables(?array $variableNames = null): array
    {
        if (empty($variableNames)) {
            return Context::getCommandContext()
            ->getVariableInstanceManager()
            ->findVariableInstancesByTaskId($this->id);
        } else {
            return Context::getCommandContext()
                ->getVariableInstanceManager()
                ->findVariableInstancesByTaskIdAndVariableNames($this->id, $variableNames);
        }
    }

    public function getParentVariableScope(): ?AbstractVariableScope
    {
        if ($this->getExecution() !== null) {
            return $this->execution;
        }
        /*if ($this->getCaseExecution()!=null) {
            return caseExecution;
        }*/
        if ($this->getParentTask() !== null) {
            return $this->parentTask;
        }
        return null;
    }

    public function getVariableScopeKey(): string
    {
        return "task";
    }

    // execution ////////////////////////////////////////////////////////////////

    public function getParentTask(): TaskEntity
    {
        if ($this->parentTask === null && $this->parentTaskId !== null) {
            $this->parentTask = Context::getCommandContext()
                                    ->getTaskManager()
                                    ->findTaskById($this->parentTaskId);
        }
        return $this->parentTask;
    }

    public function getExecution(): ExecutionEntity
    {
        if (($this->execution === null) && ($this->executionId !== null)) {
            $this->execution = Context::getCommandContext()
            ->getExecutionManager()
            ->findExecutionById($this->executionId);
        }
        return $this->execution;
    }

    public function setExecution(PvmExecutionImpl $execution): void
    {
        if ($this->execution !== null) {
            $this->execution = $execution;
            $this->executionId = $this->execution->getId();
            $this->processInstanceId = $this->execution->getProcessInstanceId();
            $this->processDefinitionId = $this->execution->getProcessDefinitionId();

            // get the process instance
            $instance = $this->execution->getProcessInstance();
            if ($instance !== null) {
                // set case instance id on this task
                //$this->caseInstanceId = instance->getCaseInstanceId();
            }
        } else {
            $this->execution = null;
            $this->executionId = null;
            $this->processInstanceId = null;
            $this->processDefinitionId = null;
            //$this->caseInstanceId = null;
        }
    }

    // case execution ////////////////////////////////////////////////////////////////

    /*public CaseExecutionEntity getCaseExecution() {
        ensureCaseExecutionInitialized();
        return caseExecution;
    }

    protected void ensureCaseExecutionInitialized() {
        if ((caseExecution==null) && (caseExecutionId!=null) ) {
            caseExecution = Context
            ->getCommandContext()
            ->getCaseExecutionManager()
            ->findCaseExecutionById(caseExecutionId);
        }
    }

    public void setCaseExecution(CaseExecutionEntity caseExecution) {
        if (caseExecution!=null) {

            $this->caseExecution = caseExecution;
            $this->caseExecutionId = $this->caseExecution->getId();
            $this->caseInstanceId = $this->caseExecution->getCaseInstanceId();
            $this->caseDefinitionId = $this->caseExecution->getCaseDefinitionId();
            $this->tenantId = $this->caseExecution->getTenantId();

        } else {
            $this->caseExecution = null;
            $this->caseExecutionId = null;
            $this->caseInstanceId = null;
            $this->caseDefinitionId = null;
            $this->tenantId = null;
        }
    }

    public String getCaseExecutionId() {
        return caseExecutionId;
    }

    public void setCaseExecutionId(string $caseExecutionId) {
        $this->caseExecutionId = caseExecutionId;
    }

    public String getCaseInstanceId() {
        return caseInstanceId;
    }

    public void setCaseInstanceId(string $caseInstanceId) {
        registerCommandContextCloseListener();
        propertyChanged(CASE_INSTANCE_ID, $this->caseInstanceId, caseInstanceId);
        $this->caseInstanceId = caseInstanceId;
    }

    public CaseDefinitionEntity getCaseDefinition() {
        if (caseDefinitionId !== null) {
            return Context
                ->getProcessEngineConfiguration()
                ->getDeploymentCache()
                ->findDeployedCaseDefinitionById(caseDefinitionId);
        }
        return null;
    }

    public String getCaseDefinitionId() {
        return caseDefinitionId;
    }

    public void setCaseDefinitionId(string $caseDefinitionId) {
        $this->caseDefinitionId = caseDefinitionId;
    }*/

    // task assignment //////////////////////////////////////////////////////////

    public function addIdentityLink(?string $userId, ?string $groupId, string $type): IdentityLinkEntity
    {
        $this->ensureTaskActive();

        $identityLink = $this->newIdentityLink($userId, $groupId, $type);
        $identityLink->insert();
        $this->getIdentityLinks();
        $this->taskIdentityLinkEntities[] = $identityLink;

        $this->fireAddIdentityLinkAuthorizationProvider($type, $userId, $groupId);
        return $identityLink;
    }

    public function fireIdentityLinkHistoryEvents(string $userId, string $groupId, string $type, HistoryEventTypes $historyEventType): void
    {
        $identityLinkEntity = newIdentityLink($userId, $groupId, $type);
        $this->identityLinkEntity->fireHistoricIdentityLinkEvent($historyEventType);
    }

    public function newIdentityLink(string $userId, string $groupId, string $type): IdentityLinkEntity
    {
        $identityLinkEntity = new IdentityLinkEntity();
        $identityLinkEntity->setTask($this);
        $identityLinkEntity->setUserId($userId);
        $identityLinkEntity->setGroupId($groupId);
        $identityLinkEntity->setType($type);
        $identityLinkEntity->setTenantId($this->getTenantId());
        return $identityLinkEntity;
    }

    public function deleteIdentityLink(?string $userId, ?string $groupId, string $type): void
    {
        $this->ensureTaskActive();

        $identityLinks = Context::getCommandContext()
            ->getIdentityLinkManager()
            ->findIdentityLinkByTaskUserGroupAndType($this->id, $userId, $groupId, $type);

        foreach ($identityLinks as $identityLink) {
            $this->fireDeleteIdentityLinkAuthorizationProvider($type, $userId, $groupId);
            $identityLink->delete();
        }
    }

    public function deleteIdentityLinks(): void
    {
        $identityLinkEntities = $this->getIdentityLinks();
        foreach ($identityLinkEntities as $identityLinkEntity) {
            $this->fireDeleteIdentityLinkAuthorizationProvider(
                $identityLinkEntity->getType(),
                $identityLinkEntity->getUserId(),
                $identityLinkEntity->getGroupId()
            );
            $identityLinkEntity->delete(false);
        }
        $this->isIdentityLinksInitialized = false;
    }

    public function getCandidates(): array
    {
        $potentialOwners = [];
        foreach ($this->getIdentityLinks() as $identityLinkEntity) {
            if (IdentityLinkType::CANDIDATE == $identityLinkEntity->getType()) {
                $potentialOwners[] = $identityLinkEntity;
            }
        }
        return $potentialOwners;
    }

    public function addCandidateUser(string $userId): void
    {
        $this->addIdentityLink($userId, null, IdentityLinkType::CANDIDATE);
    }

    public function addCandidateUsers(array $candidateUsers): void
    {
        foreach ($candidateUsers as $candidateUser) {
            $this->addCandidateUser($candidateUser);
        }
    }

    public function addCandidateGroup(string $groupId): void
    {
        $this->addIdentityLink(null, $groupId, IdentityLinkType::CANDIDATE);
    }

    public function addCandidateGroups(array $candidateGroups): void
    {
        foreach ($candidateGroups as $candidateGroup) {
            $this->addCandidateGroup($candidateGroup);
        }
    }

    public function addGroupIdentityLink(string $groupId, string $identityLinkType): void
    {
        $this->addIdentityLink(null, $groupId, $identityLinkType);
    }

    public function addUserIdentityLink(string $userId, string $identityLinkType): void
    {
        $this->addIdentityLink($userId, null, $identityLinkType);
    }

    public function deleteCandidateGroup(string $groupId): void
    {
        $this->deleteGroupIdentityLink($groupId, IdentityLinkType::CANDIDATE);
    }

    public function deleteCandidateUser(string $userId): void
    {
        $this->deleteUserIdentityLink($userId, IdentityLinkType::CANDIDATE);
    }

    public function deleteGroupIdentityLink(string $groupId, string $identityLinkType): void
    {
        if ($groupId !== null) {
            $this->deleteIdentityLink(null, $groupId, $identityLinkType);
        }
    }

    public function deleteUserIdentityLink(string $userId, string $identityLinkType): void
    {
        if ($userId !== null) {
            $this->deleteIdentityLink($userId, null, $identityLinkType);
        }
    }

    public function getIdentityLinks(): array
    {
        if (!$this->isIdentityLinksInitialized) {
            $this->taskIdentityLinkEntities = Context::getCommandContext()
            ->getIdentityLinkManager()
            ->findIdentityLinksByTaskId(id);
            $this->isIdentityLinksInitialized = true;
        }

        return $this->taskIdentityLinkEntities;
    }

    public function getActivityInstanceVariables(): array
    {
        if ($this->execution !== null) {
            return $execution->getVariables();
        }
        return [];
    }

    public function setExecutionVariables(array $parameters): void
    {
        $scope = $this->getParentVariableScope();
        if ($scope !== null) {
            $scope->setVariables($parameters);
        }
    }

    public function __toString()
    {
        return "Task[" . $this->id . "]";
    }

    // special setters //////////////////////////////////////////////////////////

    public function setName(string $taskName): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::NAME, $this->name, $taskName);
        $this->name = $taskName;
    }

    public function setDescription(string $description): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::DESCRIPTION, $this->description, $description);
        $this->description = $description;
    }

    public function setAssignee(?string $assignee): void
    {
        $timestamp = ClockUtil::getCurrentTime()->format('c');
        $this->ensureTaskActive();
        $this->registerCommandContextCloseListener();

        $oldAssignee = $this->assignee;
        if ($assignee === null && $oldAssignee === null) {
            return;
        }

        $this->addIdentityLinkChanges(IdentityLinkType::ASSIGNEE, $oldAssignee, $assignee);
        $this->propertyChanged(self::ASSIGNEE, $oldAssignee, $assignee);
        $this->assignee = $assignee;

        $commandContext = Context::getCommandContext();
        // if there is no command context, then it means that the user is calling the
        // setAssignee outside a service method.  E.g. while creating a new task.
        if ($commandContext !== null) {
            if ($commandContext->getDbEntityManager()->contains($this)) {
                $this->fireAssigneeAuthorizationProvider($oldAssignee, $assignee);
                $this->fireHistoricIdentityLinks();
            }
            if ($commandContext->getProcessEngineConfiguration()->isTaskMetricsEnabled() && $assignee !== null && $assignee != $oldAssignee) {
                // assignee has changed and is not null, so mark a new task worker
                $commandContext->getMeterLogManager()->insert(new TaskMeterLogEntity($assignee, $timestamp));
            }
        }
    }

    public function setOwner(string $owner): void
    {
        $this->ensureTaskActive();
        $this->registerCommandContextCloseListener();

        $oldOwner = $this->owner;
        if ($this->owner === null && $oldOwner === null) {
            return;
        }

        $this->addIdentityLinkChanges(IdentityLinkType::OWNER, $oldOwner, $this->owner);
        $this->propertyChanged(self::OWNER, $oldOwner, $this->owner);
        $this->owner = $owner;

        $commandContext = Context::getCommandContext();
        // if there is no command context, then it means that the user is calling the
        // setOwner outside a service method.  E.g. while creating a new task.
        if ($commandContext !== null && $commandContext->getDbEntityManager()->contains($this)) {
            $this->fireOwnerAuthorizationProvider($oldOwner, $this->owner);
            $this->fireHistoricIdentityLinks();
        }
    }

    public function setDueDate(string $dueDate): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::DUE_DATE, $this->dueDate, $dueDate);
        $this->dueDate = $dueDate;
    }

    public function setPriority(int $priority): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::PRIORITY, $this->priority, $priority);
        $this->priority = $priority;
    }

    public function setParentTaskId(string $parentTaskId): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::PARENT_TASK, $this->parentTaskId, $parentTaskId);
        $this->parentTaskId = $parentTaskId;
    }

    /* plain setter for persistence */
    public function setNameWithoutCascade(string $taskName): void
    {
        $this->name = $taskName;
    }

    /* plain setter for persistence */
    public function setDescriptionWithoutCascade(string $description): void
    {
        $this->description = $description;
    }

    /* plain setter for persistence */
    public function setAssigneeWithoutCascade(string $assignee): void
    {
        $this->assignee = $assignee;
    }

    /* plain setter for persistence */
    public function setOwnerWithoutCascade(string $owner): void
    {
        $this->owner = $owner;
    }

    public function setDueDateWithoutCascade(string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function setPriorityWithoutCascade(int $priority): void
    {
        $this->priority = $priority;
    }

    /* plain setter for persistence */
    /*public function setCaseInstanceIdWithoutCascade(string $caseInstanceId): void
    {
        $this->caseInstanceId = caseInstanceId;
    }*/

    public function setParentTaskIdWithoutCascade(string $parentTaskId): void
    {
        $this->parentTaskId = $parentTaskId;
    }

    public function setTaskDefinitionKeyWithoutCascade(string $taskDefinitionKey): void
    {
        $this->taskDefinitionKey = $taskDefinitionKey;
    }

    public function setDelegationStateWithoutCascade(string $delegationState): void
    {
        $this->delegationState = $delegationState;
    }

    /**
     * Setter for mybatis mapper.
     *
     * @param delegationState  the delegation state as string
     */
    public function setDelegationStateString(?string $delegationState): void
    {
        if ($delegationState === null) {
            $this->setDelegationStateWithoutCascade(null);
        } else {
            $this->setDelegationStateWithoutCascade($delegationState);
        }
    }

    public function setFollowUpDateWithoutCascade(string $followUpDate): void
    {
        $this->followUpDate = $followUpDate;
    }

    /**
     * @return bool true if invoking the listener was successful;
     *   if not successful, either false is returned (case: BPMN error propagation)
     *   or an exception is thrown
     */
    public function fireEvent(string $taskEventName): bool
    {
        $taskEventListeners = $this->getListenersForEvent($taskEventName);

        if (!empty($taskEventListeners)) {
            foreach ($taskEventListeners as $taskListener) {
                if (!$this->invokeListener($taskEventName, $taskListener)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function getListenersForEvent(string $event): array
    {
        $resolvedTaskDefinition = $this->getTaskDefinition();
        if ($resolvedTaskDefinition !== null) {
            if ($this->skipCustomListeners) {
                return $resolvedTaskDefinition->getBuiltinTaskListeners($event);
            } else {
                return $resolvedTaskDefinition->getTaskListeners($event);
            }
        } else {
            return [];
        }
    }

    protected function getTimeoutListener(string $timeoutId): ?TaskListenerInterface
    {
        $resolvedTaskDefinition = $this->getTaskDefinition();
        if ($resolvedTaskDefinition === null) {
            return null;
        } else {
            return $resolvedTaskDefinition->getTimeoutTaskListener($timeoutId);
        }
    }

    /**
     * @return bool true if the next listener can be invoked; false if not
     */
    protected function invokeListener(?CoreExecution $currentExecution, string $eventName, TaskListenerInterface $taskListener): bool
    {
        if ($currentExecution !== null) {
            $isBpmnTask = $currentExecution instanceof ActivityExecution && $currentExecution !== null;
            $listenerInvocation = new TaskListenerInvocation($taskListener, $this, $currentExecution);

            try {
                Context::getProcessEngineConfiguration()
                ->getDelegateInterceptor()
                ->handleInvocation($listenerInvocation);
            } catch (\Exception $ex) {
                // exceptions on delete events are never handled as BPMN errors
                if ($isBpmnTask && $eventName != TaskListenerInterface::EVENTNAME_DELETE) {
                    try {
                        BpmnExceptionHandler::propagateException($currentExecution, $ex);
                        return false;
                    } catch (ErrorPropagationException $e) {
                        // exception has been logged by thrower
                        // re-throw the original exception so that it is logged
                        // and set as cause of the failure
                        throw $ex;
                    }
                } else {
                    throw $ex;
                }
            }
            return true;
        } else {
            $popProcessDataContext = false;
            $commandInvocationContext = Context::getCommandInvocationContext();
            $execution = $this->getExecution();
            if ($execution === null) {
                //$execution = getCaseExecution();
            } else {
                if ($commandInvocationContext !== null) {
                    $popProcessDataContext = $commandInvocationContext->getProcessDataContext()->pushSection($execution);
                }
            }
            if ($execution !== null) {
                $this->setEventName($eventName);
            }
            try {
                $result = $this->invokeListener($execution, $eventName, $taskListener);
                if ($popProcessDataContext) {
                    $commandInvocationContext->getProcessDataContext()->popSection();
                }
                return $result;
            } catch (\Exception $e) {
                //throw LOG.invokeTaskListenerException(e);
                throw $e;
            }
        }
    }

    /**
     * Tracks a property change. Therefore the original and new value are stored in a map.
     * It tracks multiple changes and if a property finally is changed back to the original
     * value, then the change is removed.
     *
     * @param propertyName
     * @param orgValue
     * @param newValue
     */
    protected function propertyChanged(string $propertyName, $orgValue, $newValue): void
    {
        if (array_key_exists($propertyName, $this->propertyChanges)) {// update an existing change to save the original value
            $oldOrgValue = $this->propertyChanges[$propertyName]->getOrgValue();
            if (
                ($oldOrgValue === null && $newValue === null) // change back to null
                || ($oldOrgValue !== null && $oldOrgValue == $newValue)
            ) { // remove this change
                unset($this->propertyChanges[$propertyName]);
            } else {
                $this->propertyChanges[$propertyName]->setNewValue($newValue);
            }
        } else { // save this change
            if (
                ($orgValue === null && $newValue !== null) // null to value
                || ($orgValue !== null && $newValue === null) // value to null
                || ($orgValue !== null && $orgValue != $newValue)
            ) {
                $this->propertyChanges[$propertyName] = new PropertyChange($propertyName, $orgValue, $newValue);
            }
        }
    }

    // authorizations ///////////////////////////////////////////////////////////

    public function transitionTo(int $state): bool
    {
        $this->lifecycleState = $state;

        switch ($state) {
            case TaskState::STATE_CREATED:
                $commandContext = Context::getCommandContext();
                if ($commandContext !== null) {
                    $commandContext->getHistoricTaskInstanceManager()->createHistoricTask($this);
                }
                return $this->fireEvent(TaskListenerInterface::EVENTNAME_CREATE) && $this->fireAssignmentEvent();

            case TaskState::STATE_COMPLETED:
                return $this->fireEvent(TaskListenerInterface::EVENTNAME_COMPLETE) && TaskState::STATE_COMPLETED == $this->lifecycleState;

            case TaskState::STATE_DELETED:
                return $this->fireEvent(TaskListenerInterface::EVENTNAME_DELETE);

            case TaskState::STATE_INIT:
            default:
                throw new ProcessEngineException(sprintf("Task %s cannot transition into state %s.", $this->id, $state));
        }
    }

    public function triggerUpdateEvent(): bool
    {
        if ($this->lifecycleState == TaskState::STATE_CREATED) {
            return $this->fireEvent(TaskListenerInterface::EVENTNAME_UPDATE) && $this->fireAssignmentEvent();
        } else {
            // silently ignore; no events are triggered in the other states
            return true;
        }
    }

    /**
     * @return bool true if invoking the listener was successful;
     *   if not successful, either false is returned (case: BPMN error propagation)
     *   or an exception is thrown
     */
    public function triggerTimeoutEvent(string $timeoutId): bool
    {
        $taskListener = $this->getTimeoutListener($timeoutId);
        if ($taskListener === null) {
            /* throw LOG.invokeTaskListenerException(new NotFoundException("Cannot find timeout taskListener with id '"
                                                                            + timeoutId + "' for task " + $this->id));*/
            throw new \Exception("triggerTimeoutEvent");
        }
        return $this->invokeListener(TaskListenerInterface::EVENTNAME_TIMEOUT, $taskListener);
    }

    protected function fireAssignmentEvent(): bool
    {
        if (array_key_exists(self::ASSIGNEE, $this->propertyChanges)) {
            $assigneePropertyChange = $this->propertyChanges[self::ASSIGNEE];
            return $this->fireEvent(TaskListenerInterface::EVENTNAME_ASSIGNMENT);
        }

        return true;
    }

    protected function fireAssigneeAuthorizationProvider(string $oldAssignee, string $newAssignee): void
    {
        $this->fireAuthorizationProvider(self::ASSIGNEE, $oldAssignee, $newAssignee);
    }

    protected function fireOwnerAuthorizationProvider(string $oldOwner, string $newOwner): void
    {
        $this->fireAuthorizationProvider(self::OWNER, $oldOwner, $newOwner);
    }

    protected function fireAuthorizationProvider(?string $property = null, ?string $oldValue = null, ?string $newValue = null): void
    {
        if ($property === null && $oldValue === null && $newValue === null) {
            if (array_key_exists(self::ASSIGNEE, $this->propertyChanges)) {
                $assigneePropertyChange = $this->propertyChanges[self::ASSIGNEE];
                $oldAssignee = $assigneePropertyChange->getOrgValueString();
                $newAssignee = $assigneePropertyChange->getNewValueString();
                $this->fireAssigneeAuthorizationProvider($oldAssignee, $newAssignee);
            }

            if (array_key_exists(self::OWNER, $this->propertyChanges)) {
                $assigneePropertyChange = $this->propertyChanges[self::OWNER];
                $oldOwner = $ownerPropertyChange->getOrgValueString();
                $newOwner = $ownerPropertyChange->getNewValueString();
                $this->fireOwnerAuthorizationProvider($oldOwner, $newOwner);
            }
        } else {
            if ($this->isAuthorizationEnabled()) { // && caseExecutionId === null
                $provider = $this->getResourceAuthorizationProvider();

                $authorizations = null;
                if (self::ASSIGNEE == $property) {
                    $authorizations = $provider->newTaskAssignee($this, $oldValue, $newValue);
                } elseif (self::OWNER == $property) {
                    $authorizations = $provider->newTaskOwner($this, $oldValue, $newValue);
                }

                $this->saveAuthorizations($authorizations);
            }
        }
    }

    protected function fireAddIdentityLinkAuthorizationProvider(string $type, string $userId, string $groupId): void
    {
        if ($this->isAuthorizationEnabled()) { // && caseExecutionId === null
            $provider = $this->getResourceAuthorizationProvider();

            $authorizations = null;
            if ($userId !== null) {
                $authorizations = $provider->newTaskUserIdentityLink($this, $userId, $type);
            } elseif ($groupId !== null) {
                $authorizations = $provider->newTaskGroupIdentityLink($this, $groupId, $type);
            }

            $this->saveAuthorizations($authorizations);
        }
    }

    protected function fireDeleteIdentityLinkAuthorizationProvider(string $type, string $userId, string $groupId): void
    {
        if ($this->isAuthorizationEnabled()) { // && caseExecutionId === null
            $provider = $this->getResourceAuthorizationProvider();
            $authorizations = null;
            if ($userId !== null) {
                $authorizations = $provider->deleteTaskUserIdentityLink($this, $userId, $type);
            } elseif ($groupId !== null) {
                $authorizations = $provider->deleteTaskGroupIdentityLink($this, $groupId, $type);
            }

            $this->deleteAuthorizations($authorizations);
        }
    }

    protected function getResourceAuthorizationProvider(): ?ResourceAuthorizationProviderInterface
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        return $processEngineConfiguration->getResourceAuthorizationProvider();
    }

    protected function saveAuthorizations(array $authorizations): void
    {
        $commandContext = Context::getCommandContext();
        $taskManager = $commandContext->getTaskManager();
        $taskManager->saveDefaultAuthorizations($authorizations);
    }

    protected function deleteAuthorizations(array $authorizations): void
    {
        $commandContext = Context::getCommandContext();
        $taskManager = $commandContext->getTaskManager();
        $taskManager->deleteDefaultAuthorizations($authorizations);
    }

    protected function isAuthorizationEnabled(): bool
    {
        return Context::getProcessEngineConfiguration()->isAuthorizationEnabled();
    }

    // modified getters and setters /////////////////////////////////////////////

    public function setTaskDefinition(TaskDefinition $taskDefinition): void
    {
        $this->taskDefinition = $taskDefinition;
        $this->taskDefinitionKey = $taskDefinition->getKey();
    }

    public function getTaskDefinition(): ?TaskDefinition
    {
        if ($this->taskDefinition === null && $this->taskDefinitionKey !== null) {
            $taskDefinitions = null;
            if ($this->processDefinitionId !== null) {
                $processDefinition = Context::getProcessEngineConfiguration()
                    ->getDeploymentCache()
                    ->findDeployedProcessDefinitionById($this->processDefinitionId);

                $taskDefinitions = $processDefinition->getTaskDefinitions();
            } else {
                /*CaseDefinitionEntity caseDefinition = Context
                    ->getProcessEngineConfiguration()
                    ->getDeploymentCache()
                    ->findDeployedCaseDefinitionById(caseDefinitionId);

                taskDefinitions = caseDefinition->getTaskDefinitions();*/
            }

            if ($taskDefinitions !== null) {
                $taskDefinition = $taskDefinitions[$taskDefinitionKey];
            }
        }
        return $taskDefinition;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function isStandaloneTask(): bool
    {
        return $this->executionId === null;// && caseExecutionId === null;
    }

    public function getProcessDefinition(): ProcessDefinitionEntity
    {
        if ($this->processDefinitionId !== null) {
            return Context::getProcessEngineConfiguration()
                ->getDeploymentCache()
                ->findDeployedProcessDefinitionById($processDefinitionId);
        }
        return null;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function initializeFormKey(): void
    {
        $this->isFormKeyInitialized = true;
        if ($this->taskDefinitionKey !== null) {
            $taskDefinition = $this->getTaskDefinition();
            if ($this->taskDefinition !== null) {
                // initialize formKey
                $formKey = $taskDefinition->getFormKey();
                if ($formKey !== null) {
                    $this->formKey = $formKey->getValue($this);
                } else {
                    // initialize form reference
                    $formRef = $taskDefinition->getFormDefinitionKey();
                    $formRefBinding = $taskDefinition->getFormDefinitionBinding();
                    $formRefVersion = $taskDefinition->getFormDefinitionVersion();
                    if ($formRef !== null && $formRefBinding !== null) {
                        $formRefValue = $formRef->getValue($this);
                        if ($formRefValue !== null) {
                            $extFormRef = new FormRefImpl($formRefValue, $formRefBinding);
                            if ($formRefBinding == DefaultFormHandler::FORM_REF_BINDING_VERSION && $formRefVersion !== null) {
                                $formRefVersionValue = $formRefVersion->getValue($this);
                                $extFormRef->setVersion(intval($formRefVersionValue));
                            }
                            $this->formRef = $extFormRef;
                        }
                    }
                }
            }
        }
    }

    public function getFormKey(): string
    {
        if (!$this->isFormKeyInitialized) {
            //throw LOG.uninitializedFormKeyException();
            throw new \Exception("uninitializedFormKeyException");
        }
        return $this->formKey;
    }

    public function getFormRef(): FormRefInterface
    {
        if (!$this->isFormKeyInitialized) {
            //throw LOG.uninitializedFormKeyException();
            throw new \Exception("uninitializedFormKeyException");
        }
        return $this->formRef;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getAssignee(): string
    {
        return $this->assignee;
    }

    public function getTaskDefinitionKey(): string
    {
        return $this->taskDefinitionKey;
    }

    public function setTaskDefinitionKey(string $taskDefinitionKey): void
    {
        if (
            ($taskDefinitionKey === null && $this->taskDefinitionKey !== null)
            || ($taskDefinitionKey !== null && $taskDefinitionKey == $this->taskDefinitionKey)
        ) {
            $this->taskDefinition = null;
            $this->formKey = null;
            $this->isFormKeyInitialized = false;
        }

        $this->taskDefinitionKey = $taskDefinitionKey;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $this->eventName;
    }

    public function setExecutionId(string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getProcessInstance(): ExecutionEntity
    {
        if ($this->processInstance === null && $this->processInstanceId !== null) {
            $this->processInstance = Context::getCommandContext()->getExecutionManager()->findExecutionById($this->processInstanceId);
        }
        return $this->processInstance;
    }

    public function setProcessInstance(ExecutionEntity $processInstance): void
    {
        $this->processInstance = $processInstance;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getDelegationState(): string
    {
        return $this->delegationState;
    }

    public function setDelegationState(string $delegationState): void
    {
        $this->propertyChanged(self::DELEGATION, $this->delegationState, $delegationState);
        $this->delegationState = $delegationState;
    }

    public function getDelegationStateString(): ?string
    {
        return strval($this->delegationState);
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getDeleteReason(): string
    {
        return $this->deleteReason;
    }

    public function setDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getParentTaskId(): ?string
    {
        return $this->parentTaskId;
    }

    public function getSuspensionState(): int
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(int $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function isSuspended(): bool
    {
        return $this->suspensionState == SuspensionState::suspended()->getStateCode();
    }

    public function getFollowUpDate(): string
    {
        return $this->followUpDate;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function setFollowUpDate(string $followUpDate): void
    {
        $this->registerCommandContextCloseListener();
        $this->propertyChanged(self::FOLLOW_UP_DATE, $this->followUpDate, $followUpDate);
        $this->followUpDate = $followUpDate;
    }

    public function getVariablesInternal(): array
    {
        return $this->variableStore->getVariables();
    }

    public function onCommandContextClose(CommandContext $commandContext): void
    {
        if ($commandContext->getDbEntityManager()->isDirty($this)) {
            $commandContext->getHistoricTaskInstanceManager()->updateHistoricTaskInstance($this);
        }
    }

    public function onCommandFailed(CommandContext $commandContext, \Throwable $t): void
    {
        // ignore
    }

    protected function registerCommandContextCloseListener(): void
    {
        $commandContext = Context::getCommandContext();
        if ($commandContext !== null) {
            $commandContext->registerCommandContextListener($this);
        }
    }

    public function getPropertyChanges(): array
    {
        return $this->propertyChanges;
    }

    public function logUserOperation(string $operation): void
    {
        if (
            UserOperationLogEntryInterface::OPERATION_TYPE_COMPLETE == $operation ||
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE == $operation
        ) {
            propertyChanged(self::DELETE, false, true);
        }

        $commandContext = Context::getCommandContext();
        if ($commandContext !== null) {
            $values = array_values($this->propertyChanges);
            $commandContext->getOperationLogManager()->logTaskOperations($operation, $this, $values);
            $this->fireHistoricIdentityLinks();
            $this->propertyChanges = [];
        }
    }

    public function fireHistoricIdentityLinks(): void
    {
        foreach ($this->identityLinkChanges as $propertyChange) {
            $oldValue = $propertyChange->getOrgValueString();
            $propertyName = $propertyChange->getPropertyName();
            if ($oldValue !== null) {
                $this->fireIdentityLinkHistoryEvents($oldValue, null, $propertyName, HistoryEventTypes::identityLinkDelete());
            }
            $newValue = $propertyChange->getNewValueString();
            if ($newValue !== null) {
                $this->fireIdentityLinkHistoryEvents($newValue, null, $propertyName, HistoryEventTypes::identityLinkAdd());
            }
        }
        $this->identityLinkChanges = [];
    }

    public function getProcessEngineServices(): ProcessEngineServicesInterface
    {
        return Context::getProcessEngineConfiguration()
                ->getProcessEngine();
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        return Context::getProcessEngineConfiguration()->getProcessEngine();
    }

    public function equals($obj = null): bool
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj === null) {
            return false;
        }
        if (get_class($this) != get_class($obj)) {
            return false;
        }
        if ($this->id === null) {
            if ($obj->id !== null) {
                return false;
            }
        } elseif ($this->id != $obj->id) {
            return false;
        }
        return true;
    }

    public function executeMetrics(string $metricsName, CommandContext $commandContext): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if (Metrics::ACTIVTY_INSTANCE_START == $metricsName && $processEngineConfiguration->isMetricsEnabled()) {
            $processEngineConfiguration->getMetricsRegistry()->markOccurrence(Metrics::ACTIVTY_INSTANCE_START);
        }
        if (
            Metrics::UNIQUE_TASK_WORKERS == $metricsName && $processEngineConfiguration->isTaskMetricsEnabled() &&
            $assignee !== null && array_key_exists(self::ASSIGNEE, $propertyChanges)
        ) {
            // assignee has changed and is not null, so mark a new task worker
            $commandContext->getMeterLogManager()->insert(new TaskMeterLogEntity($assignee, ClockUtil::getCurrentTime()->format('c')));
        }
    }

    public function addIdentityLinkChanges(string $type, string $oldProperty, string $newProperty): void
    {
        $this->identityLinkChanges[] = new PropertyChange($type, $oldProperty, $newProperty);
    }

    public function setVariablesLocal(array $variables, ?bool $skipSerializationFormatCheck = null): void
    {
        parent::setVariablesLocal($variables, $skipSerializationFormatCheck);
        Context::getCommandContext()->getDbEntityManager()->forceUpdate($this);
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->processDefinitionId !== null) {
            $referenceIdAndClass[$this->processDefinitionId] = ProcessDefinitionEntity::class;
        }
        if ($this->processInstanceId !== null) {
            $referenceIdAndClass[$this->processInstanceId] = ExecutionEntity::class;
        }
        if ($this->executionId !== null) {
            $referenceIdAndClass[$this->executionId] = ExecutionEntity::class;
        }
        /*if (caseDefinitionId !== null) {
            referenceIdAndClass.put(caseDefinitionId, CaseDefinitionEntity::class);
        }
        if (caseExecutionId !== null) {
            referenceIdAndClass.put(caseExecutionId, CaseExecutionEntity::class);
        }*/

        return $referenceIdAndClass;
    }

    public function bpmnError(string $errorCode, ?string $errorMessage, ?array $variables = null): void
    {
        $this->ensureTaskActive();
        $activityExecution = $this->getExecution();
        $bpmnError = null;
        if ($errorMessage !== null) {
            $bpmnError = new BpmnError($errorCode, $errorMessage);
        } else {
            $bpmnError = new BpmnError($errorCode);
        }
        try {
            if (!empty($variables)) {
                $activityExecution->setVariables($variables);
            }
            BpmnExceptionHandler::propagateBpmnError($bpmnError, $activityExecution);
        } catch (\Exception $ex) {
            //throw ProcessEngineLogger.CMD_LOGGER.exceptionBpmnErrorPropagationFailed(errorCode, ex);
            throw $ex;
        }
    }

    public function escalation(string $escalationCode, ?array $variables = null): void
    {
        $this->ensureTaskActive();
        $activityExecution = $this->getExecution();

        if (!empty($variables)) {
            $activityExecution->setVariables($variables);
        }
        EscalationHandler::propagateEscalation($activityExecution, $escalationCode);
    }
}
