<?php

namespace Jabe\Impl\Pvm\Runtime;

use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Behavior\{
    BpmnBehaviorLogger,
    CancelBoundaryEventActivityBehavior,
    CancelEndEventActivityBehavior,
    CompensationEventActivityBehavior,
    EventSubProcessActivityBehavior,
    MultiInstanceActivityBehavior,
    ReceiveTaskActivityBehavior,
    SequentialMultiInstanceActivityBehavior,
    SubProcessActivityBehavior
};
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Cmd\GetActivityInstanceCmd;
use Jabe\Impl\Persistence\Entity\{
    ActivityInstanceImpl,
    EventSubscriptionEntity,
    ExecutionEntity,
    JobDefinitionEntity,
    ProcessDefinitionEntity,
    VariableInstanceEntity,
    VariableInstanceHistoryListener
};
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface,
    CompositeActivityBehaviorInterface
};
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Impl\Tree\{
    ExecutionWalker,
    ReferenceWalker
};

class LegacyBehavior
{

    //private final static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    // concurrent scopes ///////////////////////////////////////////

    /**
     * Prunes a concurrent scope. This can only happen if
     * (a) the process instance has been migrated from a previous version to a new version of the process engine
     *
     * This is an inverse operation to {@link #createConcurrentScope(PvmExecutionImpl)}.
     *
     * See: javadoc of this class for note about concurrent scopes.
     *
     * @param execution
     */
    public static function pruneConcurrentScope(PvmExecutionImpl $execution): void
    {
    }

    /**
     * Cancels an execution which is both concurrent and scope. This can only happen if
     * (a) the process instance has been migrated from a previous version to a new version of the process engine
     *
     * See: javadoc of this class for note about concurrent scopes.
     *
     * @param execution the concurrent scope execution to destroy
     * @param cancelledScopeActivity the activity that cancels the execution; it must hold that
     *  cancellingActivity's event scope is the scope the execution is responsible for
     */
    public static function cancelConcurrentScope(PvmExecutionImpl $execution, PvmActivityInterface $cancelledScopeActivity): void
    {
    }

    /**
     * Destroys a concurrent scope Execution. This can only happen if
     * (a) the process instance has been migrated from a previous version to a 7.3+ version of the process engine
     *
     * See: javadoc of this class for note about concurrent scopes.
     *
     * @param execution the execution to destroy
     */
    public static function destroyConcurrentScope(PvmExecutionImpl $execution): void
    {
    }

    // sequential multi instance /////////////////////////////////

    public static function eventSubprocessComplete(ActivityExecutionInterface $scopeExecution): bool
    {
        return false;
    }

    public static function eventSubprocessConcurrentChildExecutionEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): bool
    {
        return false;
    }

    /**
     * Destroy an execution for an activity that was previously not a scope and now is
     * (e.g. event subprocess)
     */
    public static function destroySecondNonScope(PvmExecutionImpl $execution): bool
    {
        self::ensureScope($execution);
        $performLegacyBehavior = self::isLegacyBehaviorRequired($execution);

        if ($performLegacyBehavior) {
            // legacy behavior is to do nothing
        }

        return $performLegacyBehavior;
    }

    protected static function ensureScope(PvmExecutionImpl $execution): void
    {
        if (!$execution->isScope()) {
            throw new ProcessEngineException("Execution must be scope.");
        }
    }

    protected static function isLegacyBehaviorRequired(ActivityExecutionInterface $scopeExecution): bool
    {
        // legacy behavior is turned off: the current activity was parsed as scope.
        // now we need to check whether a scope execution was correctly created for the
        // event subprocess.
        // first create the mapping:
        $activityExecutionMapping = $scopeExecution->createActivityExecutionMapping();
        // if the scope execution for the current activity is the same as for the parent scope
        // -> we need to perform legacy behavior
        $activity = $scopeExecution->getActivity();
        if (!$activity->isScope()) {
            $activity = $activity->getFlowScope();
        }

        foreach ($activityExecutionMapping as $map) {
            if ($map[0] == $activity) {
                foreach ($activityExecutionMapping as $map2) {
                    if ($map2[0] == $activity->getFlowScope()) {
                        return $map[1] == $map2[1];
                    }
                }
                return false;
            }
        }
        return false;
    }

    /**
     * In case the process instance was migrated from a previous version, activities which are now parsed as scopes
     * do not have scope executions. Use the flow scopes of these activities in order to find their execution.
     * - For an event subprocess this is the scope execution of the scope in which the event subprocess is embeded in
     * - For a multi instance sequential subprocess this is the multi instace scope body.
     *
     * @param scope
     * @param activityExecutionMapping
     * @return
     */
    public static function getScopeExecution(ScopeImpl $scope, array $activityExecutionMapping): ?PvmExecutionImpl
    {
        $flowScope = $scope->getFlowScope();
        foreach ($activityExecutionMapping as $map) {
            if ($map[0] == $flowScope) {
                return $map[1];
            }
        }
        return null;
    }

    /**
     * Creates an activity execution mapping, when the scope hierarchy and the execution hierarchy are out of sync.
     *
     * @param scopeExecutions
     * @param scopes
     * @return
     */
    public static function createActivityExecutionMapping(array $scopeExecutions, array $scopes): array
    {
        return [];
    }

    /**
     * Tolerates the broken execution trees fixed with CAM-3727 where there may be more
     * ancestor scope executions than ancestor flow scopes;
     *
     * In that case, the argument execution is removed, the parent execution of the argument
     * is returned such that one level of mismatch is corrected.
     *
     * Note that this does not necessarily skip the correct scope execution, since
     * the broken parent-child relationships may be anywhere in the tree (e.g. consider a non-interrupting
     * boundary event followed by a subprocess (i.e. scope), when the subprocess ends, we would
     * skip the subprocess's execution).
     *
     */
    public static function determinePropagatingExecutionOnEnd(PvmExecutionImpl $propagatingExecution, array $activityExecutionMapping): ?PvmExecutionImpl
    {
        if (!$propagatingExecution->isScope()) {
            // non-scope executions may end in the "wrong" flow scope
            return $propagatingExecution;
        } else {
            // superfluous scope executions won't be contained in the activity-execution mapping
            foreach ($activityExecutionMapping as $map) {
                if ($map[1] == $propagatingExecution) {
                    return $propagatingExecution;
                }
            }
            $propagatingExecution->remove();
            $parent = $propagatingExecution->getParent();
            $parent->setActivity($propagatingExecution->getActivity());
            return $propagatingExecution->getParent();
        }
    }

    /**
     * Concurrent + scope executions are legacy and could occur in processes with non-interrupting
     * boundary events or event subprocesses
     */
    public static function isConcurrentScope(PvmExecutionImpl $propagatingExecution): bool
    {
        return $propagatingExecution->isConcurrent() && $propagatingExecution->isScope();
    }

    /**
     * <p>Required for migrating active sequential MI receive tasks. These activities were formerly not scope,
     * but are now. This has the following implications:
     *
     * <p>Before migration:
     * <ul><li> the event subscription is attached to the miBody scope execution</ul>
     *
     * <p>After migration:
     * <ul><li> a new subscription is created for every instance
     * <li> the new subscription is attached to a dedicated scope execution as a child of the miBody scope
     *   execution</ul>
     *
     * <p>Thus, this method removes the subscription on the miBody scope
     */
    public static function removeLegacySubscriptionOnParent(ExecutionEntity $execution, EventSubscriptionEntity $eventSubscription): void
    {
    }

    /**
     * Remove all entries for legacy non-scopes given that the assigned scope execution is also responsible for another scope
     */
    public static function removeLegacyNonScopesFromMapping(array $mapping): void
    {
    }

    /**
     * This is relevant for GetActivityInstanceCmd where in case of legacy multi-instance execution trees, the default
     * algorithm omits multi-instance activity instances.
     */
    public static function repairParentRelationships(array $values, ?string $processInstanceId): void
    {
    }

    /**
     * When deploying an async job definition for an activity wrapped in an miBody, set the activity id to the
     * miBody except the wrapped activity is marked as async.
     *
     * Background: in <= 7.2 async job definitions were created for the inner activity, although the
     * semantics are that they are executed before the miBody is entered
     */
    public static function migrateMultiInstanceJobDefinitions(ProcessDefinitionEntity $processDefinition, array $jobDefinitions): void
    {
    }

    /**
     * When executing an async job for an activity wrapped in an miBody, set the execution to the
     * miBody except the wrapped activity is marked as async.
     *
     * Background: in <= 7.2 async jobs were created for the inner activity, although the
     * semantics are that they are executed before the miBody is entered
     */
    public static function repairMultiInstanceAsyncJob(ExecutionEntity $execution): void
    {
    }

    /**
     * With prior versions, the boundary event was already executed when compensation was performed; Thus, after
     * compensation completes, the execution is signalled waiting at the boundary event.
     */
    public static function signalCancelBoundaryEvent(?string $signalName): bool
    {
        return false;
    }

    /**
     * @see #signalCancelBoundaryEvent(String)
     */
    public static function parseCancelBoundaryEvent(ActivityImpl $activity): void
    {
        $activity->setProperty(BpmnParse::PROPERTYNAME_THROWS_COMPENSATION, true);
    }

    /**
     * <p>In general, only leaf executions have activity ids.</p>
     * <p>Exception to that rule: compensation throwing executions.</p>
     * <p>Legacy exception (<= 7.2) to that rule: miBody executions and parallel gateway executions</p>
     *
     * @return bool - true, if the argument is not a leaf and has an invalid (i.e. legacy) non-null activity id
     */
    public static function hasInvalidIntermediaryActivityId(PvmExecutionImpl $execution): bool
    {
        return !empty($execution->getNonEventScopeExecutions()) && !CompensationBehavior::isCompensationThrowing($execution);
    }

    /**
     * Returns true if the given execution is in a compensation-throwing activity but there is no dedicated scope execution
     * in the given mapping.
     */
    public static function isCompensationThrowing(PvmExecutionImpl $execution, ?array $activityExecutionMapping = null): bool
    {
        $activityExecutionMapping = $activityExecutionMapping ?? $execution->createActivityExecutionMapping();
        if (CompensationBehavior::isCompensationThrowing($execution)) {
            $compensationThrowingActivity = $execution->getActivity();

            if ($compensationThrowingActivity->isScope()) {
                foreach ($activityExecutionMapping as $map1) {
                    if ($map1[0] == $compensationThrowingActivity) {
                        foreach ($activityExecutionMapping as $map2) {
                            if ($map2[0] == $compensationThrowingActivity->getFlowScope()) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            } else {
                // for transaction sub processes with cancel end events, the compensation throwing execution waits in the boundary event, not in the end
                // event; cancel boundary events are currently not scope
                return $compensationThrowingActivity->getActivityBehavior() instanceof CancelBoundaryEventActivityBehavior;
            }
        }
        return false;
    }

    /**
     * See #CAM-10978
     * Use case process instance with <code>asyncBefore</code> startEvent
     * After unifying the history variable's creation<br>
     * The following changed:<br>
     *   * variables will receive the <code>processInstanceId</code> as <code>activityInstanceId</code> in such cases (previously was the startEvent id)<br>
     *   * historic details have new <code>initial</code> property to track initial variables that process is started with<br>
     * The jobs created prior <code>7.13</code> and not executed before do not have historic information of variables.
     * This method takes care of that.
     */
    public static function createMissingHistoricVariables(PvmExecutionImpl $execution): void
    {
    }
}
