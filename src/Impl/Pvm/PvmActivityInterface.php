<?php

namespace Jabe\Impl\Pvm;

use Jabe\Impl\Pvm\Delegate\ActivityBehaviorInterface;

interface PvmActivityInterface extends PvmScopeInterface
{
    /**
     * The inner behavior of an activity. The inner behavior is the logic which is executed after
     * the {@link ExecutionListener#EVENTNAME_START start} listeners have been executed.
     *
     * In case the activity {@link #isScope() is scope}, a new execution will be created
     *
     * @return ActivityBehaviorInterface the inner behavior of the activity
     */
    public function getActivityBehavior(): ?ActivityBehaviorInterface;

    /**
     * The start behavior of an activity. The start behavior is executed before the
     * {@link ExecutionListener#EVENTNAME_START start} listeners of the activity are executed.
     *
     * @return string the start behavior of an activity.
     */
    public function getActivityStartBehavior(): ?string;

    /**
     * Finds and returns an outgoing sequence flow (transition) by it's id.
     * @param transitionId the id of the transition to find
     * @return PvmTransitionInterface the transition or null in case it cannot be found
     */
    public function findOutgoingTransition(?string $transitionId): ?PvmTransitionInterface;

    /**
     * @return array the list of outgoing sequence flows (transitions)
     */
    public function getOutgoingTransitions(): array;

    /**
     * @return array the list of incoming sequence flows (transitions)
     */
    public function getIncomingTransitions(): array;

    /**
     * Indicates whether the activity is executed asynchronously.
     * This can be done <em>after</em> the {@link #getActivityStartBehavior() activity start behavior} and
     * <em>before</em> the {@link ExecutionListener#EVENTNAME_START start} listeners are invoked.
     *
     * @return bool - true if the activity is executed asynchronously.
     */
    public function isAsyncBefore(): bool;

    /**
     * Indicates whether execution after this execution should continue asynchronously.
     * This can be done <em>after</em> the {@link ExecutionListener#EVENTNAME_END end} listeners are invoked.
     * @return bool - true if execution after this activity continues asynchronously.
     */
    public function isAsyncAfter(): bool;
}
