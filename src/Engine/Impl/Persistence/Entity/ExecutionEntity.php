<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\{
    ProcessEngineInterface,
    ProcessEngineServicesInterface
};
use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Parser\{
    BpmnParse,
    EventSubscriptionDeclaration
};
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cfg\Multitenancy\{
    TenantIdProviderInterface,
    TenantIdProviderProcessInstanceContext
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;
use Jabe\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Core\Variable\Scope\{
    VariableCollectionProvider,
    VariableInstanceFactoryInterface,
    VariableInstanceLifecycleListenerInterface,
    VariableListenerInvocationListener,
    VariableStore,
    VariablesProviderInterface
};
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface,
};
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\History\AbstractHistoryLevel;
use Jabe\Engine\Impl\History\Event\{
    HistoricVariableUpdateEventEntity,
    HistoryEvent,
    HistoryEventProcessor,
    HistoryEventCreator,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Engine\Impl\Incident\{
    IncidentContext,
    IncidentHandling
};
use Jabe\Engine\Impl\Interceptor\AtomicOperationInvocation;
use Jabe\Engine\Impl\JobExecutor\{
    MessageJobDeclaration,
    TimerDeclarationImpl
};
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmProcessDefinitionInterface
};
use Jabe\Engine\Impl\Pvm\Delegate\CompositeActivityBehaviorInterface;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Pvm\Runtime\{
    ActivityInstanceState,
    AtomicOperation,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Tree\{
    ExecutionTopDownWalker,
    TreeVisitorInterface
};
use Jabe\Engine\Impl\Util\{
    BitMaskUtil,
    CollectionUtil
};
use Jabe\Engine\Impl\Variable\VariableDeclaration;
use Jabe\Engine\Repository\ProcessDefinitionInterface;
use Jabe\Engine\Runtime\{
    ExecutionInterface,
    JobInterface,
    ProcessInstanceInterface
};
use Jabe\Engine\Variable\{
    VariableMapInterface,
    Variables
};
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\FlowElementInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;
use Jabe\Model\Xml\Type\ModelElementTypeInterface;

class ExecutionEntity extends PvmExecutionImpl implements ExecutionInterface, ProcessInstanceInterface, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface, VariablesProviderInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    // Persistent refrenced entities state //////////////////////////////////////
    public const EVENT_SUBSCRIPTIONS_STATE_BIT = 1;
    public const TASKS_STATE_BIT = 2;
    public const JOBS_STATE_BIT = 3;
    public const INCIDENT_STATE_BIT = 4;
    public const VARIABLES_STATE_BIT = 5;
    public const SUB_PROCESS_INSTANCE_STATE_BIT = 6;
    public const SUB_CASE_INSTANCE_STATE_BIT = 7;
    public const EXTERNAL_TASKS_BIT = 8;

    // current position /////////////////////////////////////////////////////////

    /**
     * the process instance. this is the root of the execution tree. the
     * processInstance of a process instance is a self reference.
     */
    protected $processInstance;

    /** the parent execution */
    protected $parent;

    /** nested executions representing scopes or concurrent paths */
    protected $executions = [];

    /** super execution, not-null if this execution is part of a subprocess */
    protected $superExecution;

    /**
     * super case execution, not-null if this execution is part of a case
     * execution
     */
    //protected $superCaseExecution;

    /**
     * reference to a subprocessinstance, not-null if currently subprocess is
     * started from this execution
     */
    protected $subProcessInstance;

    /**
     * reference to a subcaseinstance, not-null if currently subcase is started
     * from this execution
     */
    //protected $subCaseInstance;

    protected $shouldQueryForSubprocessInstance = false;

    //protected $shouldQueryForSubCaseInstance = false;

    // associated entities /////////////////////////////////////////////////////

    // (we cache associated entities here to minimize db queries)
    protected $eventSubscriptions;
    protected $jobs;
    protected $tasks;
    protected $externalTasks;
    protected $incidents;
    protected $cachedEntityState;

    protected $variableStore;


    // replaced by //////////////////////////////////////////////////////////////

    protected $suspensionState;

    // Persistence //////////////////////////////////////////////////////////////

    protected $revision = 1;

    /**
     * persisted reference to the processDefinition.
     *
     * @see #processDefinition
     * @see #setProcessDefinition(ProcessDefinitionImpl)
     * @see #getProcessDefinition()
     */
    protected $processDefinitionId;

    /**
     * persisted reference to the current position in the diagram within the
     * {@link #processDefinition}.
     *
     * @see #activity
     * @see #getActivity()
     */
    protected $activityId;

    /**
     * The name of the current activity position
     */
    protected $activityName;

    /**
     * persisted reference to the process instance.
     *
     * @see #getProcessInstance()
     */
    protected $processInstanceId;

    /**
     * persisted reference to the parent of this execution.
     *
     * @see #getParent()
     */
    protected $parentId;

    /**
     * persisted reference to the super execution of this execution
     *
     * @See {@link #getSuperExecution()}
     * @see <code>setSuperExecution(ExecutionEntity)</code>
     */
    protected $superExecutionId;

    /**
     * persisted reference to the root process instance.
     *
     * @see #getRootProcessInstanceId()
     */
    protected $rootProcessInstanceId;

    /**
     * persisted reference to the super case execution of this execution
     *
     * @See {@link #getSuperCaseExecution()}
     * @see <code>setSuperCaseExecution(ExecutionEntity)</code>
     */
    protected $superCaseExecutionId;

    /**
     * Contains observers which are observe the execution.
     * @since 7.6
     */
    protected $executionObservers = [];

    protected $registeredVariableListeners = [];

    public function __construct()
    {
        $this->variableStore = new VariableStore($this, new ExecutionEntityReferencer($this));
        $this->suspensionState = SuspensionState::active()->getStateCode();
    }

    /**
     * creates a new execution. properties processDefinition, processInstance and
     * activity will be initialized.
     */
    public function createExecution(): ExecutionEntity
    {
        // create the new child execution
        $createdExecution = self::createNewExecution();

        // initialize sequence counter
        $createdExecution->setSequenceCounter($this->getSequenceCounter());

        // manage the bidirectional parent-child relation
        $createdExecution->setParent($this);

        // initialize the new execution
        $createdExecution->setProcessDefinition($this->getProcessDefinition());
        $createdExecution->setProcessInstance($this->getProcessInstance());
        $createdExecution->setActivity($this->getActivity());
        $createdExecution->setSuspensionState($this->getSuspensionState());

        // make created execution start in same activity instance
        $createdExecution->activityInstanceId = $activityInstanceId;

        // inherit the tenant id from parent execution
        if ($this->tenantId != null) {
            $createdExecution->setTenantId($this->tenantId);
        }

        // with the fix of CAM-9249 we presume that the parent and the child have the same startContext
        $createdExecution->setStartContext($this->scopeInstantiationContext);

        $createdExecution->skipCustomListeners = $this->skipCustomListeners;
        $createdExecution->skipIoMapping = $this->skipIoMapping;

        //LOG.createChildExecution(createdExecution, this);

        return $createdExecution;
    }

    // sub process instance
    // /////////////////////////////////////////////////////////////

    public function createSubProcessInstance(PvmProcessDefinitionInterface $processDefinition, ?string $businessKey = null, ?string $caseInstanceId = null): ExecutionEntity
    {
        $this->shouldQueryForSubprocessInstance = true;

        $subProcessInstance = parent::createSubProcessInstance($processDefinition, $businessKey, $caseInstanceId);

        // inherit the tenant-id from the process definition
        $tenantId = $processDefinition->getTenantId();
        if ($tenantId != null) {
            $subProcessInstance->setTenantId($tenantId);
        } else {
            // if process definition has no tenant id, inherit this process instance's tenant id
            $subProcessInstance->setTenantId($this->tenantId);
        }

        $this->fireHistoricActivityInstanceUpdate();

        return $subProcessInstance;
    }

    protected static function createNewExecution(): ExecutionEntity
    {
        $newExecution = new ExecutionEntity();
        self::initializeAssociations($newExecution);
        $newExecution->insert();

        return $newExecution;
    }

    protected function newExecution(): PvmExecutionImpl
    {
        return self::createNewExecution();
    }

    // sub case instance ////////////////////////////////////////////////////////

    /*public CaseExecutionEntity createSubCaseInstance(CmmnCaseDefinition caseDefinition) {
        return createSubCaseInstance(caseDefinition, null);
    }*/

    /*@Override
    public CaseExecutionEntity createSubCaseInstance(CmmnCaseDefinition caseDefinition, String businessKey) {
        CaseExecutionEntity subCaseInstance = (CaseExecutionEntity) caseDefinition.createCaseInstance(businessKey);

        // inherit the tenant-id from the case definition
        String tenantId = ((CaseDefinitionEntity) caseDefinition)->getTenantId();
        if (tenantId != null) {
        subCaseInstance->setTenantId(tenantId);
        }
        else {
        // if case definition has no tenant id, inherit this process instance's tenant id
        subCaseInstance->setTenantId($this->tenantId);
        }

        // manage bidirectional super-process-sub-case-instances relation
        subCaseInstance->setSuperExecution($this);
        setSubCaseInstance(subCaseInstance);

        fireHistoricActivityInstanceUpdate();

        return subCaseInstance;
    }*/

    // helper ///////////////////////////////////////////////////////////////////

    public function fireHistoricActivityInstanceUpdate(): void
    {
        $configuration = Context::getProcessEngineConfiguration();
        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceUpdate(), $this)) {
        // publish update event for current activity instance (containing the id
        // of the sub process/case)
            $scope = $this;
            HistoryEventProcessor::processHistoryEvents(new class ($scope) extends HistoryEventCreator {
                private $scope;

                public function __construct(ExecutionEntity $scope)
                {
                    $this->scope = $scope;
                }

                public function createHistoryEvent(HistoryEventProducer $producer): HistoryEvent
                {
                    return $producer->createActivityInstanceUpdateEvt($this->scope);
                }
            });
        }
    }

    // scopes ///////////////////////////////////////////////////////////////////

    public function initialize(): void
    {
        //LOG.initializeExecution($this);

        $scope = $this->getScopeActivity();
        $this->ensureParentInitialized();

        $variableDeclarations = $scope->getProperty(BpmnParse::PROPERTYNAME_VARIABLE_DECLARATIONS);
        if ($variableDeclarations != null) {
            foreach ($variableDeclarations as $variableDeclaration) {
                $variableDeclaration->initialize($this, $this->parent);
            }
        }

        if ($this->isProcessInstanceExecution()) {
            $initiatorVariableName = $this->processDefinition->getProperty(BpmnParse::PROPERTYNAME_INITIATOR_VARIABLE_NAME);
            if ($initiatorVariableName != null) {
                $authenticatedUserId = Context::getCommandContext()->getAuthenticatedUserId();
                $this->setVariable($initiatorVariableName, $authenticatedUserId);
            }
        }

        // create event subscriptions for the current scope
        foreach (EventSubscriptionDeclaration::getDeclarationsForScope($scope) as $key => $declaration) {
            if (!$declaration->isStartEvent()) {
                $declaration->createSubscriptionForExecution($this);
            }
        }
    }

    public function initializeTimerDeclarations(): void
    {
        //LOG.initializeTimerDeclaration($this);
        $scope = $this->getScopeActivity();
        $this->createTimerInstances(array_values(TimerDeclarationImpl::getDeclarationsForScope($scope)));
        foreach (TimerDeclarationImpl::getTimeoutListenerDeclarationsForScope($scope) as $key => $timerDeclarations) {
            $this->createTimerInstances(array_values($timerDeclarations));
        }
    }

    protected function createTimerInstances(array $timerDeclarations): void
    {
        foreach ($timerDeclarations as $timerDeclaration) {
            $timerDeclaration->createTimerInstance($this);
        }
    }

    protected static function initializeAssociations(ExecutionEntity $execution): void
    {
        // initialize the lists of referenced objects (prevents db queries)
        $execution->executions = [];
        $execution->variableStore->setVariablesProvider(VariableCollectionProvider::emptyVariables());
        $execution->variableStore->forceInitialization();
        $execution->eventSubscriptions = [];
        $execution->jobs = [];
        $execution->tasks = [];
        $execution->externalTasks = [];
        $execution->incidents = [];

        // Cached entity-state initialized to null, all bits are zero, indicating NO
        // entities present
        $execution->cachedEntityState = 0;
    }

    public function start(array $variables, VariableMapInterface $formProperties): void
    {
        if ($this->getSuperExecution() == null) {
            $this->setRootProcessInstanceId($this->processInstanceId);
        } else {
            $superExecution = $this->getSuperExecution();
            $this->setRootProcessInstanceId($superExecution->getRootProcessInstanceId());
        }

        // determine tenant Id if null
        $this->provideTenantId($variables, $formProperties);
        parent::start($variables, $formProperties);
    }

    public function startWithoutExecuting(array $variables): void
    {
        $this->setRootProcessInstanceId($this->getProcessInstanceId());
        $this->provideTenantId($variables, null);
        parent::startWithoutExecuting($variables);
    }

    protected function provideTenantId(array $variables, ?VariableMap $properties = null): void
    {
        if ($this->tenantId == null) {
            $tenantIdProvider = Context::getProcessEngineConfiguration()->getTenantIdProvider();

            if ($tenantIdProvider != null) {
                $variableMap = Variables::fromMap($variables);
                if ($properties != null && !$properties->isEmpty()) {
                    $variableMap->putAll($properties);
                }

                $processDefinition = $this->getProcessDefinition();

                $ctx = null;
                if ($this->superExecutionId != null) {
                    $ctx = new TenantIdProviderProcessInstanceContext($processDefinition, $variableMap, $this->getSuperExecution());
                } else {
                    $ctx = new TenantIdProviderProcessInstanceContext($processDefinition, $variableMap);
                }
                /*elseif ($this->superCaseExecutionId != null) { ctx = new TenantIdProviderProcessInstanceContext(processDefinition, variableMap, getSuperCaseExecution());
                } */

                $this->tenantId = $tenantIdProvider->provideTenantIdForProcessInstance($ctx);
            }
        }
    }

    public function fireHistoricProcessStartEvent(): void
    {
        $configuration = Context::getProcessEngineConfiguration();
        $historyLevel = $configuration->getHistoryLevel();
        // TODO: This smells bad, as the rest of the history is done via the
        // ParseListener
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceStart(), $this->processInstance)) {
            HistoryEventProcessor::processHistoryEvents(new class ($processInstance) extends HistoryEventCreator {
                private $processInstance;

                public function __construct($processInstance)
                {
                    $this->processInstance = $processInstance;
                }

                public function createHistoryEvent(HistoryEventProducer $producer): HistoryEvent
                {
                    return $producer->createProcessInstanceStartEvt($this->processInstance);
                }
            });
        }
    }

    /**
     * Method used for destroying a scope in a way that the execution can be
     * removed afterwards.
     */
    public function destroy(): void
    {
        $this->ensureParentInitialized();

        // execute Output Mappings (if they exist).
        $this->ensureActivityInitialized();
        if ($this->activity != null && $this->activity->getIoMapping() != null && !$this->skipIoMapping) {
            $this->activity->getIoMapping()->executeOutputParameters($this);
        }

        $this->clearExecution();

        parent::destroy();

        $this->removeEventSubscriptionsExceptCompensation();
    }

    public function removeAllTasks(): void
    {
        // delete all the tasks
        $this->removeTasks(null);

        // delete external tasks
        $this->removeExternalTasks();
    }

    protected function clearExecution(): void
    {
        //call the onRemove method of the execution observers
        //so they can do some clean up before
        foreach ($this->executionObservers as $observer) {
            $observer->onClear($this);
        }

        // delete all the tasks and external tasks
        $this->removeAllTasks();

        // delete all the variable instances
        $this->removeVariablesLocalInternal();

        // remove all jobs
        $this->removeJobs();

        // remove all incidents
        $this->removeIncidents();
    }

    public function removeVariablesLocalInternal(): void
    {
        foreach ($this->variableStore->getVariables() as $variableInstance) {
            $this->invokeVariableLifecycleListenersDelete(
                $variableInstance,
                $this,
                [$this->getVariablePersistenceListener()]
            );
            $this->removeVariableInternal($variableInstance);
        }
    }

    public function interrupt(string $reason, bool $skipCustomListeners, bool $skipIoMappings, bool $externallyTerminated): void
    {

        // remove Jobs
        if ($this->preserveScope) {
            $this->removeActivityJobs($reason);
        } else {
            $this->removeJobs();
            $this->removeEventSubscriptionsExceptCompensation();
        }

        $this->removeTasks($reason);

        parent::interrupt($reason, $skipCustomListeners, $skipIoMappings, $externallyTerminated);
    }

    protected function removeActivityJobs(string $reason): void
    {
        if ($this->activityId != null) {
            foreach ($this->getJobs() as $job) {
                if ($this->activityId == $job->getActivityId()) {
                    $job->delete();
                    $this->removeJob($job);
                }
            }
        }
    }

    // methods that translate to operations /////////////////////////////////////

    public function performOperation($operation): void
    {
        if ($operation instanceof AtomicOperation) {
            $async = !$this->isIgnoreAsync() && $this->executionOperation->isAsync($this);

            if (!$async && $this->requiresUnsuspendedExecution($this->executionOperation)) {
                $this->ensureNotSuspended();
            }

            Context::getCommandInvocationContext()
            ->performOperation($this->executionOperation, $this, $async);
        } else {
            parent::performOperation($operation);
        }
    }

    public function performOperationSync($operation): void
    {
        if ($operation instanceof AtomicOperation) {
            if ($this->requiresUnsuspendedExecution($this->executionOperation)) {
                $this->ensureNotSuspended();
            }
            Context::getCommandInvocationContext()->performOperation($this->executionOperation, $this);
        } else {
            parent::performOperationSync($operation);
        }
    }

    protected function ensureNotSuspended(): void
    {
        if ($this->isSuspended()) {
            //throw LOG.suspendedEntityException("Execution", id);
            throw new \Exception("Execution");
        }
    }

    protected function requiresUnsuspendedExecution(AtomicOperation $executionOperation): bool
    {
        if (
            $executionOperation != AtomicOperation::trasitionDestroyScope()
            && $executionOperation != AtomicOperation::transitionNotifyListenerTake()
            && $executionOperation != AtomicOperation::transitionNotifyListenerEnd()
            && $executionOperation != AtomicOperation::transitionCreateScope()
            && $executionOperation != AtomicOperation::transitionNotifyListenerStart()
            && $executionOperation != AtomicOperation::deleteCascade()
            && $executionOperation != AtomicOperation::deleteCascadeFireActivityEnd()
        ) {
            return true;
        }

        return false;
    }

    public function scheduleAtomicOperationAsync(AtomicOperationInvocation $executionOperationInvocation): void
    {
        $messageJobDeclaration = null;

        $messageJobDeclarations = $this->getActivity()->getProperty(BpmnParse::PROPERTYNAME_MESSAGE_JOB_DECLARATION);
        if (!empty($messageJobDeclarations)) {
            foreach ($messageJobDeclarations as $declaration) {
                if ($declaration->isApplicableForOperation($executionOperationInvocation->getOperation())) {
                    $messageJobDeclaration = $declaration;
                    break;
                }
            }
        }

        if ($messageJobDeclaration != null) {
            $message = $messageJobDeclaration->createJobInstance($executionOperationInvocation);
            Context::getCommandContext()->getJobManager()->send($message);
        } else {
            //throw LOG.requiredAsyncContinuationException($this->getActivity()->getId());
        }
    }

    public function isActive(string $activityId): bool
    {
        return $this->findExecution($activityId) != null;
    }

    public function inactivate(): void
    {
        $this->isActive = false;
    }

    // executions ///////////////////////////////////////////////////////////////

    public function addExecutionObserver(ExecutionObserverInterface $observer): void
    {
        $this->executionObservers[$observer];
    }

    public function removeExecutionObserver(ExecutionObserverInterface $observer): void
    {
        foreach ($this->executionObservers as $key => $value) {
            if ($value == $observer) {
                unset($this->executionObservers[$key]);
            }
        }
    }

    public function getExecutions(): array
    {
        $this->ensureExecutionsInitialized();
        return $this->executions;
    }

    public function getExecutionsAsCopy(): array
    {
        return $this->getExecutions();
    }

    protected function ensureExecutionsInitialized(): void
    {
        if (empty($this->executions)) {
            if ($this->isExecutionTreePrefetchEnabled()) {
                $this->ensureExecutionTreeInitialized();
            } else {
                $this->executions = Context::getCommandContext()->getExecutionManager()->findChildExecutionsByParentExecutionId($this->id);
            }
        }
    }

    /**
     * @return true if execution tree prefetching is enabled
     */
    protected function isExecutionTreePrefetchEnabled(): bool
    {
        return Context::getProcessEngineConfiguration()->isExecutionTreePrefetchEnabled();
    }

    public function setExecutions(array $executions): void
    {
        $this->executions = $executions;
    }

    // bussiness key ////////////////////////////////////////////////////////////

    public function getProcessBusinessKey(): ?string
    {
        return $this->getProcessInstance()->getBusinessKey();
    }

    // process definition ///////////////////////////////////////////////////////

    /** ensures initialization and returns the process definition. */
    public function getProcessDefinition(): ProcessDefinitionEntity
    {
        $this->ensureProcessDefinitionInitialized();
        return $this->processDefinition;
    }

    public function setProcessDefinitionId(string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    /**
     * for setting the process definition, this setter must be used as subclasses
     * can override
     */
    protected function ensureProcessDefinitionInitialized(): void
    {
        if (($this->processDefinition == null) && ($this->processDefinitionId != null)) {
            $deployedProcessDefinition = Context::getProcessEngineConfiguration()->getDeploymentCache()
                ->findDeployedProcessDefinitionById($processDefinitionId);
            $this->setProcessDefinition($deployedProcessDefinition);
        }
    }

    public function setProcessDefinition(?ProcessDefinitionImpl $processDefinition): void
    {
        $this->processDefinition = $processDefinition;
        if ($processDefinition != null) {
            $this->processDefinitionId = $processDefinition->getId();
        } else {
            $this->processDefinitionId = null;
        }
    }

    // process instance /////////////////////////////////////////////////////////

    /** ensures initialization and returns the process instance. */
    public function getProcessInstance(): ExecutionEntity
    {
        $this->ensureProcessInstanceInitialized();
        return $this->processInstance;
    }

    protected function ensureProcessInstanceInitialized(): void
    {
        if (($this->processInstance == null) && ($this->processInstanceId != null)) {
            if ($this->id == $this->processInstanceId) {
                $this->processInstance = $this;
            } else {
                if ($this->isExecutionTreePrefetchEnabled()) {
                    $this->ensureExecutionTreeInitialized();
                } else {
                    $this->processInstance = Context::getCommandContext()->getExecutionManager()->findExecutionById($this->processInstanceId);
                }
            }
        }
    }

    public function setProcessInstance(PvmExecutionImpl $processInstance): void
    {
        $this->processInstance = $processInstance;
        if ($processInstance != null) {
            $this->processInstanceId = $this->processInstance->getId();
        }
    }

    public function isProcessInstanceExecution(): bool
    {
        return $this->parentId == null;
    }

    public function isProcessInstanceStarting(): bool
    {
        // the process instance can only be starting if it is currently in main-memory already
        // we never have to access the database
        return $this->processInstance != null && $this->processInstance->isStarting;
    }

    // activity /////////////////////////////////////////////////////////////////

    /** ensures initialization and returns the activity */
    public function getActivity(): ActivityImpl
    {
        $this->ensureActivityInitialized();
        return parent::getActivity();
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    /** must be called before the activity member field or getActivity() is called */
    protected function ensureActivityInitialized(): void
    {
        if (($this->activity == null) && ($this->activityId != null)) {
            $this->setActivity($this->getProcessDefinition()->findActivity($this->activityId));
        }
    }

    public function setActivity(?PvmActivityInterface $activity = null): void
    {
        parent::setActivity($activity);
        if ($activity != null) {
            $this->activityId = $activity->getId();
            $this->activityName = $activity->getProperty("name");
        } else {
            $this->activityId = null;
            $this->activityName = null;
        }
    }

    /**
     * generates an activity instance id
     */
    protected function generateActivityInstanceId(string $activityId): string
    {

        if ($activityId == $this->processDefinitionId) {
            return $this->processInstanceId;
        } else {
            $nextId = Context::getProcessEngineConfiguration()->getIdGenerator()->getNextId();

            $compositeId = $activityId . ":" . $nextId;
            if (count($compositeId) > 64) {
                return $nextId;
            } else {
                return $compositeId;
            }
        }
    }

    // parent ///////////////////////////////////////////////////////////////////

    /** ensures initialization and returns the parent */
    public function getParent(): ?ExecutionEntity
    {
        $this->ensureParentInitialized();
        return $this->parent;
    }

    protected function ensureParentInitialized(): void
    {
        if ($this->parent == null && $this->parentId != null) {
            if ($this->isExecutionTreePrefetchEnabled()) {
                $this->ensureExecutionTreeInitialized();
            } else {
                $this->parent = Context::getCommandContext()->getExecutionManager()->findExecutionById($this->parentId);
            }
        }
    }

    public function setParentExecution(PvmExecutionImpl $parent): void
    {
        $this->parent = $parent;
        if ($parent != null) {
            $this->parentId = $parent->getId();
        } else {
            $this->parentId = null;
        }
    }

    // super- and subprocess executions /////////////////////////////////////////

    public function getSuperExecutionId(): ?string
    {
        return $this->superExecutionId;
    }

    public function getSuperExecution(): ?ExecutionEntity
    {
        $this->ensureSuperExecutionInitialized();
        return $this->superExecution;
    }

    public function setSuperExecution(PvmExecutionImpl $superExecution): void
    {
        if ($this->superExecutionId != null) {
            $this->ensureSuperExecutionInitialized();
            $this->superExecution->setSubProcessInstance(null);
        }

        $this->superExecution = $superExecution;

        if ($superExecution != null) {
            $this->superExecutionId = $superExecution->getId();
            $this->superExecution->setSubProcessInstance($this);
        } else {
            $this->superExecutionId = null;
        }
    }

    protected function ensureSuperExecutionInitialized(): void
    {
        if ($this->superExecution == null && $this->superExecutionId != null) {
            $this->superExecution = Context::getCommandContext()->getExecutionManager()->findExecutionById($this->superExecutionId);
        }
    }

    public function getSubProcessInstance(): ?ExecutionEntity
    {
        $this->ensureSubProcessInstanceInitialized();
        return $this->subProcessInstance;
    }

    public function setSubProcessInstance(PvmExecutionImpl $subProcessInstance): void
    {
        $this->shouldQueryForSubprocessInstance = $subProcessInstance != null;
        $this->subProcessInstance = $subProcessInstance;
    }

    protected function ensureSubProcessInstanceInitialized(): void
    {
        if ($this->shouldQueryForSubprocessInstance && $this->subProcessInstance == null) {
            $this->subProcessInstance = Context::getCommandContext()->getExecutionManager()->findSubProcessInstanceBySuperExecutionId($this->id);
        }
    }

    // super case executions ///////////////////////////////////////////////////

    /*public function getSuperCaseExecutionId(): ?string
    {
        return superCaseExecutionId;
    }

    public void setSuperCaseExecutionId(String superCaseExecutionId) {
        $this->superCaseExecutionId = superCaseExecutionId;
    }

    @Override
    public CaseExecutionEntity getSuperCaseExecution() {
        ensureSuperCaseExecutionInitialized();
        return superCaseExecution;
    }

    @Override
    public void setSuperCaseExecution(CmmnExecution superCaseExecution) {
        $this->superCaseExecution = (CaseExecutionEntity) superCaseExecution;

        if (superCaseExecution != null) {
        $this->superCaseExecutionId = superCaseExecution->getId();
        $this->caseInstanceId = superCaseExecution->getCaseInstanceId();
        } else {
        $this->superCaseExecutionId = null;
        $this->caseInstanceId = null;
        }
    }

    protected void ensureSuperCaseExecutionInitialized() {
        if (superCaseExecution == null && superCaseExecutionId != null) {
        superCaseExecution = Context::getCommandContext()->getCaseExecutionManager().findCaseExecutionById(superCaseExecutionId);
        }
    }

    // sub case execution //////////////////////////////////////////////////////

    @Override
    public CaseExecutionEntity getSubCaseInstance() {
        ensureSubCaseInstanceInitialized();
        return subCaseInstance;

    }

    @Override
    public void setSubCaseInstance(CmmnExecution subCaseInstance) {
        shouldQueryForSubCaseInstance = subCaseInstance != null;
        $this->subCaseInstance = (CaseExecutionEntity) subCaseInstance;
    }

    protected void ensureSubCaseInstanceInitialized() {
        if (shouldQueryForSubCaseInstance && subCaseInstance == null) {
        subCaseInstance = Context::getCommandContext()->getCaseExecutionManager().findSubCaseInstanceBySuperExecutionId(id);
        }
    }*/

    // customized persistence behavior /////////////////////////////////////////

    public function remove(): void
    {
        parent::remove();

        // removes jobs, incidents and tasks, and
        // clears the variable store
        $this->clearExecution();

        // remove all event subscriptions for this scope, if the scope has event
        // subscriptions:
        $this->removeEventSubscriptions();

        // finally delete this execution
        Context::getCommandContext()->getExecutionManager()->deleteExecution($this);
    }

    protected function removeEventSubscriptionsExceptCompensation(): void
    {
        // remove event subscriptions which are not compensate event subscriptions
        $eventSubscriptions = $this->getEventSubscriptions();
        foreach ($eventSubscriptions as $eventSubscriptionEntity) {
            if (!EventType::compensate()->name() == $eventSubscriptionEntity->getEventType()) {
                $eventSubscriptionEntity->delete();
            }
        }
    }

    public function removeEventSubscriptions(): void
    {
        foreach ($this->getEventSubscriptions() as $eventSubscription) {
            if ($this->getReplacedBy() != null) {
                $eventSubscription->setExecution($this->getReplacedBy());
            } else {
                $eventSubscription->delete();
            }
        }
    }

    private function removeJobs(): void
    {
        foreach ($this->getJobs() as $job) {
            if ($this->isReplacedByParent()) {
                $job->setExecution($this->getReplacedBy());
            } else {
                $job->delete();
            }
        }
    }

    private function removeIncidents(): void
    {
        foreach ($this->getIncidents() as $incident) {
            if ($this->isReplacedByParent()) {
                $incident->setExecution($this->getReplacedBy());
            } else {
                $incidentContext = $this->createIncidentContext($incident->getConfiguration());
                IncidentHandling::removeIncidents($incident->getIncidentType(), $incidentContext, false);
            }
        }

        foreach ($this->getIncidents() as $incident) {
            // if the handler doesn't take care of it,
            // make sure the incident is deleted nevertheless
            $incident->delete();
        }
    }

    protected function removeTasks(?string $reason): void
    {
        if ($reason == null) {
            $reason = TaskEntity::DELETE_REASON_DELETED;
        }
        foreach ($this->getTasks() as $task) {
            if ($this->isReplacedByParent()) {
                if ($task->getExecution() == null || $task->getExecution() != $replacedBy) {
                    // All tasks should have been moved when "replacedBy" has been set.
                    // Just in case tasks where added,
                    // wo do an additional check here and move it
                    $task->setExecution($replacedBy);
                    $this->getReplacedBy()->addTask($task);
                }
            } else {
                $task->delete($reason, false, $this->skipCustomListeners);
            }
        }
    }

    protected function removeExternalTasks(): void
    {
        foreach ($this->getExternalTasks() as $externalTask) {
            $externalTask->delete();
        }
    }

    public function getReplacedBy(): ?ExecutionEntity
    {
        return $this->replacedBy;
    }

    public function resolveReplacedBy(): ?ExecutionEntity
    {
        return parent::resolveReplacedBy();
    }

    public function replace(PvmExecutionImpl $execution): void
    {
        $replacedExecution = $execution;

        $this->setListenerIndex($replacedExecution->getListenerIndex());
        $replacedExecution->setListenerIndex(0);

        // update the related tasks
        $replacedExecution->moveTasksTo($this);

        $replacedExecution->moveExternalTasksTo($this);

        // update those jobs that are directly related to the argument execution's
        // current activity
        $replacedExecution->moveActivityLocalJobsTo($this);

        if (!$replacedExecution->isEnded()) {
            // on compaction, move all variables
            if ($replacedExecution->getParent() == $this) {
                $replacedExecution->moveVariablesTo($this);
            } else {
                $replacedExecution->moveConcurrentLocalVariablesTo($this);
            }
        }

        // note: this method not move any event subscriptions since concurrent
        // executions
        // do not have event subscriptions (and either one of the executions
        // involved in this
        // operation is concurrent)

        parent::replace($replacedExecution);
    }

    public function onConcurrentExpand(PvmExecutionImpl $scopeExecution): void
    {
        $scopeExecutionEntity = $scopeExecution;
        $scopeExecutionEntity->moveConcurrentLocalVariablesTo($this);
        parent::onConcurrentExpand($scopeExecutionEntity);
    }

    protected function moveTasksTo(ExecutionEntity $other): void
    {
        // update the related tasks
        foreach ($this->getTasksInternal() as $task) {
            $task->setExecution($other);

            // update the related local task variables
            $variables = $task->getVariablesInternal();

            foreach ($variables as $variable) {
                $variable->setExecution($other);
            }

            $other->addTask($task);
        }
        $this->tasks = [];
    }

    protected function moveExternalTasksTo(ExecutionEntity $other): void
    {
        foreach ($this->getExternalTasksInternal() as $externalTask) {
            $externalTask->setExecutionId($other->getId());
            $externalTask->setExecution($other);

            $other->addExternalTask(externalTask);
        }

        $this->externalTasks = [];
    }

    protected function moveActivityLocalJobsTo(ExecutionEntity $other): void
    {
        if ($this->activityId != null) {
            foreach ($this->getJobs() as $job) {
                if ($this->activityId == $job->getActivityId()) {
                    $this->removeJob($job);
                    $job->setExecution($other);
                }
            }
        }
    }

    protected function moveVariablesTo(ExecutionEntity $other): void
    {
        $variables = $variableStore->getVariables();
        $variableStore->removeVariables();

        foreach ($variables as $variable) {
            $this->moveVariableTo($variable, $other);
        }
    }

    protected function moveVariableTo(VariableInstanceEntity $variable, ExecutionEntity $other): void
    {
        if ($other->variableStore->containsKey($variable->getName())) {
            $existingInstance = $other->variableStore->getVariable($variable->getName());
            $existingInstance->setValue($variable->getTypedValue(false));
            $this->invokeVariableLifecycleListenersUpdate($existingInstance, $this);
            $this->invokeVariableLifecycleListenersDelete(
                $variable,
                $this,
                [$this->getVariablePersistenceListener()]
            );
        } else {
            $other->variableStore->addVariable($variable);
        }
    }

    protected function moveConcurrentLocalVariablesTo(ExecutionEntity $other): void
    {
        $variables = $variableStore->getVariables();

        foreach ($variables as $variable) {
            if ($variable->isConcurrentLocal()) {
                $this->moveVariableTo($variable, $other);
            }
        }
    }

    // variables ////////////////////////////////////////////////////////////////

    public function addVariableListener(VariableInstanceLifecycleListenerInterface $listener): void
    {
        $this->registeredVariableListeners[] = $listener;
    }

    public function removeVariableListener(VariableInstanceLifecycleListenerInterface $listener): void
    {
        foreach ($this->registeredVariableListeners as $key => $value) {
            if ($value == $listener) {
                unset($this->registeredVariableListeners[$key]);
            }
        }
    }

    public function isExecutingScopeLeafActivity(): bool
    {
        return $this->isActive && $this->getActivity() != null && $this->getActivity()->isScope() && $this->activityInstanceId != null
            && !($this->getActivity()->getActivityBehavior() instanceof CompositeActivityBehaviorInterface);
    }

    public function provideVariables(?array $variableNames = []): array
    {
        if (!empty($variableNames)) {
            return Context::getCommandContext()->getVariableInstanceManager()->findVariableInstancesByExecutionIdAndVariableNames($this->id, $variableNames);
        }
        return Context::getCommandContext()->getVariableInstanceManager()->findVariableInstancesByExecutionId($this->id);
    }

    /**
     * Fetch all the executions inside the same process instance as list and then
     * reconstruct the complete execution tree.
     *
     * In many cases this is an optimization over fetching the execution tree
     * lazily. Usually we need all executions anyway and it is preferable to fetch
     * more data in a single query (maybe even too much data) then to run multiple
     * queries, each returning a fraction of the data.
     *
     * The most important consideration here is network roundtrip: If the process
     * engine and database run on separate hosts, network roundtrip has to be
     * added to each query. Economizing on the number of queries economizes on
     * network roundtrip. The tradeoff here is network roundtrip vs. throughput:
     * multiple roundtrips carrying small chucks of data vs. a single roundtrip
     * carrying more data.
     *
     */
    protected function ensureExecutionTreeInitialized(): void
    {
        $executions = Context::getCommandContext()
        ->getExecutionManager()
        ->findExecutionsByProcessInstanceId($this->processInstanceId);

        $processInstance = $this->isProcessInstanceExecution() ? $this : null;

        if ($processInstance == null) {
            foreach ($executions as $execution) {
                if ($execution->isProcessInstanceExecution()) {
                    $processInstance = $execution;
                }
            }
        }

        $processInstance->restoreProcessInstance($executions, null, null, null, null, null, null);
    }

    /**
     * Restores a complete process instance tree including referenced entities.
     *
     * @param executions
     *   the list of all executions that are part of this process instance.
     *   Cannot be null, must include the process instance execution itself.
     * @param eventSubscriptions
     *   the list of all event subscriptions that are linked to executions which is part of this process instance
     *   If null, event subscriptions are not initialized and lazy loaded on demand
     * @param variables
     *   the list of all variables that are linked to executions which are part of this process instance
     *   If null, variables are not initialized and are lazy loaded on demand
     * @param jobs
     * @param tasks
     * @param incidents
     */
    public function restoreProcessInstance(
        array $executions,
        ?array $eventSubscriptions,
        ?array $variables,
        ?array $tasks,
        ?array $jobs,
        ?array $incidents,
        ?array $externalTasks
    ): void {

        if (!$this->isProcessInstanceExecution()) {
            //throw LOG.restoreProcessInstanceException($this);
        }

        // index executions by id
        $executionsMap = [];
        foreach ($executions as $execution) {
            $executionsMap[$execution->getId()] = $execution;
        }

        $variablesByScope = [];
        if ($variables != null) {
            foreach ($variables as $variable) {
                CollectionUtil::addToMapOfLists($variablesByScope, $variable->getVariableScopeId(), $variable);
            }
        }

        // restore execution tree
        foreach ($executions as $execution) {
            if (empty($execution->executions)) {
                $execution->executions = [];
            }
            if (empty($execution->eventSubscriptions) && $eventSubscriptions != null) {
                $execution->eventSubscriptions = [];
            }
            if ($variables != null) {
                $execution->variableStore->setVariablesProvider(
                    new VariableCollectionProvider($variablesByScope->get($execution->id))
                );
            }
            $parentId = $execution->getParentId();
            $parent = null;
            if (array_key_exists($parentId, $executionsMap)) {
                $parent = $executionsMap[$parentId];
            }
            if (!$execution->isProcessInstanceExecution()) {
                if ($parent == null) {
                    //throw LOG.resolveParentOfExecutionFailedException(parentId, execution->getId());
                    throw new \Exception("Execution");
                }
                $execution->processInstance = $this;
                $execution->parent = $parent;
                if ($parent->executions == null) {
                    $parent->executions = [];
                }
                $parent->executions[] = $execution;
            } else {
                $execution->processInstance = $execution;
            }
        }

        if ($eventSubscriptions != null) {
            // add event subscriptions to the right executions in the tree
            foreach ($eventSubscriptions as $eventSubscription) {
                $executionEntity = null;
                if (array_key_exists($eventSubscription->getExecutionId(), $executionsMap)) {
                    $executionEntity = $executionsMap[$eventSubscription->getExecutionId()];
                }
                if ($executionEntity != null) {
                    $executionEntity->addEventSubscription($eventSubscription);
                } else {
                    //throw LOG.executionNotFoundException(eventSubscription->getExecutionId());
                    throw new \Exception("Execution");
                }
            }
        }

        if ($jobs != null) {
            foreach ($jobs as $job) {
                $execution = null;
                if (array_key_exists($job->getExecutionId(), $executionsMap)) {
                    $execution = $executionsMap[$job->getExecutionId()];
                }
                $job->setExecution($execution);
            }
        }

        if ($tasks != null) {
            foreach ($tasks as $task) {
                $execution = null;
                if (array_key_exists($task->getExecutionId(), $executionsMap)) {
                    $execution = $executionsMap[$task->getExecutionId()];
                }
                $task->setExecution($execution);
                $execution->addTask($task);

                if ($variables != null) {
                    $task->variableStore->setVariablesProvider(new VariableCollectionProvider($variablesByScope->get($task->id)));
                }
            }
        }


        if ($incidents != null) {
            foreach ($incidents as $incident) {
                $execution = null;
                if (array_key_exists($incident->getExecutionId(), $executionsMap)) {
                    $execution = $executionsMap[$incident->getExecutionId()];
                }
                $incident->setExecution($execution);
            }
        }

        if ($externalTasks != null) {
            foreach ($externalTasks as $externalTask) {
                $execution = null;
                if (array_key_exists($externalTask->getExecutionId(), $executionsMap)) {
                    $execution = $executionsMap[$externalTask->getExecutionId()];
                }
                $externalTask->setExecution($execution);
                $execution->addExternalTask($externalTask);
            }
        }
    }


    // persistent state /////////////////////////////////////////////////////////

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["processDefinitionId"] = $this->processDefinitionId;
        $persistentState["businessKey"] = $this->businessKey;
        $persistentState["activityId"] = $this->activityId;
        $persistentState["activityInstanceId"] = $this->activityInstanceId;
        $persistentState["isActive"] = $this->isActive;
        $persistentState["isConcurrent"] = $this->isConcurrent;
        $persistentState["isScope"] = $this->isScope;
        $persistentState["isEventScope"] = $this->isEventScope;
        $persistentState["parentId"] = $this->parentId;
        $persistentState["superExecution"] = $this->superExecutionId;
        $persistentState["superCaseExecutionId"] = $this->superCaseExecutionId;
        $persistentState["caseInstanceId"] = $this->caseInstanceId;
        $persistentState["suspensionState"] = $this->suspensionState;
        $persistentState["cachedEntityState"] = $this->getCachedEntityState();
        $persistentState["sequenceCounter"] = $this->getSequenceCounter();
        return $persistentState;
    }

    public function insert(): void
    {
        Context::getCommandContext()->getExecutionManager()->insertExecution($this);
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function forceUpdate(): void
    {
        Context::getCommandContext()->getDbEntityManager()->forceUpdate($this);
    }

    // toString /////////////////////////////////////////////////////////////////

    public function __toString()
    {
        if ($this->isProcessInstanceExecution()) {
            return "ProcessInstance[" . $this->getToStringIdentity() . "]";
        } else {
            return ($this->isConcurrent ? "Concurrent" : "") . ($this->isScope ? "Scope" : "") . "Execution[" . $this->getToStringIdentity() . "]";
        }
    }

    protected function getToStringIdentity(): string
    {
        return $this->id;
    }

    // event subscription support //////////////////////////////////////////////

    public function getEventSubscriptionsInternal(): array
    {
        $this->ensureEventSubscriptionsInitialized();
        return $this->eventSubscriptions;
    }

    public function getEventSubscriptions(): array
    {
        return $this->getEventSubscriptionsInternal();
    }

    public function getCompensateEventSubscriptions(?string $activityId = null): array
    {
        if ($activityId == null) {
            $eventSubscriptions = $this->getEventSubscriptionsInternal();
            $result = [];
            foreach ($eventSubscriptions as $eventSubscriptionEntity) {
                if ($eventSubscriptionEntity->isSubscriptionForEventType(EventType::compensate())) {
                    $result[] = $eventSubscriptionEntity;
                }
            }
            return $result;
        } else {
            $eventSubscriptions = $this->getEventSubscriptionsInternal();
            $result = [];
            foreach ($eventSubscriptions as $eventSubscriptionEntity) {
                if (
                    $eventSubscriptionEntity->isSubscriptionForEventType(EventType::compensate())
                    && $this->activityId == $eventSubscriptionEntity->getActivityId()
                ) {
                    $result[] = $eventSubscriptionEntity;
                }
            }
            return $result;
        }
    }

    protected function ensureEventSubscriptionsInitialized(): void
    {
        if (empty($this->eventSubscriptions)) {
            $this->eventSubscriptions = Context::getCommandContext()->getEventSubscriptionManager()->findEventSubscriptionsByExecution($this->id);
        }
    }

    public function addEventSubscription(EventSubscriptionEntity $eventSubscriptionEntity): void
    {
        $eventSubscriptionsInternal = $this->getEventSubscriptionsInternal();
        $exists = false;
        foreach ($eventSubscriptionsInternal as $value) {
            if ($value == $eventSubscriptionEntity) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->eventSubscriptions[] = $eventSubscriptionEntity;
        }
    }

    public function removeEventSubscription(EventSubscriptionEntity $eventSubscriptionEntity): void
    {
        foreach ($this->eventSubscriptions as $key => $value) {
            if ($value == $eventSubscriptionEntity) {
                unset($this->eventSubscriptions[$key]);
            }
        }
    }

    // referenced job entities //////////////////////////////////////////////////

    protected function ensureJobsInitialized(): void
    {
        if ($this->jobs == null) {
            $this->jobs = Context::getCommandContext()->getJobManager()->findJobsByExecutionId($this->id);
        }
    }

    protected function getJobsInternal(): array
    {
        $this->ensureJobsInitialized();
        return $this->jobs;
    }

    public function getJobs(): array
    {
        return $this->getJobsInternal();
    }

    public function addJob(JobEntity $jobEntity): void
    {
        $jobsInternal = $this->getJobsInternal();
        $exists = false;
        foreach ($jobsInternal as $value) {
            if ($value == $jobEntity) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->jobs[] = $jobEntity;
        }
    }

    public function removeJob(JobEntity $job): void
    {
        foreach ($this->jobs as $key => $value) {
            if ($value == $job) {
                unset($this->jobs[$key]);
            }
        }
    }

    // referenced incidents entities
    // //////////////////////////////////////////////

    protected function ensureIncidentsInitialized(): void
    {
        if ($this->incidents == null) {
            $this->incidents = Context::getCommandContext()->getIncidentManager()->findIncidentsByExecution($this->id);
        }
    }

    protected function getIncidentsInternal(): array
    {
        $this->ensureIncidentsInitialized();
        return $this->incidents;
    }

    public function getIncidents(): array
    {
        return $this->getIncidentsInternal();
    }

    public function addIncident(IncidentEntity $incident): void
    {
        $incidentsInternal = $this->getIncidentsInternal();
        $exists = false;
        foreach ($incidentsInternal as $value) {
            if ($value == $incident) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->incidents[] = $incident;
        }
    }

    public function removeIncident(IncidentEntity $incident): void
    {
        foreach ($this->incidents as $key => $value) {
            if ($value == $incident) {
                unset($this->incidents[$key]);
            }
        }
    }

    public function getIncidentByCauseIncidentId(string $causeIncidentId): ?IncidentEntity
    {
        foreach ($this->getIncidents() as $incident) {
            if ($incident->getCauseIncidentId() != null && $incident->getCauseIncidentId() == $causeIncidentId) {
                return $incident;
            }
        }
        return null;
    }

    // referenced task entities
    // ///////////////////////////////////////////////////

    protected function ensureTasksInitialized(): void
    {
        if ($this->tasks == null) {
            $this->tasks = Context::getCommandContext()->getTaskManager()->findTasksByExecutionId($this->id);
        }
    }

    protected function getTasksInternal(): array
    {
        $this->ensureTasksInitialized();
        return $this->tasks;
    }

    public function getTasks(): array
    {
        return $this->getTasksInternal();
    }

    public function addTask(TaskEntity $taskEntity): void
    {
        $tasksInternal = $this->getTasksInternal();
        $exists = false;
        foreach ($tasksInternal as $value) {
            if ($value == $taskEntity) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->tasks[] = $taskEntity;
        }
    }

    public function removeTask(TaskEntity $task): void
    {
        foreach ($this->tasks as $key => $value) {
            if ($value == $task) {
                unset($this->tasks[$key]);
            }
        }
    }

    // external tasks

    protected function ensureExternalTasksInitialized(): void
    {
        if ($this->externalTasks == null) {
            $this->externalTasks = Context::getCommandContext()->getExternalTaskManager()->findExternalTasksByExecutionId($this->id);
        }
    }

    protected function getExternalTasksInternal(): array
    {
        $this->ensureExternalTasksInitialized();
        return $this->externalTasks;
    }

    public function addExternalTask(ExternalTaskEntity $externalTask): void
    {
        $externalTasks = $this->getExternalTasksInternal();
        $exists = false;
        foreach ($externalTasks as $value) {
            if ($value == $externalTask) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->externalTasks[] = $externalTask;
        }
    }

    public function removeExternalTask(ExternalTaskEntity $externalTask): void
    {
        foreach ($this->externalTasks as $key => $value) {
            if ($value == $externalTask) {
                unset($this->externalTasks[$key]);
            }
        }
    }

    public function getExternalTasks(): array
    {
        return $this->getExternalTasksInternal();
    }

    // variables /////////////////////////////////////////////////////////

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
        $listeners = [];

        $listeners[] = $this->getVariablePersistenceListener();
        $listeners[] = new VariableInstanceConcurrentLocalInitializer($this);
        $listeners[] = VariableInstanceSequenceCounterListener::instance();

        $listeners[] = VariableInstanceHistoryListener::instance();

        $listeners[] = new VariableListenerInvocationListener($this);

        $listeners = array_merge($listeners, $this->registeredVariableListeners);

        return $listeners;
    }

    public function getVariablePersistenceListener(): VariableInstanceLifecycleListenerInterface
    {
        return VariableInstanceEntityPersistenceListener::instance();
    }

    public function getVariablesInternal(): array
    {
        return $this->variableStore->getVariables();
    }

    public function removeVariableInternal(VariableInstanceEntity $variable): void
    {
        if ($this->variableStore->containsValue($variable)) {
            $this->variableStore->removeVariable($variable->getName());
        }
    }

    public function addVariableInternal(VariableInstanceEntity $variable): void
    {
        if ($this->variableStore->containsKey($variable->getName())) {
            $existingVariable = $this->variableStore->getVariable($variable->getName());
            $existingVariable->setValue($variable->getTypedValue());
            $variable->delete();
        } else {
            $this->variableStore->addVariable($variable);
        }
    }

    public function handleConditionalEventOnVariableChange(VariableEvent $variableEvent): void
    {
        $subScriptions = $this->getEventSubscriptions();
        foreach ($subScriptions as $subscription) {
            if (EventType::conditional()->name() == $subscription->getEventType()) {
                $subscription->processEventSync($variableEvent);
            }
        }
    }

    public function dispatchEvent(VariableEvent $variableEvent): void
    {
        $execs = [];
        $scope = new \stdClass();
        $scope->execs = $execs;
        (new ExecutionTopDownWalker($this))->addPreVisitor(new class ($scope) implements TreeVisitorInterface {
            private $scope;

            public function __construct($scope)
            {
                $this->scope = $scope;
            }

            public function visit($obj): void
            {
                if (
                    !empty($obj->getEventSubscriptions())
                    && ($obj->isInState(ActivityInstanceState::default()) || (!$obj->getActivity()->isScope()))
                ) { // state is default or tree is compacted
                    $this->scope->execs[] = $obj;
                }
            }
        })->walkUntil();
        foreach ($scope->execs as $execution) {
            $execution->handleConditionalEventOnVariableChange($variableEvent);
        }
    }



    // getters and setters //////////////////////////////////////////////////////

    public function setCachedEntityState(int $cachedEntityState): void
    {
        $this->cachedEntityState = $cachedEntityState;

        // Check for flags that are down. These lists can be safely initialized as
        // empty, preventing
        // additional queries that end up in an empty list anyway
        if ($this->jobs == null && !BitMaskUtil::isBitOn($this->cachedEntityState, self::JOBS_STATE_BIT)) {
            $this->jobs = [];
        }
        if ($this->tasks == null && !BitMaskUtil::isBitOn($this->cachedEntityState, self::TASKS_STATE_BIT)) {
            $this->tasks = [];
        }
        if ($this->eventSubscriptions == null && !BitMaskUtil::isBitOn($this->cachedEntityState, self::EVENT_SUBSCRIPTIONS_STATE_BIT)) {
            $this->eventSubscriptions = [];
        }
        if ($this->incidents == null && !BitMaskUtil::isBitOn($this->cachedEntityState, self::INCIDENT_STATE_BIT)) {
            $this->incidents = [];
        }
        if (!$this->variableStore->isInitialized() && !BitMaskUtil::isBitOn($this->cachedEntityState, self::VARIABLES_STATE_BIT)) {
            $this->variableStore->setVariablesProvider(VariableCollectionProvider::emptyVariables());
            $this->variableStore->forceInitialization();
        }
        if ($this->externalTasks == null && !BitMaskUtil::isBitOn($this->cachedEntityState, self::EXTERNAL_TASKS_BIT)) {
            $this->externalTasks = [];
        }
        $this->shouldQueryForSubprocessInstance = BitMaskUtil::isBitOn($this->cachedEntityState, self::SUB_PROCESS_INSTANCE_STATE_BIT);
        $this->shouldQueryForSubCaseInstance = BitMaskUtil::isBitOn($this->cachedEntityState, self::SUB_CASE_INSTANCE_STATE_BIT);
    }

    public function getCachedEntityState(): int
    {
        $this->cachedEntityState = 0;

        // Only mark a flag as false when the list is not-null and empty. If null,
        // we can't be sure there are no entries in it since
        // the list hasn't been initialized/queried yet.
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::TASKS_STATE_BIT, ($this->tasks == null || count($this->tasks) > 0));
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::EVENT_SUBSCRIPTIONS_STATE_BIT, ($this->eventSubscriptions == null || count($this->eventSubscriptions) > 0));
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::JOBS_STATE_BIT, ($this->jobs == null || count($this->jobs) > 0));
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::INCIDENT_STATE_BIT, ($this->incidents == null || count($this->incidents) > 0));
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::VARIABLES_STATE_BIT, (!$this->variableStore->isInitialized() || !$this->variableStore->isEmpty()));
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::SUB_PROCESS_INSTANCE_STATE_BIT, $this->shouldQueryForSubprocessInstance);
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::SUB_CASE_INSTANCE_STATE_BIT, $this->shouldQueryForSubCaseInstance);
        $this->cachedEntityState = BitMaskUtil::setBit($this->cachedEntityState, self::EXTERNAL_TASKS_BIT, ($this->externalTasks == null || count($this->externalTasks) > 0));

        return $this->cachedEntityState;
    }

    public function getCachedEntityStateRaw(): int
    {
        return $this->cachedEntityState;
    }

    public function getRootProcessInstanceId(): string
    {
        if ($this->isProcessInstanceExecution()) {
            return $this->rootProcessInstanceId;
        } else {
            $processInstance = $this->getProcessInstance();
            return $processInstance->rootProcessInstanceId;
        }
    }

    public function getRootProcessInstanceIdRaw(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;

        if ($this->id == $processInstanceId) {
            $this->processInstance = $this;
        }
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function setSuperExecutionId(string $superExecutionId): void
    {
        $this->superExecutionId = $superExecutionId;
    }

    public function getReferencedEntityIds(): array
    {
        $referenceIds = [];

        if ($this->superExecutionId != null) {
            $referenceIds[] = $this->superExecutionId;
        }
        if ($this->parentId != null) {
            $referenceIds[] = $parentId;
        }

        return $referenceIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];

        if ($this->superExecutionId != null) {
            $referenceIdAndClass[$this->superExecutionId] = ExecutionEntity::class;
        }
        if ($this->parentId != null) {
            $referenceIdAndClass[$this->parentId] = ExecutionEntity::class;
        }
        if ($this->processInstanceId != null) {
            $referenceIdAndClass[$this->processInstanceId] = ExecutionEntity::class;
        }
        if ($this->processDefinitionId != null) {
            $referenceIdAndClass[$this->processDefinitionId] = ProcessDefinitionEntity::class;
        }

        return $referenceIdAndClass;
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

    public function getCurrentActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getCurrentActivityName(): ?string
    {
        return $this->activityName;
    }

    public function getBpmnModelElementInstance(): FlowElementInterface
    {
        $bpmnModelInstance = $this->getBpmnModelInstance();
        if ($bpmnModelInstance != null) {
            $modelElementInstance = null;
            if (ExecutionListenerInterface::EVENTNAME_TAKE == $this->eventName) {
                $modelElementInstance = $bpmnModelInstance->getModelElementById($transition->getId());
            } else {
                $modelElementInstance = $bpmnModelInstance->getModelElementById($activityId);
            }

            try {
                return $modelElementInstance;
            } catch (\Exception $e) {
                $elementType = $modelElementInstance->getElementType();
                //throw LOG.castModelInstanceException(modelElementInstance, "FlowElement", elementType->getTypeName(),
                //elementType->getTypeNamespace(), e);
                throw $e;
            }
        } else {
            return null;
        }
    }

    public function getBpmnModelInstance(): ?BpmnModelInstanceInterface
    {
        if ($this->processDefinitionId != null) {
            return Context::getProcessEngineConfiguration()->getDeploymentCache()->findBpmnModelInstanceForProcessDefinition($this->processDefinitionId);
        } else {
            return null;
        }
    }

    public function getProcessEngineServices(): ProcessEngineServicesInterface
    {
        return Context::getProcessEngineConfiguration()->getProcessEngine();
    }

    public function getProcessEngine(): ProcessEngineInterface
    {
        return Context::getProcessEngineConfiguration()->getProcessEngine();
    }

    public function getProcessDefinitionTenantId(): ?string
    {
        return $this->getProcessDefinition()->getTenantId();
    }
}
