<?php

namespace Jabe\Impl\Pvm\Runtime;

use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Instance\CoreExecution;
use Jabe\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Impl\Core\Variable\Scope\AbstractVariableScope;
use Jabe\Impl\Form\FormPropertyHelper;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventProcessor,
    HistoryEventCreator,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Incident\{
    IncidentContext,
    IncidentHandlerInterface,
    IncidentHandling
};
use Jabe\Impl\Persistence\Entity\{
    DelayedVariableEvent,
    IncidentEntity
};
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmException,
    PvmExecutionInterface,
    PvmLogger,
    PvmProcessDefinitionInterface,
    PvmProcessInstanceInterface,
    PvmScopeInterface,
    PvmTransitionInterface
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    CompositeActivityBehaviorInterface,
    ModificationObserverBehaviorInterface,
    SignallableActivityBehaviorInterface
};
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ActivityStartBehavior,
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Impl\Pvm\Runtime\Operation\PvmAtomicOperationInterface;
use Jabe\Impl\Tree\{
    ExecutionWalker,
    FlowScopeWalker,
    LeafActivityInstanceExecutionCollector,
    ReferenceWalker,
    ScopeCollector,
    ScopeExecutionCollector,
    TreeVisitorInterface,
    WalkConditionInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\IncidentInterface;
use Jabe\Variable\VariableMapInterface;

abstract class PvmExecutionImpl extends CoreExecution implements ActivityExecutionInterface, PvmProcessInstanceInterface
{
    //private static final PvmLogger LOG = ProcessEngineLogger.PVM_LOGGER;

    protected $processDefinition;

    protected $scopeInstantiationContext;

    protected $ignoreAsync = false;

    /**
     * true for process instances in the initial phase. Currently
     * this controls that historic variable updates created during this phase receive
     * the <code>initial</code> flag (see HistoricVariableUpdateEventEntity#isInitial).
     */
    protected $isStarting = false;

    // current position /////////////////////////////////////////////////////////

    /**
     * current activity
     */
    protected $activity;

    /**
     * the activity which is to be started next
     */
    protected $nextActivity;

    /**
     * the transition that is currently being taken
     */
    protected $transition;

    /**
     * A list of outgoing transitions from the current activity
     * that are going to be taken
     */
    protected $transitionsToTake = null;

    /**
     * the unique id of the current activity instance
     */
    protected $activityInstanceId;

    /**
     * the id of a case associated with this execution
     */
    protected $caseInstanceId;

    protected $replacedBy;

    // cascade deletion ////////////////////////////////////////////////////////

    protected $deleteRoot;
    protected $deleteReason;
    protected $externallyTerminated;

    //state/type of execution //////////////////////////////////////////////////

    /**
     * indicates if this execution represents an active path of execution.
     * Executions are made inactive in the following situations:
     * <ul>
     * <li>an execution enters a nested scope</li>
     * <li>an execution is split up into multiple concurrent executions, then the parent is made inactive.</li>
     * <li>an execution has arrived in a parallel gateway or join and that join has not yet activated/fired.</li>
     * <li>an execution is ended.</li>
     * </ul>
     */
    protected $isActive = true;
    protected $isScope = true;
    protected $isConcurrent = false;
    protected $isEnded = false;
    protected $isEventScope = false;
    protected $isRemoved = false;

    /**
     * transient; used for process instance modification to preserve a scope from getting deleted
     */
    protected $preserveScope = false;

    /**
     * marks the current activity instance
     */
    protected $activityInstanceState;

    protected $activityInstanceEndListenersFailed = false;

    // sequence counter ////////////////////////////////////////////////////////
    protected $sequenceCounter = 0;

    public function __construct()
    {
        $this->activityInstanceState = ActivityInstanceState::default()->getStateCode();
    }

    // API ////////////////////////////////////////////////

    /**
     * creates a new execution. properties processDefinition, processInstance and activity will be initialized.
     */
    abstract public function createExecution(): PvmExecutionImpl;

    public function createSubProcessInstance(PvmProcessDefinitionInterface $processDefinition, ?string $businessKey = null, ?string $caseInstanceId = null): PvmExecutionImpl
    {
        $subProcessInstance = $this->newExecution();

        // manage bidirectional super-subprocess relation
        $subProcessInstance->setSuperExecution($this);
        $this->setSubProcessInstance($subProcessInstance);

        // Initialize the new execution
        $subProcessInstance->setProcessDefinition($processDefinition);
        $subProcessInstance->setProcessInstance($subProcessInstance);
        $subProcessInstance->setActivity($processDefinition->getInitial());

        if ($businessKey !== null) {
            $subProcessInstance->setBusinessKey($businessKey);
        }

        /*if (caseInstanceId !== null) {
            subProcessInstance->setCaseInstanceId(caseInstanceId);
        }*/

        return $subProcessInstance;
    }

    abstract protected function newExecution(): PvmExecutionImpl;

    // sub case instance

    /*abstract public function CmmnExecution createSubCaseInstance(CmmnCaseDefinition caseDefinition);

    @Override
    abstract public function CmmnExecution createSubCaseInstance(CmmnCaseDefinition caseDefinition, String businessKey);*/

    abstract public function initialize(): void;

    abstract public function initializeTimerDeclarations(): void;

    public function executeIoMapping(): void
    {
        // execute Input Mappings (if they exist).
        $currentScope = $this->getScopeActivity();
        if ($currentScope != $currentScope->getProcessDefinition()) {
            $currentActivity = $currentScope;
            if ($currentActivity !== null && $currentActivity->getIoMapping() !== null && !$this->skipIoMapping) {
                $currentActivity->getIoMapping()->executeInputParameters($this);
            }
        }
    }

    public function startWithFormProperties(VariableMapInterface $formProperties): void
    {
        $this->start(null, $formProperties);
    }

    public function start(?array $variables = [], ?VariableMapInterface $formProperties = null): void
    {
        $this->initialize();

        $this->fireHistoricProcessStartEvent();

        if (!empty($variables)) {
            $this->setVariables($variables);
        }

        if ($formProperties !== null) {
            FormPropertyHelper::initFormPropertiesOnScope($formProperties, $this);
        }

        $this->initializeTimerDeclarations();

        $this->performOperation(AtomicOperation::processStart());
    }

    /**
     * perform starting behavior but don't execute the initial activity
     *
     * @param variables the variables which are used for the start
     */
    public function startWithoutExecuting(array $variables): void
    {
        $this->initialize();

        $this->fireHistoricProcessStartEvent();

        $this->setActivityInstanceId($this->getId());
        $this->setVariables($variables);

        $this->initializeTimerDeclarations();

        $this->performOperation(AtomicOperation::fireProcessStart());

        $this->setActivity(null);
    }

    abstract public function fireHistoricProcessStartEvent(): void;

    public function destroy(): void
    {
        //LOG.destroying(this);
        $this->setScope(false);
    }

    public function removeAllTasks(): void
    {
    }

    protected function removeEventScopes(): void
    {
        $childExecutions = $this->getEventScopeExecutions();
        foreach ($childExecutions as $childExecution) {
            //LOG.removingEventScope(childExecution);
            $childExecution->destroy();
            $childExecution->remove();
        }
    }

    public function clearScope(string $reason, bool $skipCustomListeners, bool $skipIoMappings, bool $externallyTerminated): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMapping = $skipIoMappings;

        if ($this->getSubProcessInstance() !== null) {
            $this->getSubProcessInstance()->deleteCascade($reason, $skipCustomListeners, $skipIoMappings, $externallyTerminated, false);
        }

        // remove all child executions and sub process instances:
        $executions = $this->getNonEventScopeExecutions();
        foreach ($executions as $childExecution) {
            if ($childExecution->getSubProcessInstance() !== null) {
                $childExecution->getSubProcessInstance()->deleteCascade($reason, $skipCustomListeners, $skipIoMappings, $externallyTerminated, false);
            }
            $childExecution->deleteCascade($reason, $skipCustomListeners, $skipIoMappings, $externallyTerminated, false);
        }

        // fire activity end on active activity
        $activity = $this->getActivity();
        if ($this->isActive && $activity !== null) {
            // set activity instance state to cancel
            if ($this->activityInstanceState != ActivityInstanceState::ending()->getStateCode()) {
                $this->setCanceled(true);
                $this->performOperation(AtomicOperation::fireActivityEnd());
            }
            // set activity instance state back to 'default'
            // -> execution will be reused for executing more activities and we want the state to
            // be default initially.
            $this->activityInstanceState = ActivityInstanceState::default()->getStateCode();
        }
    }

    /**
     * Interrupts an execution
     */
    public function interrupt(string $reason, ?bool $skipCustomListeners = false, ?bool $skipIoMappings = false, ?bool $externallyTerminated = false): void
    {
        //LOG.interruptingExecution(reason, skipCustomListeners);
        $this->clearScope($reason, $skipCustomListeners, $skipIoMappings, $externallyTerminated);
    }

    /**
     * Ends an execution. Invokes end listeners for the current activity and notifies the flow scope execution
     * of this happening which may result in the flow scope ending.
     *
     * @param completeScope true if ending the execution contributes to completing the BPMN 2.0 scope
     */
    public function end(bool $completeScope): void
    {
        $this->setCompleteScope($completeScope);

        $this->isActive = false;
        $this->isEnded = true;

        if ($this->hasReplacedParent()) {
            $this->getParent()->replacedBy = null;
        }

        $this->performOperation(AtomicOperation::activityNotifyListenerEnd());
    }

    public function endCompensation(): void
    {
        $this->performOperation(AtomicOperation::fireActivityEnd());
        $this->remove();

        $parent = $this->getParent();

        if ($parent->getActivity() === null) {
            $parent->setActivity($this->getActivity()->getFlowScope());
        }

        $parent->signal("compensationDone", null);
    }

    /**
     * <p>Precondition: execution is already ended but this has not been propagated yet.</p>
     * <p>
     * <p>Propagates the ending of this execution to the flowscope execution; currently only supports
     * the process instance execution</p>
     */
    public function propagateEnd(): void
    {
        if (!$this->isEnded()) {
            throw new ProcessEngineException($this->__toString() . " must have ended before ending can be propagated");
        }

        if ($this->isProcessInstanceExecution()) {
            $this->performOperation(AtomicOperation::processEnd());
        } else {
            // not supported yet
        }
    }

    public function remove(): void
    {
        $parent = $this->getParent();
        if ($parent !== null) {
            $parent->removeExecution($this);

            // if the sequence counter is greater than the
            // sequence counter of the parent, then set
            // the greater sequence counter on the parent.
            $parentSequenceCounter = $parent->getSequenceCounter();
            $mySequenceCounter = $this->getSequenceCounter();
            if ($mySequenceCounter > $parentSequenceCounter) {
                $parent->setSequenceCounter($mySequenceCounter);
            }

            // propagate skipping configuration upwards, if it was not initially set on
            // the root execution
            $parent->skipCustomListeners |= $this->skipCustomListeners;
            $parent->skipIoMapping |= $this->skipIoMapping;
        }

        $this->isActive = false;
        $this->isEnded = true;
        $this->isRemoved = true;

        if ($this->hasReplacedParent()) {
            $this->getParent()->replacedBy = null;
        }
        $this->removeEventScopes();
    }

    abstract protected function removeExecution(PvmExecutionImpl $execution): void;

    abstract protected function addExecution(PvmExecutionImpl $execution): void;

    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    public function createConcurrentExecution(): PvmExecutionImpl
    {
        if (!$this->isScope()) {
            throw new ProcessEngineException("Cannot create concurrent execution for " . $this);
        }

        // The following covers the three cases in which a concurrent execution may be created
        // (this execution is the root in each scenario).
        //
        // Note: this should only consider non-event-scope executions. Event-scope executions
        // are not relevant for the tree structure and should remain under their original parent.
        //
        //
        // (1) A compacted tree:
        //
        // Before:               After:
        //       -------               -------
        //       |  e1  |              |  e1 |
        //       -------               -------
        //                             /     \
        //                         -------  -------
        //                         |  e2 |  |  e3 |
        //                         -------  -------
        //
        // e2 replaces e1; e3 is the new root for the activity stack to instantiate
        //
        //
        // (2) A single child that is a scope execution
        // Before:               After:
        //       -------               -------
        //       |  e1 |               |  e1 |
        //       -------               -------
        //          |                  /     \
        //       -------           -------  -------
        //       |  e2 |           |  e3 |  |  e4 |
        //       -------           -------  -------
        //                            |
        //                         -------
        //                         |  e2 |
        //                         -------
        //
        //
        // e3 is created and is concurrent;
        // e4 is the new root for the activity stack to instantiate
        //
        // (3) Existing concurrent execution(s)
        // Before:               After:
        //       -------                    ---------
        //       |  e1 |                    |   e1  |
        //       -------                    ---------
        //       /     \                   /    |    \
        //  -------    -------      -------  -------  -------
        //  |  e2 | .. |  eX |      |  e2 |..|  eX |  | eX+1|
        //  -------    -------      -------  -------  -------
        //
        // eX+1 is concurrent and the new root for the activity stack to instantiate
        $children = $this->getNonEventScopeExecutions();

        // whenever we change the set of child executions we have to force an update
        // on the scope executions to avoid concurrent modifications (e.g. tree compaction)
        // that go unnoticed
        $this->forceUpdate();

        if (empty($children)) {
            // (1)
            $replacingExecution = $this->createExecution();
            $replacingExecution->setConcurrent(true);
            $replacingExecution->setScope(false);
            $replacingExecution->replace($this);
            $this->inactivate();
            $this->setActivity(null);
        } elseif (count($children) == 1) {
            // (2)
            $child = $children[0];

            $concurrentReplacingExecution = $this->createExecution();
            $concurrentReplacingExecution->setConcurrent(true);
            $concurrentReplacingExecution->setScope(false);
            $concurrentReplacingExecution->setActive(false);
            $concurrentReplacingExecution->onConcurrentExpand($this);
            $child->setParent($concurrentReplacingExecution);
            $this->leaveActivityInstance();
            $this->setActivity(null);
        }

        // (1), (2), and (3)
        $concurrentExecution = $this->createExecution();
        $concurrentExecution->setConcurrent(true);
        $concurrentExecution->setScope(false);

        return $concurrentExecution;
    }

    public function tryPruneLastConcurrentChild(): bool
    {
        if (count($this->getNonEventScopeExecutions()) == 1) {
            $lastConcurrent = $this->getNonEventScopeExecutions()[0];
            if ($lastConcurrent->isConcurrent()) {
                if (!$lastConcurrent->isScope()) {
                    $this->setActivity($lastConcurrent->getActivity());
                    $this->setTransition($lastConcurrent->getTransition());
                    $this->replace($lastConcurrent);

                    // Move children of lastConcurrent one level up
                    if ($lastConcurrent->hasChildren()) {
                        foreach ($lastConcurrent->getExecutionsAsCopy() as $childExecution) {
                            $childExecution->setParent($this);
                        }
                    }

                    // Make sure parent execution is re-activated when the last concurrent
                    // child execution is active
                    if (!$this->isActive() && $lastConcurrent->isActive()) {
                        $this->setActive(true);
                    }

                    $lastConcurrent->remove();
                } else {
                    // legacy behavior
                    LegacyBehavior::pruneConcurrentScope($lastConcurrent);
                }
                return true;
            }
        }

        return false;
    }

    public function deleteCascade(
        string $deleteReason,
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false,
        ?bool $externallyTerminated = false,
        ?bool $skipSubprocesses = false
    ): void {
        $this->deleteReason = $deleteReason;
        $this->setDeleteRoot(true);
        $this->isEnded = true;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMapping = $skipIoMappings;
        $this->externallyTerminated = $externallyTerminated;
        $this->skipSubprocesses = $skipSubprocesses;
        $this->performOperation(AtomicOperation::deleteCascade());
    }

    public function executeEventHandlerActivity(ActivityImpl $eventHandlerActivity): void
    {
        // the target scope
        $flowScope = $eventHandlerActivity->getFlowScope();

        // the event scope (the current activity)
        $eventScope = $eventHandlerActivity->getEventScope();

        if (
            $eventHandlerActivity->getActivityStartBehavior() == ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE
            && $flowScope != $eventScope
        ) {
            // the current scope is the event scope of the activity
            $this->findExecutionForScope($eventScope, $flowScope)->executeActivity($eventHandlerActivity);
        } else {
            $this->executeActivity(eventHandlerActivity);
        }
    }

    // tree compaction & expansion ///////////////////////////////////////////

    /**
     * <p>Returns an execution that has replaced this execution for executing activities in their shared scope.</p>
     * <p>Invariant: this execution and getReplacedBy() execute in the same scope.</p>
     */
    abstract public function getReplacedBy(): ?PvmExecutionImpl;

    /**
     * Instead of {@link #getReplacedBy()}, which returns the execution that this execution was directly replaced with,
     * this resolves the chain of replacements (i.e. in the case the replacedBy execution itself was replaced again)
     */
    public function resolveReplacedBy(): ?PvmExecutionImpl
    {
        // follow the links of execution replacement;
        // note: this can be at most two hops:
        // case 1:
        //   this execution is a scope execution
        //     => tree may have expanded meanwhile
        //     => scope execution references replacing execution directly (one hop)
        //
        // case 2:
        //   this execution is a concurrent execution
        //     => tree may have compacted meanwhile
        //     => concurrent execution references scope execution directly (one hop)
        //
        // case 3:
        //   this execution is a concurrent execution
        //     => tree may have compacted/expanded/compacted/../expanded any number of times
        //     => the concurrent execution has been removed and therefore references the scope execution (first hop)
        //     => the scope execution may have been replaced itself again with another concurrent execution (second hop)
        //   note that the scope execution may have a long "history" of replacements, but only the last replacement is relevant here
        $replacingExecution = $this->getReplacedBy();

        if ($replacingExecution !== null) {
            $secondHopReplacingExecution = $replacingExecution->getReplacedBy();
            if ($secondHopReplacingExecution !== null) {
                $replacingExecution = $secondHopReplacingExecution;
            }
        }

        return $replacingExecution;
    }

    public function hasReplacedParent(): bool
    {
        return $this->getParent() !== null && $this->getParent()->getReplacedBy() == $this;
    }

    public function isReplacedByParent(): bool
    {
        return $this->getReplacedBy() !== null && $this->getReplacedBy() == $this->getParent();
    }

    /**
     * <p>Replace an execution by this execution. The replaced execution has a pointer ({@link #getReplacedBy()}) to this execution.
     * This pointer is maintained until the replaced execution is removed or this execution is removed/ended.</p>
     * <p>
     * <p>This is used for two cases: Execution tree expansion and execution tree compaction</p>
     * <ul>
     * <li><b>expansion</b>: Before:
     * <pre>
     *       -------
     *       |  e1 |  scope
     *       -------
     *     </pre>
     * After:
     * <pre>
     *       -------
     *       |  e1 |  scope
     *       -------
     *          |
     *       -------
     *       |  e2 |  cc (no scope)
     *       -------
     *     </pre>
     * e2 replaces e1: it should receive all entities associated with the activity currently executed
     * by e1; these are tasks, (local) variables, jobs (specific for the activity, not the scope)
     * </li>
     * <li><b>compaction</b>: Before:
     * <pre>
     *       -------
     *       |  e1 |  scope
     *       -------
     *          |
     *       -------
     *       |  e2 |  cc (no scope)
     *       -------
     *     </pre>
     * After:
     * <pre>
     *       -------
     *       |  e1 |  scope
     *       -------
     *     </pre>
     * e1 replaces e2: it should receive all entities associated with the activity currently executed
     * by e2; these are tasks, (all) variables, all jobs
     * </li>
     * </ul>
     *
     * @see #createConcurrentExecution()
     * @see #tryPruneLastConcurrentChild()
     */
    public function replace(PvmExecutionImpl $execution): void
    {
        // activity instance id handling
        $this->activityInstanceId = $execution->getActivityInstanceId();
        $this->isActive = $execution->isActive;

        $this->replacedBy = null;
        $execution->replacedBy = $this;

        $this->transitionsToTake = $execution->transitionsToTake;

        $execution->leaveActivityInstance();
    }

    /**
     * Callback on tree expansion when this execution is used as the concurrent execution
     * where the argument's children become a subordinate to. Note that this case is not the inverse
     * of replace because replace has the semantics that the replacing execution can be used to continue
     * execution of this execution's activity instance.
     */
    public function onConcurrentExpand(PvmExecutionImpl $scopeExecution): void
    {
        // by default, do nothing
    }

    // methods that translate to operations /////////////////////////////////////

    public function signal(string $signalName, $signalData): void
    {
        if ($this->getActivity() === null) {
            throw new PvmException("cannot signal execution " . $this->id . ": it has no current activity");
        }

        $activityBehavior = $this->activity->getActivityBehavior();
        try {
            $activityBehavior->signal($this, $signalName, $signalData);
        } catch (\Exception $e) {
            throw new PvmException("couldn't process signal '" . $signalName . "' on activity '" . $this->activity->getId() . "': " . $e->getMessage(), $e);
        }
    }

    public function take(): void
    {
        if ($this->transition === null) {
            throw new PvmException($this->__toString() . ": no transition to take specified");
        }
        $transitionImpl = $this->transition;
        $this->setActivity($transitionImpl->getSource());
        // while executing the transition, the activityInstance is 'null'
        // (we are not executing an activity)
        $this->setActivityInstanceId(null);
        $this->setActive(true);
        $this->performOperation(AtomicOperation::transitionNotifyListenerTake());
    }

    /**
     * Execute an activity which is not contained in normal flow (having no incoming sequence flows).
     * Cannot be called for activities contained in normal flow.
     * <p>
     * First, the ActivityStartBehavior is evaluated.
     * In case the start behavior is not ActivityStartBehavior#DEFAULT, the corresponding start
     * behavior is executed before executing the activity.
     * <p>
     * For a given activity, the execution on which this method must be called depends on the type of the start behavior:
     * <ul>
     * <li>CONCURRENT_IN_FLOW_SCOPE: scope execution for PvmActivity#getFlowScope()</li>
     * <li>INTERRUPT_EVENT_SCOPE: scope execution for PvmActivity#getEventScope()</li>
     * <li>CANCEL_EVENT_SCOPE: scope execution for PvmActivity#getEventScope()</li>
     * </ul>
     *
     * @param activity the activity to start
     */
    public function executeActivity(PvmActivityInterface $activity): void
    {
        if (!empty($activity->getIncomingTransitions())) {
            throw new ProcessEngineException("Activity is contained in normal flow and cannot be executed using executeActivity().");
        }

        $activityStartBehavior = $activity->getActivityStartBehavior();
        if (!$this->isScope() && ActivityStartBehavior::DEFAULT != $this->activityStartBehavior) {
            throw new ProcessEngineException("Activity '" . $activity . "' with start behavior '" . $activityStartBehavior . "'"
            . "cannot be executed by non-scope execution.");
        }

        $activityImpl = $activity;
        $this->isEnded = false;
        $this->isActive = true;

        switch ($activityStartBehavior) {
            case ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE:
                $this->nextActivity = $activityImpl;
                $this->performOperation(AtomicOperation::activityStartConcurrent());
                break;

            case ActivityStartBehavior::CANCEL_EVENT_SCOPE:
                $this->nextActivity = $activityImpl;
                $this->performOperation(AtomicOperation::activityStartCancelScope());
                break;

            case ActivityStartBehavior::INTERRUPT_EVENT_SCOPE:
                $this->nextActivity = $activityImpl;
                $this->performOperation(AtomicOperation::activityStartInterruptScope());
                break;

            default:
                $this->setActivity($activityImpl);
                $this->setActivityInstanceId(null);
                $this->performOperation(AtomicOperation::activityStartCreateScope());
                break;
        }
    }

    /**
     * Instantiates the given activity stack under this execution.
     * Sets the variables for the execution responsible to execute the most deeply nested
     * activity.
     *
     * @param activityStack The most deeply nested activity is the last element in the list
     */
    public function executeActivitiesConcurrent(
        array &$activityStack,
        ?PvmActivityInterface $targetActivity = null,
        ?PvmTransitionInterface $targetTransition = null,
        ?array $variables = [],
        ?array $localVariables = [],
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false
    ): void {
        $flowScope = null;
        if (!empty($activityStack)) {
            $flowScope = $activityStack[0]->getFlowScope();
        } elseif ($targetActivity !== null) {
            $flowScope = $targetActivity->getFlowScope();
        } elseif ($targetTransition !== null) {
            $flowScope = $targetTransition->getSource()->getFlowScope();
        }

        $propagatingExecution = null;
        if ($flowScope->getActivityBehavior() instanceof ModificationObserverBehaviorInterface) {
            $flowScopeBehavior = $flowScope->getActivityBehavior();
            $propagatingExecution = $flowScopeBehavior->createInnerInstance($this);
        } else {
            $propagatingExecution = $this->createConcurrentExecution();
        }

        $propagatingExecution->executeActivities(
            $activityStack,
            $targetActivity,
            $targetTransition,
            $variables,
            $localVariables,
            $skipCustomListeners,
            $skipIoMappings
        );
    }

    /**
     * Instantiates the given set of activities and returns the execution for the bottom-most activity
     */
    public function instantiateScopes(array &$activityStack, bool $skipCustomListeners, bool $skipIoMappings): array
    {
        if (empty($activityStack)) {
            return [];
        }

        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMapping = $skipIoMappings;

        $executionStartContext = new ScopeInstantiationContext();

        $instantiationStack = new InstantiationStack($activityStack);
        $executionStartContext->setInstantiationStack($instantiationStack);
        $this->setStartContext($executionStartContext);

        $this->performOperation(AtomicOperation::activityInitStackAndReturn());

        $createdExecutions = [];

        $currentExecution = $this;
        foreach ($activityStack as $instantiatedActivity) {
            // there must exactly one child execution
            $currentExecution = $currentExecution->getNonEventScopeExecutions()[0];
            if ($currentExecution->isConcurrent()) {
                // there may be a non-scope execution that we have to skip (e.g. multi-instance)
                $currentExecution = $currentExecution->getNonEventScopeExecutions()[0];
            }

            $createdExecutions[] = [$instantiatedActivity, $currentExecution];
        }

        return $createdExecutions;
    }

    /**
     * Instantiates the given activity stack. Uses this execution to execute the
     * highest activity in the stack.
     * Sets the variables for the execution responsible to execute the most deeply nested
     * activity.
     *
     * @param activityStack The most deeply nested activity is the last element in the list
     */
    public function executeActivities(
        array &$activityStack,
        ?PvmActivityInterface $targetActivity = null,
        ?PvmTransitionInterface $targetTransition = null,
        ?array $variables = [],
        ?array $localVariables = [],
        ?bool $skipCustomListeners = false,
        ?bool $skipIoMappings = false
    ): void {
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMapping = $skipIoMappings;
        $this->activityInstanceId = null;
        $this->isEnded = false;

        if (!empty($activityStack)) {
            $executionStartContext = new ScopeInstantiationContext();

            $instantiationStack = new InstantiationStack($activityStack, $targetActivity, $targetTransition);
            $executionStartContext->setInstantiationStack($instantiationStack);
            $executionStartContext->setVariables($variables);
            $executionStartContext->setVariablesLocal($localVariables);
            $this->setStartContext($executionStartContext);

            $this->performOperation(AtomicOperation::activityInitStack());
        } elseif ($targetActivity !== null) {
            $this->setVariables($variables);
            $this->setVariablesLocal($localVariables);
            $this->setActivity($targetActivity);
            $this->performOperation(AtomicOperation::activityStartCReateScope());
        } elseif ($targetTransition !== null) {
            $this->setVariables($variables);
            $this->setVariablesLocal($localVariables);
            $this->setActivity($targetTransition->getSource());
            $this->setTransition($targetTransition);
            $this->performOperation(AtomicOperation::trasitionStartNotifyListenerTake());
        }
    }

    public function findInactiveConcurrentExecutions(PvmActivityInterface $activity): array
    {
        $inactiveConcurrentExecutionsInActivity = [];
        if ($this->isConcurrent()) {
            return $this->getParent()->findInactiveChildExecutions($activity);
        } elseif (!$this->isActive()) {
            $inactiveConcurrentExecutionsInActivity[] = $this;
        }
        return $inactiveConcurrentExecutionsInActivity;
    }

    public function findInactiveChildExecutions(PvmActivityInterface $activity): array
    {
        $inactiveConcurrentExecutionsInActivity = [];
        $concurrentExecutions = $this->getAllChildExecutions();
        foreach ($concurrentExecutions as $concurrentExecution) {
            if ($concurrentExecution->getActivity() == $activity && !$concurrentExecution->isActive()) {
                $inactiveConcurrentExecutionsInActivity[] = $concurrentExecution;
            }
        }

        return $inactiveConcurrentExecutionsInActivity;
    }

    protected function getAllChildExecutions(): array
    {
        $childExecutions = [];
        foreach ($this->getExecutions() as $childExecution) {
            $childExecutions[] = $childExecution;
            $childExecutions = array_merge($childExecutions, $childExecution->getAllChildExecutions());
        }
        return $childExecutions;
    }

    public function leaveActivityViaTransition($outgoingTransition, ?array $_recyclableExecutions = []): void
    {
        if ($outgoingTransition instanceof PvmTransitionInterface) {
            $_transitions = [$outgoingTransition];
        } else {
            $_transitions = $outgoingTransition;
        }
        $recyclableExecutions = [];
        if (!empty($_recyclableExecutions)) {
            $recyclableExecutions = $_recyclableExecutions;
        }

        // if recyclable executions size is greater
        // than 1, then the executions are joined and
        // the activity is left with 'this' execution,
        // if it is not not the last concurrent execution.
        // therefore it is necessary to remove the local
        // variables (event if it is the last concurrent
        // execution).
        if (count($recyclableExecutions) > 1) {
            $this->removeVariablesLocalInternal();
        }

        // mark all recyclable executions as ended
        // if the list of recyclable executions also
        // contains 'this' execution, then 'this' execution
        // is also marked as ended. (if 'this' execution is
        // pruned, then the local variables are not copied
        // to the parent execution)
        // this is a workaround to not delete all recyclable
        // executions and create a new execution which leaves
        // the activity.
        foreach ($recyclableExecutions as $execution) {
            $execution->setEnded(true);
        }

        // remove 'this' from recyclable executions to
        // leave the activity with 'this' execution
        // (when 'this' execution is the last concurrent
        // execution, then 'this' execution will be pruned,
        // and the activity is left with the scope
        // execution)
        foreach ($recyclableExecutions as $key => $execution) {
            if ($execution == $this) {
                unset($recyclableExecutions[$key]);
            }
        }

        foreach ($recyclableExecutions as $execution) {
            $execution->end(empty($_transitions));
        }

        $propagatingExecution = this;
        if ($this->getReplacedBy() !== null) {
            $propagatingExecution = $this->getReplacedBy();
        }

        $propagatingExecution->isActive = true;
        $propagatingExecution->isEnded = false;

        if (empty($_transitions)) {
            $propagatingExecution->end(!$propagatingExecution->isConcurrent());
        } else {
            $propagatingExecution->setTransitionsToTake($_transitions);
            $propagatingExecution->performOperation(AtomicOperation::transitionNotifyListenerEnd());
        }
    }

    abstract protected function removeVariablesLocalInternal(): void;

    public function isActive(?string $activityId = null): bool
    {
        if ($activityId !== null) {
            return $this->findExecution($activityId) !== null;
        }
        $this->isActive;
    }

    public function inactivate(): void
    {
        $this->isActive = false;
    }

    // executions ///////////////////////////////////////////////////////////////

    abstract public function getExecutions(): array;

    abstract public function getExecutionsAsCopy(): array;

    public function getNonEventScopeExecutions(): array
    {
        $children = $this->getExecutions();
        $result = [];

        foreach ($children as $child) {
            if (!$child->isEventScope()) {
                $result[] = $child;
            }
        }

        return $result;
    }

    public function getEventScopeExecutions(): array
    {
        $children = $this->getExecutions();
        $result = [];

        foreach ($children as $child) {
            if ($child->isEventScope()) {
                $result[] = $child;
            }
        }

        return $result;
    }

    public function findExecution(string $activityId): ?PvmExecutionImpl
    {
        if (
            ($this->getActivity() !== null)
            && ($this->getActivity()->getId() == $activityId)
        ) {
            return $this;
        }
        foreach ($this->getExecutions() as $nestedExecution) {
            $result = $nestedExecution->findExecution($activityId);
            if ($result !== null) {
                return result;
            }
        }
        return null;
    }

    public function findExecutions(string $activityId): array
    {
        $matchingExecutions = [];
        $this->collectExecutions($activityId, $matchingExecutions);

        return $matchingExecutions;
    }

    protected function collectExecutions(string $activityId, array &$executions): array
    {
        if (
            ($this->getActivity() !== null)
            && ($this->getActivity()->getId() == $activityId)
        ) {
            $executions[] = $this;
        }

        foreach ($this->getExecutions() as $nestedExecution) {
            $nestedExecution->collectExecutions($activityId, $executions);
        }
    }

    public function findActiveActivityIds(): array
    {
        $activeActivityIds = [];
        $this->collectActiveActivityIds($activeActivityIds);
        return $activeActivityIds;
    }

    protected function collectActiveActivityIds(array &$activeActivityIds): void
    {
        $activity = $this->getActivity();
        if ($this->isActive && $activity !== null) {
            $activeActivityIds[] = $activity->getId();
        }

        foreach ($this->getExecutions() as $execution) {
            $execution->collectActiveActivityIds($activeActivityIds);
        }
    }

    // business key /////////////////////////////////////////

    public function getProcessBusinessKey(): ?string
    {
        return $this->getProcessInstance()->getBusinessKey();
    }

    public function setProcessBusinessKey(string $businessKey): void
    {
        $processInstance = $this->getProcessInstance();
        $processInstance->setBusinessKey($businessKey);

        $historyLevel = Context::getCommandContext()->getProcessEngineConfiguration()->getHistoryLevel();
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
    }

    public function getBusinessKey(): ?string
    {
        if ($this->isProcessInstanceExecution()) {
            return $this->businessKey;
        }
        return $this->getProcessBusinessKey();
    }

    // process definition ///////////////////////////////////////////////////////

    public function setProcessDefinition(ProcessDefinitionImpl $processDefinition): void
    {
        $this->processDefinition = $processDefinition;
    }

    public function getProcessDefinition(): ProcessDefinitionImpl
    {
        return $this->processDefinition;
    }

    // process instance /////////////////////////////////////////////////////////

    /**
     * ensures initialization and returns the process instance.
     */

    abstract public function getProcessInstance(): PvmExecutionImpl;

    abstract public function setProcessInstance(PvmExecutionImpl $pvmExecutionImpl): void;

    // case instance id /////////////////////////////////////////////////////////

    /*public function getCaseInstanceId(): ?string
    {
        return $this->caseInstanceId;
    }

    public function setCaseInstanceId(string $caseInstanceId): void
    {
        $this->caseInstanceId = $caseInstanceId;
    }*/

    // activity /////////////////////////////////////////////////////////////////

    /**
     * ensures initialization and returns the activity
     */
    public function getActivity(): ?ActivityImpl
    {
        return $this->activity;
    }

    public function getActivityId(): ?string
    {
        $activity = $this->getActivity();
        if ($activity !== null) {
            return $activity->getId();
        }
        return null;
    }

    public function getCurrentActivityName(): ?string
    {
        $activity = $this->getActivity();
        if ($activity !== null) {
            return $activity->getName();
        }
        return null;
    }

    public function getCurrentActivityId(): ?string
    {
        return $this->getActivityId();
    }

    public function setActivity(?PvmActivityInterface $activity = null): void
    {
        $this->activity = $activity;
    }

    public function enterActivityInstance(): void
    {
        $activity = $this->getActivity();
        $activityInstanceId = $this->generateActivityInstanceId($activity->getId());

        //LOG.debugEnterActivityInstance(this, getParentActivityInstanceId());

        // <LEGACY>: in general, io mappings may only exist when the activity is scope
        // however, for multi instance activities, the inner activity does not become a scope
        // due to the presence of an io mapping. In that case, it is ok to execute the io mapping
        // anyway because the multi-instance body already ensures variable isolation
        $this->executeIoMapping();

        if ($activity->isScope()) {
            $this->initializeTimerDeclarations();
        }

        $this->activityInstanceEndListenersFailed = false;
    }

    public function activityInstanceStarting(): void
    {
        $this->activityInstanceState = ActivityInstanceState::starting()->getStateCode();
    }

    public function activityInstanceStarted(): void
    {
        $this->activityInstanceState = ActivityInstanceState::default()->getStateCode();
    }

    public function activityInstanceDone(): void
    {
        $this->activityInstanceState = ActivityInstanceState::ending()->getStateCode();
    }

    public function activityInstanceEndListenerFailure(): void
    {
        $this->activityInstanceEndListenersFailed = true;
    }

    abstract protected function generateActivityInstanceId(string $activityId): string;

    public function leaveActivityInstance(): void
    {
        if ($this->activityInstanceId !== null) {
            //LOG.debugLeavesActivityInstance(this, activityInstanceId);
        }
        $this->activityInstanceId = $this->getParentActivityInstanceId();

        $this->activityInstanceState = ActivityInstanceState::default()->getStateCode();
        $this->activityInstanceEndListenersFailed = false;
    }

    public function getParentActivityInstanceId(): ?string
    {
        if ($this->isProcessInstanceExecution()) {
            return $this->getId();
        } else {
            return $this->getParent()->getActivityInstanceId();
        }
    }

    public function setActivityInstanceId(?string $activityInstanceId): void
    {
        $this->activityInstanceId = $activityInstanceId;
    }

    // parent ///////////////////////////////////////////////////////////////////

    /**
     * ensures initialization and returns the parent
     */
    abstract public function getParent(): ?PvmExecutionImpl;

    public function getParentId(): ?string
    {
        $parent = $this->getParent();
        if ($parent !== null) {
            return $parent->getId();
        }
        return null;
    }

    public function hasChildren(): bool
    {
        return !empty($this->getExecutions());
    }

    /**
     * Sets the execution's parent and updates the old and new parents' set of
     * child executions
     */
    public function setParent(PvmExecutionImpl $parent): void
    {
        $currentParent = $this->getParent();

        $this->setParentExecution($parent);

        if ($currentParent !== null) {
            $currentParent->removeExecution($this);
        }

        if ($parent !== null) {
            $parent->addExecution($this);
        }
    }

    /**
     * Use #setParent to also update the child execution sets
     */
    abstract public function setParentExecution(PvmExecutionImpl $parent): void;

    // super- and subprocess executions /////////////////////////////////////////

    abstract public function getSuperExecution(): ?PvmExecutionImpl;

    abstract public function setSuperExecution(PvmExecutionImpl $superExecution): void;

    abstract public function getSubProcessInstance(): ?PvmExecutionImpl;

    abstract public function setSubProcessInstance(?PvmExecutionImpl $subProcessInstance): void;

    // super case execution /////////////////////////////////////////////////////

    //abstract public function CmmnExecution getSuperCaseExecution();

    //abstract public function void setSuperCaseExecution(CmmnExecution superCaseExecution);

    // sub case execution ///////////////////////////////////////////////////////

    //abstract public function CmmnExecution getSubCaseInstance();

    //abstract public function void setSubCaseInstance(CmmnExecution subCaseInstance);

    // scopes ///////////////////////////////////////////////////////////////////

    protected function getScopeActivity(): ScopeImpl
    {
        $scope = null;
        // this if condition is important during process instance startup
        // where the activity of the process instance execution may not be aligned
        // with the execution tree
        if ($this->isProcessInstanceExecution()) {
            $scope = $this->getProcessDefinition();
        } else {
            $scope = $this->getActivity();
        }
        return $scope;
    }

    public function isScope(): bool
    {
        return $this->isScope;
    }

    public function setScope(bool $isScope): void
    {
        $this->isScope = $isScope;
    }

    /**
     * For a given target flow scope, this method returns the corresponding scope execution.
     * <p>
     * Precondition: the execution is active and executing an activity.
     * Can be invoked for scope and non scope executions.
     *
     * @param targetFlowScope mixed - scope activity or process definition for which the scope execution should be found
     * @return PvmExecutionImpl the scope execution for the provided targetFlowScope
     */
    public function findExecutionForFlowScope($targetFlowScope): ?PvmExecutionImpl
    {
        if (is_string($targetFlowScope)) {
            $targetScopeId = $targetFlowScope;
            EnsureUtil::ensureNotNull("target scope id", "targetScopeId", "targetScopeId", $targetScopeId);

            $currentActivity = $this->getActivity();
            EnsureUtil::ensureNotNull("activity of current execution", "currentActivity", $currentActivity);

            $walker = new FlowScopeWalker($currentActivity);
            $targetFlowScope = $walker->walkUntil(new class ($targetScopeId) implements WalkConditionInterface {

                private $targetScopeId;

                public function __construct(string $targetScopeId)
                {
                    $this->targetScopeId = $targetScopeId;
                }

                public function isFulfilled($scope = null): bool
                {
                    return $scope === null || $scope->getId() == $this->targetScopeId;
                }
            });

            if ($targetFlowScope === null) {
                //throw LOG.scopeNotFoundException(targetScopeId, this->getId());
            }

            return $this->findExecutionForFlowScope($targetFlowScope);
        } elseif ($targetFlowScope instanceof PvmScopeInterface) {
            // if this execution is not a scope execution, use the parent
            $scopeExecution = $this->isScope() ? $this : $this->getParent();

            $currentActivity = $this->getActivity();
            EnsureUtil::ensureNotNull("activity of current execution", "currentActivity", $currentActivity);

            // if this is a scope execution currently executing a non scope activity
            $currentActivity = $currentActivity->isScope() ? $currentActivity : $currentActivity->getFlowScope();

            return $scopeExecution->findExecutionForScope($currentActivity, $targetFlowScope);
        }
    }

    public function findExecutionForScope(ScopeImpl $currentScope, ScopeImpl $targetScope): ?PvmExecutionImpl
    {
        if (!$targetScope->isScope()) {
            throw new ProcessEngineException("Target scope must be a scope.");
        }

        $activityExecutionMapping = $this->createActivityExecutionMapping($currentScope);
        $scopeExecution = null;
        foreach ($activityExecutionMapping as $map) {
            if ($map[0] == $targetScope) {
                $scopeExecution = $map[1];
            }
        }
        if ($scopeExecution === null) {
            // the target scope is scope but no corresponding execution was found
            // => legacy behavior
            $scopeExecution = LegacyBehavior::getScopeExecution(targetScope, activityExecutionMapping);
        }
        return $scopeExecution;
    }

    protected function getFlowScopeExecution(): PvmExecutionImpl
    {
        if (!$this->isScope || CompensationBehavior::executesNonScopeCompensationHandler($this)) {
            // LEGACY: a correct implementation should also skip a compensation-throwing parent scope execution
            // (since compensation throwing activities are scopes), but this cannot be done for backwards compatibility
            // where a compensation throwing activity was no scope (and we would wrongly skip an execution in that case)
            return $this->getParent()->getFlowScopeExecution();
        }
        return $this;
    }

    protected function getFlowScope(): ScopeImpl
    {
        $activity = $this->getActivity();

        if (
            !$activity->isScope() || $this->activityInstanceId === null
            || ($activity->isScope() && !$this->isScope() && $activity->getActivityBehavior() instanceof CompositeActivityBehaviorInterface)
        ) {
            // if
            // - this is a scope execution currently executing a non scope activity
            // - or it is not scope but the current activity is (e.g. can happen during activity end, when the actual
            //   scope execution has been removed and the concurrent parent has been set to the scope activity)
            // - or it is asyncBefore/asyncAfter

            return $activity->getFlowScope();
        }
        return $activity;
    }

    /**
     * Creates an extended mapping based on this execution and the given existing mapping.
     * Any entry <code>mapping</code> in mapping that corresponds to an ancestor scope of
     * <code>currentScope</code> is reused.
     */
    protected function createActivityExecutionMapping(
        ?ScopeImpl $currentScope = null,
        ?array $mapping = null
    ): array {
        if ($currentScope !== null && $mapping !== null) {
            if (!$this->isScope()) {
                throw new ProcessEngineException("Execution must be a scope execution");
            }
            if (!$currentScope->isScope()) {
                throw new ProcessEngineException("Current scope must be a scope.");
            }

            // collect all ancestor scope executions unless one is encountered that is already in "mapping"
            $scopeExecutionCollector = new ScopeExecutionCollector();
            (new ExecutionWalker($this))
                ->addPreVisitor($scopeExecutionCollector)
                ->walkWhile(new class ($mapping) implements WalkConditionInterface {
                    private $mapping;

                    public function __construct(array $mapping)
                    {
                        $this->mapping = $mapping;
                    }

                    public function isFulfilled($element = null): bool
                    {
                        $contains = false;
                        foreach ($this->mapping as $map) {
                            if ($map[1] == $element) {
                                $contains = true;
                                break;
                            }
                        }
                        return $element === null || $contains;
                    }
                });
            $scopeExecutions = $scopeExecutionCollector->getScopeExecutions();

            // collect all ancestor scopes unless one is encountered that is already in "mapping"
            $scopeCollector = new ScopeCollector();
            (new FlowScopeWalker($currentScope))
                ->addPreVisitor($scopeCollector)
                ->walkWhile(new class ($mapping) implements WalkConditionInterface {
                    private $mapping;

                    public function __construct(array $mapping)
                    {
                        $this->mapping = $mapping;
                    }

                    public function isFulfilled($element = null): bool
                    {
                        $contains = false;
                        foreach ($this->mapping as $map) {
                            if ($map[0] == $element) {
                                $contains = true;
                                break;
                            }
                        }
                        return $element === null || $contains;
                    }
                });

            $scopes = $scopeCollector->getScopes();
            $outerScope = new \stdClass();
            $outerScope->scopes = $scopes;
            $outerScope->scopeExecutions = $scopeExecutions;

            // add all ancestor scopes and scopeExecutions that are already in "mapping"
            // and correspond to ancestors of the topmost previously collected scope
            $topMostScope = $scopes[count($scopes) - 1];
            (new FlowScopeWalker($topMostScope->getFlowScope()))
                ->addPreVisitor(new class ($outerScope, $mapping) implements TreeVisitorInterface {
                    private $outerScope;
                    private $mapping;

                    public function __construct($outerScope, $mapping)
                    {
                        $this->outerScope = $outerScope;
                        $this->mapping = $mapping;
                    }

                    public function visit($obj): void
                    {
                        $this->outerScope->scopes[] = $obj;

                        $priorMappingExecution = null;
                        foreach ($this->mapping as $map) {
                            if ($map[0] == $obj) {
                                $priorMappingExecution = $map[1];
                            }
                        }

                        $contains = false;
                        foreach ($this->outerScope->scopeExecutions as $exec) {
                            if ($exec == $priorMappingExecution) {
                                $contains = true;
                                break;
                            }
                        }

                        if ($priorMappingExecution !== null && !$contains) {
                            $this->outerScope->scopeExecutions[] = $priorMappingExecution;
                        }
                    }
                })
                ->walkWhile();

            $scopes = $outerScope->scopes;
            $scopeExecutions = $outerScope->scopeExecutions;

            if (count($scopes) == count($scopeExecutions)) {
                // the trees are in sync
                $result = [];
                for ($i = 0; $i < count($scopes); $i += 1) {
                    $result[] = [$scopes[$i], $scopeExecutions[$i]];
                }
                return $result;
            } else {
                // Wounderful! The trees are out of sync. This is due to legacy behavior
                return LegacyBehavior::createActivityExecutionMapping($scopeExecutions, $scopes);
            }
        } elseif ($currentScope !== null) {
            if (!$this->isScope()) {
                throw new ProcessEngineException("Execution must be a scope execution");
            }
            if (!$currentScope->isScope()) {
                throw new ProcessEngineException("Current scope must be a scope.");
            }

            // A single path in the execution tree from a leaf (no child executions) to the root
            // may in fact contain multiple executions that correspond to leaves in the activity instance hierarchy.
            //
            // This is because compensation throwing executions have child executions. In that case, the
            // flow scope hierarchy is not aligned with the scope execution hierarchy: There is a scope
            // execution for a compensation-throwing event that is an ancestor of this execution,
            // while these events are not ancestor scopes of currentScope.
            //
            // The strategy to deal with this situation is as follows:
            // 1. Determine all executions that correspond to leaf activity instances
            // 2. Order the leaf executions in top-to-bottom fashion
            // 3. Iteratively build the activity execution mapping based on the leaves in top-to-bottom order
            //    3.1. For the first leaf, create the activity execution mapping regularly
            //    3.2. For every following leaf, rebuild the mapping but reuse any scopes and scope executions
            //         that are part of the mapping created in the previous iteration
            //
            // This process ensures that the resulting mapping does not contain scopes that are not ancestors
            // of currentScope and that it does not contain scope executions for such scopes.
            // For any execution hierarchy that does not involve compensation, the number of iterations in step 3
            // should be 1, i.e. there are no other leaf activity instance executions in the hierarchy.

            // 1. Find leaf activity instance executions
            $leafCollector = new LeafActivityInstanceExecutionCollector();
            (new ExecutionWalker($this))->addPreVisitor($leafCollector)->walkUntil();

            $leafCollector->removeLeaf($this);
            $leaves = $leafCollector->getLeaves();

            // 2. Order them from top to bottom
            $leaves = array_reverse($leaves);

            // 3. Iteratively extend the mapping for every additional leaf
            $mapping = [];
            foreach ($leaves as $leaf) {
                $leafFlowScope = $leaf->getFlowScope();
                $leafFlowScopeExecution = $leaf->getFlowScopeExecution();

                $mapping = $leafFlowScopeExecution->createActivityExecutionMapping($leafFlowScope, $mapping);
            }

            // finally extend the mapping for the current execution
            // (note that the current execution need not be a leaf itself)
            $mapping = $this->createActivityExecutionMapping($currentScope, $mapping);

            return $mapping;
        } elseif ($currentScope === null && $mapping === null) {
            $currentActivity = $this->getActivity();
            EnsureUtil::ensureNotNull("activity of current execution", "currentActivity", $currentActivity);

            $flowScope = $this->getFlowScope();
            $flowScopeExecution = $this->getFlowScopeExecution();

            return $flowScopeExecution->createActivityExecutionMapping($flowScope);
        }
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

    // variables ////////////////////////////////////////////

    public function getVariableScopeKey(): string
    {
        return "execution";
    }

    public function getParentVariableScope(): ?AbstractVariableScope
    {
        return $this->getParent();
    }

    /**
     * {@inheritDoc}
     */
    public function setVariable(string $variableName, $value, ?string $targetActivityId = null): void
    {
        $activityId = $this->getActivityId();
        if (!empty($activityId) && $activityId == $targetActivityId) {
            $this->setVariableLocal($variableName, $value);
        } else {
            $executionForFlowScope = $this->findExecutionForFlowScope($targetActivityId);
            if ($executionForFlowScope !== null) {
                $executionForFlowScope->setVariableLocal($variableName, $value);
            }
        }
    }

    // sequence counter ///////////////////////////////////////////////////////////

    public function getSequenceCounter(): int
    {
        return $this->sequenceCounter;
    }

    public function setSequenceCounter(int $sequenceCounter): void
    {
        $this->sequenceCounter = $sequenceCounter;
    }

    public function incrementSequenceCounter(): void
    {
        $this->sequenceCounter += 1;
    }

    // Getter / Setters ///////////////////////////////////

    public function isExternallyTerminated(): bool
    {
        return $this->externallyTerminated;
    }

    public function setExternallyTerminated(bool $externallyTerminated): void
    {
        $this->externallyTerminated = $externallyTerminated;
    }

    public function getDeleteReason(): ?string
    {
        return $this->deleteReason;
    }

    public function setDeleteReason(string $deleteReason): void
    {
        $this->deleteReason = $deleteReason;
    }

    public function isDeleteRoot(): bool
    {
        return $this->deleteRoot;
    }

    public function setDeleteRoot(bool $deleteRoot): void
    {
        $this->deleteRoot = $deleteRoot;
    }

    public function getTransition(): ?TransitionImpl
    {
        return $this->transition;
    }

    public function getTransitionsToTake(): array
    {
        return $this->transitionsToTake;
    }

    public function setTransitionsToTake(array $transitionsToTake): void
    {
        $this->transitionsToTake = $transitionsToTake;
    }

    public function getCurrentTransitionId(): ?string
    {
        $transition = $this->getTransition();
        if ($transition !== null) {
            return $transition->getId();
        }
        return null;
    }

    public function setTransition(PvmTransitionInterface $transition): void
    {
        $this->transition = $transition;
    }

    public function isConcurrent(): bool
    {
        return $this->isConcurrent;
    }

    public function setConcurrent(bool $isConcurrent): void
    {
        $this->isConcurrent = $isConcurrent;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function setEnded(bool $isEnded): void
    {
        $this->isEnded = $isEnded;
    }

    public function isEnded(): bool
    {
        return $this->isEnded;
    }

    public function isCanceled(): bool
    {
        return ActivityInstanceState::canceled()->getStateCode() == $this->activityInstanceState;
    }

    public function setCanceled(bool $canceled): void
    {
        if ($this->canceled) {
            $this->activityInstanceState = ActivityInstanceState::canceled()->getStateCode();
        }
    }

    public function isCompleteScope(): bool
    {
        return ActivityInstanceState::scopeComplete()->getStateCode() == $this->activityInstanceState;
    }

    public function setCompleteScope(bool $completeScope): void
    {
        if ($completeScope && !$this->isCanceled()) {
            $this->activityInstanceState = ActivityInstanceState::scopeComplete()->getStateCode();
        }
    }

    public function setPreserveScope(bool $preserveScope): void
    {
        $this->preserveScope = $preserveScope;
    }

    public function isPreserveScope(): bool
    {
        return $this->preserveScope;
    }

    public function getActivityInstanceState(): int
    {
        return $this->activityInstanceState;
    }

    public function isInState(ActivityInstanceState $state): bool
    {
        return $this->activityInstanceState == $state->getStateCode();
    }

    public function hasFailedOnEndListeners(): bool
    {
        return $this->activityInstanceEndListenersFailed;
    }

    public function isEventScope(): bool
    {
        return $this->isEventScope;
    }

    public function setEventScope(bool $isEventScope): void
    {
        $this->isEventScope = $isEventScope;
    }

    public function getScopeInstantiationContext(): ?ScopeInstantiationContext
    {
        return $this->scopeInstantiationContext;
    }

    public function disposeScopeInstantiationContext(): void
    {
        $this->scopeInstantiationContext = null;

        $parent = $this;
        while (($parent = $parent->getParent()) !== null && $parent->scopeInstantiationContext !== null) {
            $parent->scopeInstantiationContext = null;
        }
    }

    public function getNextActivity(): PvmActivityInterface
    {
        return $this->nextActivity;
    }

    public function isProcessInstanceExecution(): bool
    {
        return $this->getParent() === null;
    }

    public function setStartContext(ScopeInstantiationContext $startContext): void
    {
        $this->scopeInstantiationContext = $startContext;
    }

    public function setIgnoreAsync(bool $ignoreAsync): void
    {
        $this->ignoreAsync = $ignoreAsync;
    }

    public function isIgnoreAsync(): bool
    {
        return $this->ignoreAsync;
    }

    public function setStarting(bool $isStarting): void
    {
        $this->isStarting = $isStarting;
    }

    public function isStarting(): bool
    {
        return $this->isStarting;
    }

    public function isProcessInstanceStarting(): bool
    {
        return $this->getProcessInstance()->isStarting();
    }

    public function setProcessInstanceStarting(bool $starting): void
    {
        $this->getProcessInstance()->setStarting($starting);
    }

    public function setNextActivity(PvmActivityInterface $nextActivity): void
    {
        $this->nextActivity = $nextActivity;
    }

    public function getParentScopeExecution(bool $considerSuperExecution): PvmExecutionImpl
    {
        if ($this->isProcessInstanceExecution()) {
            if ($considerSuperExecution && $this->getSuperExecution() !== null) {
                $superExecution = $this->getSuperExecution();
                if ($superExecution->isScope()) {
                    return $superExecution;
                } else {
                    return $superExecution->getParent();
                }
            } else {
                return null;
            }
        } else {
            $parent = $this->getParent();
            if ($parent->isScope()) {
                return $parent;
            } else {
                return $parent->getParent();
            }
        }
    }

    /**
     * Contains the delayed variable events, which will be dispatched on a save point.
     */
    protected $delayedEvents = [];

    /**
     * Delays and stores the given DelayedVariableEvent on the process instance.
     *
     * @param delayedVariableEvent the DelayedVariableEvent which should be store on the process instance
     */
    public function delayEvent($target, ?VariableEvent $variableEvent = null): void
    {
        if ($target instanceof PvmExecutionImpl) {
            $delayedVariableEvent = new DelayedVariableEvent($target, $variableEvent);
            $this->delayEvent($delayedVariableEvent);
        } elseif ($target instanceof DelayedVariableEvent) {
            //if process definition has no conditional events the variable events does not have to be delayed
            $hasConditionalEvents = $this->getProcessDefinition()->getProperties()->get(BpmnProperties::hasConditionalEvents());
            if ($hasConditionalEvents === null || $hasConditionalEvents != true) {
                return;
            }

            if ($this->isProcessInstanceExecution()) {
                $this->delayedEvents[] = $target;
            } else {
                $this->getProcessInstance()->delayEvent($target);
            }
        }
    }

    /**
     * The current delayed variable events.
     *
     * @return a list of DelayedVariableEvent objects
     */
    public function getDelayedEvents(): array
    {
        if ($this->isProcessInstanceExecution()) {
            return $this->delayedEvents;
        }
        return $this->getProcessInstance()->getDelayedEvents();
    }

    /**
     * Cleares the current delayed variable events.
     */
    public function clearDelayedEvents(): void
    {
        if ($this->isProcessInstanceExecution()) {
            $this->delayedEvents = [];
        } else {
            $this->getProcessInstance()->clearDelayedEvents();
        }
    }

    /**
     * Dispatches the current delayed variable events and performs the given atomic operation
     * if the current state was not changed.
     *
     * @param continuation the atomic operation continuation which should be executed
     */
    public function dispatchDelayedEventsAndPerformOperation($continuation = null): void
    {
        if ($continuation instanceof PvmAtomicOperationInterface) {
            $this->dispatchDelayedEventsAndPerformOperation(new class ($continuation) implements CallbackInterface {
                private $atomicOperation;

                public function __construct(PvmAtomicOperationInterface $atomicOperation)
                {
                    $this->atomicOperation = $atomicOperation;
                }

                public function callback($param)
                {
                    $param->performOperation($this->atomicOperation);
                    return null;
                }
            });
        } elseif ($continuation instanceof CallbackInterface) {
            $execution = $this;

            if (empty($execution->getDelayedEvents())) {
                $this->continueExecutionIfNotCanceled($continuation, $execution);
                return;
            }

            $this->continueIfExecutionDoesNotAffectNextOperation(new class ($this) implements CallbackInterface {
                private $scope;

                public function __construct(PvmExecutionImpl $scope)
                {
                    $this->scope = $scope;
                }

                public function callback(PvmExecutionImpl $execution)
                {
                    $this->scope->dispatchScopeEvents($execution);
                    return null;
                }
            }, new class () implements CallbackInterface {
                private $scope;
                private $continuation;

                public function __construct(PvmExecutionImpl $scope, $continuation)
                {
                    $this->scope = $scope;
                    $this->continuation = $continuation;
                }

                public function callback(PvmExecutionImpl $execution)
                {
                    $this->scope->continueExecutionIfNotCanceled($this->continuation, $execution);
                    return null;
                }
            }, $execution);
        }
    }

    /**
     * Executes the given depending operations with the given execution.
     * The execution state will be checked with the help of the activity instance id and activity id of the execution before and after
     * the dispatching callback call. If the id's are not changed the
     * continuation callback is called.
     *
     * @param dispatching         the callback to dispatch the variable events
     * @param continuation        the callback to continue with the next atomic operation
     * @param execution           the execution which is used for the execution
     */
    public function continueIfExecutionDoesNotAffectNextOperation(
        CallbackInterface $dispatching,
        CallbackInterface $continuation,
        PvmExecutionImpl $execution
    ): void {
        $lastActivityId = $execution->getActivityId();
        $lastActivityInstanceId = $this->getActivityInstanceId($execution);

        $dispatching->callback($execution);

        $execution = $execution->getReplacedBy() !== null ? $execution->getReplacedBy() : $execution;
        $currentActivityInstanceId = $this->getActivityInstanceId($execution);
        $currentActivityId = $execution->getActivityId();

        //if execution was canceled or was changed during the dispatch we should not execute the next operation
        //since another atomic operation was executed during the dispatching
        if (!$execution->isCanceled() && $this->isOnSameActivity($lastActivityInstanceId, $lastActivityId, $currentActivityInstanceId, $currentActivityId)) {
            $continuation->callback($execution);
        }
    }

    protected function continueExecutionIfNotCanceled(?CallbackInterface $continuation, PvmExecutionImpl $execution): void
    {
        if ($continuation !== null && !$execution->isCanceled()) {
            $continuation->callback($execution);
        }
    }

    /**
     * Dispatches the current delayed variable events on the scope of the given execution.
     *
     * @param execution the execution on which scope the delayed variable should be dispatched
     */
    protected function dispatchScopeEvents(PvmExecutionImpl $execution): void
    {
        $scopeExecution = $execution->isScope() ? $execution : $execution->getParent();

        $delayedEvents = $scopeExecution->getDelayedEvents();
        $scopeExecution->clearDelayedEvents();

        $activityInstanceIds = [];
        $activityIds = [];
        $this->initActivityIds($delayedEvents, $activityInstanceIds, $activityIds);

        //For each delayed variable event we have to check if the delayed event can be dispatched,
        //the check will be done with the help of the activity id and activity instance id.
        //That means it will be checked if the dispatching changed the execution tree in a way that we can't dispatch the
        //the other delayed variable events. We have to check the target scope with the last activity id and activity instance id
        //and also the replace pointer if it exist. Because on concurrency the replace pointer will be set on which we have
        //to check the latest state.
        foreach ($delayedEvents as $event) {
            $targetScope = $event->getTargetScope();
            $replaced = $targetScope->getReplacedBy() !== null ? $targetScope->getReplacedBy() : $targetScope;
            $this->dispatchOnSameActivity($targetScope, $replaced, $activityIds, $activityInstanceIds, $event);
        }
    }

    /**
     * Initializes the given maps with the target scopes and current activity id's and activity instance id's.
     *
     * @param delayedEvents       the delayed events which contains the information about the target scope
     * @param activityInstanceIds the map which maps target scope to activity instance id
     * @param activityIds         the map which maps target scope to activity id
     */
    protected function initActivityIds(
        array $delayedEvents,
        array &$activityInstanceIds,
        array &$activityIds
    ): void {
        foreach ($delayedEvents as $event) {
            $targetScope = $event->getTargetScope();

            $targetScopeActivityInstanceId = $this->getActivityInstanceId($targetScope);
            $activityInstanceIds[] = [$targetScope, $targetScopeActivityInstanceId];
            $activityIds[] = [$targetScope, $targetScope->getActivityId()];
        }
    }

    /**
     * Dispatches the delayed variable event, if the target scope and replaced by scope (if target scope was replaced) have the
     * same activity Id's and activity instance id's.
     *
     * @param targetScope          the target scope on which the event should be dispatched
     * @param replacedBy           the replaced by pointer which should have the same state
     * @param activityIds          the map which maps scope to activity id
     * @param activityInstanceIds  the map which maps scope to activity instance id
     * @param delayedVariableEvent the delayed variable event which should be dispatched
     */
    private function dispatchOnSameActivity(
        PvmExecutionImpl $targetScope,
        PvmExecutionImpl $replacedBy,
        array $activityIds,
        array $activityInstanceIds,
        DelayedVariableEvent $delayedVariableEvent
    ): void {
        //check if the target scope has the same activity id and activity instance id
        //since the dispatching was started
        $currentActivityInstanceId = $this->getActivityInstanceId($targetScope);
        $currentActivityId = $targetScope->getActivityId();

        $lastActivityInstanceId = null;
        foreach ($activityInstanceIds as $map) {
            if ($map[0] == $targetScope) {
                $lastActivityInstanceId = $map[1];
            }
        }

        $lastActivityId = null;
        foreach ($activityIds as $map) {
            if ($map[0] == $targetScope) {
                $lastActivityId = $map[1];
            }
        }

        $onSameAct = $this->isOnSameActivity($lastActivityInstanceId, $lastActivityId, $currentActivityInstanceId, $currentActivityId);

        //If not we have to check the replace pointer,
        //which was set if a concurrent execution was created during the dispatching.
        if ($targetScope != $replacedBy && !$onSameAct) {
            $currentActivityInstanceId = $this->getActivityInstanceId($replacedBy);
            $currentActivityId = $replacedBy->getActivityId();
            $onSameAct = $this->isOnSameActivity($lastActivityInstanceId, $lastActivityId, $currentActivityInstanceId, $currentActivityId);
        }

        //dispatching
        if ($onSameAct && $this->isOnDispatchableState($targetScope)) {
            $targetScope->dispatchEvent($delayedVariableEvent->getEvent());
        }
    }

    /**
     * Checks if the given execution is on a dispatchable state.
     * That means if the current activity is not a leaf in the activity tree OR
     * it is a leaf but not a scope OR it is a leaf, a scope
     * and the execution is in state DEFAULT, which means not in state
     * Starting, Execute or Ending. For this states it is
     * prohibited to trigger conditional events, otherwise unexpected behavior can appear.
     *
     * @return bool - true if the execution is on a dispatchable state, false otherwise
     */
    private function isOnDispatchableState(PvmExecutionImpl $targetScope): bool
    {
        $targetActivity = $targetScope->getActivity();
        return
            //if not leaf, activity id is null -> dispatchable
            $targetScope->getActivityId() === null ||
            // if leaf and not scope -> dispatchable
            !$targetActivity->isScope() ||
            // if leaf, scope and state in default -> dispatchable
            ($targetScope->isInState(ActivityInstanceState::default()));
    }

    /**
     * Compares the given activity instance id's and activity id's to check if the execution is on the same
     * activity as before an operation was executed. The activity instance id's can be null on transitions.
     * In this case the activity Id's have to be equal, otherwise the execution changed.
     *
     * @param string|null - lastActivityInstanceId    the last activity instance id
     * @param string|null - lastActivityId            the last activity id
     * @param string|null - currentActivityInstanceId the current activity instance id
     * @param string|null - currentActivityId         the current activity id
     * @return bool - true if the execution is on the same activity, otherwise false
     */
    private function isOnSameActivity(
        ?string $lastActivityInstanceId,
        ?string $lastActivityId,
        ?string $currentActivityInstanceId,
        ?string $currentActivityId
    ): bool {
        return
            //activityInstanceId's can be null on transitions, so the activityId must be equal
            (($lastActivityInstanceId === null && $lastActivityInstanceId == $currentActivityInstanceId && $lastActivityId == $currentActivityId)
            //if activityInstanceId's are not null they must be equal -> otherwise execution changed
            || ($lastActivityInstanceId !== null && $lastActivityInstanceId == $currentActivityInstanceId
            && ($lastActivityId === null || $lastActivityId == $currentActivityId)));
    }

    /**
     * Returns the activity instance id for the given execution.
     *
     * @param PvmExecutionImpl|null - targetScope the execution for which the activity instance id should be returned
     * @return string the activity instance id
     */
    private function getActivityInstanceId(?PvmExecutionImpl $targetScope = null): ?string
    {
        if ($targetScope !== null) {
            if ($targetScope->isConcurrent()) {
                return $targetScope->getActivityInstanceId();
            } else {
                $targetActivity = $targetScope->getActivity();
                if (($targetActivity !== null && empty($targetActivity->getActivities()))) {
                    return $targetScope->getActivityInstanceId();
                }
                return $targetScope->getParentActivityInstanceId();
            }
        }
        return $this->activityInstanceId;
    }

    /**
     * Returns the newest incident in this execution
     *
     * @param incidentType the type of new incident
     * @param configuration configuration of the incident
     * @return new incident
     */
    public function createIncident(string $incidentType, string $configuration, ?string $message = null): IncidentInterface
    {
        $incidentContext = $this->createIncidentContext($configuration);

        return IncidentHandling::createIncident($incidentType, $incidentContext, $message);
    }

    protected function createIncidentContext(string $configuration): IncidentContext
    {
        $incidentContext = new IncidentContext();

        $incidentContext->setTenantId($this->getTenantId());
        $incidentContext->setProcessDefinitionId($this->getProcessDefinitionId());
        $incidentContext->setExecutionId($this->getId());
        $incidentContext->setActivityId($this->getActivityId());
        $incidentContext->setConfiguration($configuration);

        return $incidentContext;
    }

    /**
     * Resolves an incident with given id.
     *
     * @param incidentId
     */
    public function resolveIncident(string $incidentId): void
    {
        $incident = Context::getCommandContext()
            ->getIncidentManager()
            ->findIncidentById($incidentId);

        $incidentContext = new IncidentContext($incident);
        IncidentHandling::removeIncidents($incident->getIncidentType(), $incidentContext, true);
    }

    public function findIncidentHandler(string $incidentType): ?IncidentHandlerInterface
    {
        $incidentHandlers = Context::getProcessEngineConfiguration()->getIncidentHandlers();
        if (array_key_exists($incidentType, $incidentHandlers)) {
            return $incidentHandlers[$incidentType];
        }
        return null;
    }
}
