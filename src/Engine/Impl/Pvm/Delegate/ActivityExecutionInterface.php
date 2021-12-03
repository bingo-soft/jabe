<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

use BpmPlatform\Engine\Delegate\DelegateExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmProcessDefinitionInterface,
    PvmProcessInstanceInterface,
    PvmScopeInterface,
    PvmTransitionInterface
};
use BpmPlatform\Engine\Impl\Pvm\Process\{
    ScopeImpl,
    TransitionImpl,
    PvmExecutionImpl
};

interface ActivityExecutionInterface extends DelegateExecutionInterface
{
    /* Process instance/activity/transition retrieval */

    /**
     * returns the current {@link PvmActivity} of the execution.
     */
    public function getActivity(): PvmActivityInterface;

    /** invoked to notify the execution that a new activity instance is started */
    public function enterActivityInstance(): void;

    /** invoked to notify the execution that an activity instance is ended. */
    public function leaveActivityInstance(): void;

    public function setActivityInstanceId(?string $id): void;

    /** return the Id of the activity instance currently executed by this execution */
    public function getActivityInstanceId(): string;

    /** return the Id of the parent activity instance currently executed by this execution */
    public function getParentActivityInstanceId(): string;

    /* Execution management */

/**
     * creates a new execution. This execution will be the parent of the newly created execution.
     * properties processDefinition, processInstance and activity will be initialized.
     */
    public function createExecution(?bool $initializeExecutionStartContext = null): ActivityExecutionInterface;

    /**
     * @see #createSubProcessInstance(PvmProcessDefinition)
     *
     * @param processDefinition The {@link PvmProcessDefinition} of the subprocess.
     * @param businessKey the business key of the process instance
     * @param caseInstanceId the case instance id of the process instance
     */
    public function createSubProcessInstance(PvmProcessDefinitionInterface $processDefinition, ?string $businessKey = null, ?string $caseInstanceId = null): PvmProcessInstanceInterface;

    /**
     * <p>Creates a new sub case instance.</p>
     *
     * <p><code>This</code> execution will be the super execution of the
     * created sub case instance.</p>
     *
     * @param caseDefinition The {@link CmmnCaseDefinition} of the sub case instance.
     */
    //CmmnCaseInstance createSubCaseInstance(CmmnCaseDefinition caseDefinition);

    /**
     * <p>Creates a new sub case instance.</p>
     *
     * <p><code>This</code> execution will be the super execution of the
     * created sub case instance.</p>
     *
     * @param caseDefinition The {@link CmmnCaseDefinition} of the sub case instance.
     * @param businessKey The businessKey to be set on sub case instance.
     */
    //CmmnCaseInstance createSubCaseInstance(CmmnCaseDefinition caseDefinition, String businessKey);

    /**
     * returns the parent of this execution, or null if there no parent.
     */
    public function getParent(): ?ActivityExecutionInterface;

    /**
     * returns the list of execution of which this execution the parent of.
     * This is a copy of the actual list, so a modification has no direct effect.
     */
    public function getExecutions(): array;

    /**
     * returns child executions that are not event scope executions.
     */
    public function getNonEventScopeExecutions(): array;

    /**
     * @return true if this execution has child executions (event scope executions or not)
     */
    public function hasChildren(): bool;

    /**
     * ends this execution.
     */
    public function end(bool $isScopeComplete): void;

    /**
     * Execution finished compensation. Removes this
     * execution and notifies listeners.
     */
    public function endCompensation(): void;

    /* State management */

    /**
     * makes this execution active or inactive.
     */
    public function setActive(bool $isActive): void;

    /**
     * returns whether this execution is currently active.
     */
    public function isActive(): bool;

    /**
     * returns whether this execution has ended or not.
     */
    public function isEnded(): bool;

    /**
     * changes the concurrent indicator on this execution.
     */
    public function setConcurrent(bool $isConcurrent): bool;

    /**
     * returns whether this execution is concurrent or not.
     */
    public function isConcurrent(): bool;

    /**
     * returns whether this execution is a process instance or not.
     */
    public function isProcessInstanceExecution(): bool;

    /**
     * Inactivates this execution.
     * This is useful for example in a join: the execution
     * still exists, but it is not longer active.
     */
    public function inactivate(): void;

    /**
     * Returns whether this execution is a scope.
     */
    public function isScope(): bool;

    /**
     * Changes whether this execution is a scope or not
     */
    public function setScope(bool $isScope): void;

    /**
     * Returns whether this execution completed the parent scope.
     */
    public function isCompleteScope(): bool;

    /**
     * Retrieves all executions which are concurrent and inactive at the given activity.
     */
    public function findInactiveConcurrentExecutions(PvmActivityInterface $activity): array;

    public function findInactiveChildExecutions(PvmActivityInterface $activity): array;

    /**
     * Takes the given outgoing transitions, and potentially reusing
     * the given list of executions that were previously joined.
     */
    public function leaveActivityViaTransitions(array $outgoingTransitions, array $joinedExecutions): void;

    public function leaveActivityViaTransition($outgoingTransition, ?array $_recyclableExecutions = []): void;

    /**
     * Executes the {@link ActivityBehavior} associated with the given activity.
     */
    public function executeActivity(PvmActivityInterface $activity): void;

    /**
     * Called when an execution is interrupted. This will remove all associated entities
     * such as event subscriptions, jobs, ...
     */
    public function interrupt(string $reason, ?bool $skipCustomListeners = false, ?bool $skipIoMappings = false, ?bool $externallyTerminated = false): void;

    /** An activity which is to be started next. */
    public function getNextActivity(): ?PvmActivityInterface;


    public function remove(): void;
    public function destroy(): void;

    public function signal(string $string, $signalData): void;

    public function setActivity(PvmActivityInterface $activity): void;

    public function tryPruneLastConcurrentChild(): bool;

    public function forceUpdate(): void;

    public function getTransition(): TransitionImpl;

    /**
     * Assumption: the current execution is active and executing an activity ({@link #getActivity()} is not null).
     *
     * For a given target scope, this method returns the scope execution.
     *
     * @param targetScope scope activity or process definition for which the scope execution should be found;
     *   must be an ancestor of the execution's current activity
     * @return
     */
    public function findExecutionForFlowScope(PvmScopeInterface $targetScope): ActivityExecution;

    /**
     * Returns a mapping from scope activities to scope executions for all scopes that
     * are ancestors of the activity currently executed by this execution.
     *
     * Assumption: the current execution is active and executing an activity ({@link #getActivity()} is not null).
     */
    public function createActivityExecutionMapping(): array;

    public function setEnded(bool $b): void;
}
