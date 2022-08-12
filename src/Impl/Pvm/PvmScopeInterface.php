<?php

namespace Jabe\Impl\Pvm;

use Jabe\Impl\Pvm\Process\{
    ScopeImpl,
    TransitionImpl
};

interface PvmScopeInterface extends PvmProcessElementInterface
{
    /**
     * Indicates whether this is a local scope for variables and events
     * if true, there will _always_ be a scope execution created for it.
     *<p>
    * Note: the fact that this is a scope does not mean that it is also a
    * {@link #isSubProcessScope() sub process scope.}
    *
    * @returns true if this activity is a scope
    */
    public function isScope(): bool;

    /** Indicates whether this scope is a sub process scope.
     * A sub process scope is a scope which contains "normal flow".Scopes which are flow scopes but not sub process scopes:
     * <ul>
     * <li>a multi instance body scope</li>
     * <li>leaf scope activities which are pure event scopes (Example: User task with attached boundary event)</li>
     * </ul>
     *
     * @return bool - true if this is a sub process scope
     */
    public function isSubProcessScope(): bool;

    /**
     * The event scope for an activity is the scope in which the activity listens for events.
     * This may or may not be the {@link #getFlowScope() flow scope.}.
     * Consider: boundary events have a different event scope than flow scope.
     *<p>
    * The event scope is always a {@link #isScope() scope}.
    *
    * @return PvmScopeInterface the event scope of the activity
    */
    public function getEventScope(): ?PvmScopeInterface;

    /**
     * The flow scope of the activity. The scope in which the activity itself is executed.
     *<p>
    * Note: in order to ensure backwards compatible behavior,  a flow scope is not necessarily
    * a {@link #isScope() a scope}. Example: event sub processes.
    */
    public function getFlowScope(): ?PvmScopeInterface;

    /**
     * The "level of subprocess scope" as defined in bpmn: this is the subprocess
     * containing the activity. Usually this is the same as the flow scope, instead if
     * the activity is multi instance: in that case the activity is nested inside a
     * mutli instance body but "at the same level of subprocess" as other activities which
     * are siblings to the mi-body.
     *
     * @return PvmScopeInterface the level of subprocess scope as defined in bpmn
     */
    public function getLevelOfSubprocessScope(): ?PvmScopeInterface;

    /**
     * Returns the flow activities of this scope. This is the list of activities for which this scope is
     * the {@link PvmActivity#getFlowScope() flow scope}.
     *
     * @return array the list of flow activities for this scope.
     */
    public function getActivities(): array;

    /**
     * Recursively finds a flow activity. This is an activitiy which is in the hierarchy of flow activities.
     *
     * @param activityId the id of the activity to find.
     * @return PvmActivityInterface the activity or null
     */
    public function findActivity(string $activityId): PvmActivityInterface;

    /**
     * Finds an activity at the same level of subprocess.
     *
     * @param activityId the id of the activity to find.
     * @return PvmActivityInterface the activity or null
     */
    public function findActivityAtLevelOfSubprocess(string $activityId): PvmActivityInterface;

    /**
     * Recursively finds a transition.
     * @param transitionId the transiton to find
     * @return TransitionImpl the transition or null
     */
    public function findTransition(string $transitionId): TransitionImpl;
}
