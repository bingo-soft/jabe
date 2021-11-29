<?php

namespace BpmPlatform\Engine\Impl\Pvm;

use BpmPlatform\Engine\Delegate\ExecutionListenerInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Mapping\IoMapping;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;

interface PvmActivityInterface extends PvmScopeInterface
{
    /**
     * The inner behavior of an activity. The inner behavior is the logic which is executed after
     * the {@link ExecutionListener#EVENTNAME_START start} listeners have been executed.
     *
     * In case the activity {@link #isScope() is scope}, a new execution will be created
     *
     * @return the inner behavior of the activity
     */
    public function getActivityBehavior(): ?ActivityBehaviorInterface;

    /**
     * The start behavior of an activity. The start behavior is executed before the
     * {@link ExecutionListener#EVENTNAME_START start} listeners of the activity are executed.
     *
     * @return the start behavior of an activity.
     */
    public function getActivityStartBehavior(): string;

    /**
     * Finds and returns an outgoing sequence flow (transition) by it's id.
     * @param transitionId the id of the transition to find
     * @return the transition or null in case it cannot be found
     */
    public function findOutgoingTransition(string $transitionId): PvmTransitionInterface;

    /**
     * @return the list of outgoing sequence flows (transitions)
     */
    public function getOutgoingTransitions(): array;

    /**
     * @return the list of incoming sequence flows (transitions)
     */
    public function getIncomingTransitions(): array;

    /**
     * Indicates whether the activity is executed asynchronously.
     * This can be done <em>after</em> the {@link #getActivityStartBehavior() activity start behavior} and
     * <em>before</em> the {@link ExecutionListener#EVENTNAME_START start} listeners are invoked.
     *
     * @return true if the activity is executed asynchronously.
     */
    public function isAsyncBefore(): bool;

    /**
     * Indicates whether execution after this execution should continue asynchronously.
     * This can be done <em>after</em> the {@link ExecutionListener#EVENTNAME_END end} listeners are invoked.
     * @return true if execution after this activity continues asynchronously.
     */
    public function isAsyncAfter(): bool;
}
