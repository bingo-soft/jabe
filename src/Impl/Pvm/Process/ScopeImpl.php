<?php

namespace Jabe\Impl\Pvm\Process;

use Jabe\ProcessEngineException;
use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreActivity;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmException,
    PvmScopeInterface,
    PvmTransitionInterface
};

abstract class ScopeImpl extends CoreActivity implements PvmScopeInterface
{
    protected bool $isSubProcessScope = false;

    /** The activities for which the flow scope is this scope  */
    protected $flowActivities = [];
    protected $namedFlowActivities = [];

    /** activities for which this is the event scope **/
    protected $eventActivities = [];

    protected $processDefinition;

    public function __construct(?string $id, ?ProcessDefinitionImpl $processDefinition)
    {
        parent::__construct($id);
        $this->processDefinition = $processDefinition;
    }

    public function findActivity(?string $activityId): ?ActivityImpl
    {
        return parent::findActivity($activityId);
    }

    public function findTransition(?string $transitionId): ?TransitionImpl
    {
        foreach ($this->flowActivities as $childActivity) {
            foreach ($childActivity->getOutgoingTransitions() as $transition) {
                if ($transitionId == $transition->getId()) {
                    return $transition;
                }
            }
        }

        foreach ($this->flowActivities as $childActivity) {
            $nestedTransition = $childActivity->findTransition($transitionId);
            if ($nestedTransition !== null) {
                return $nestedTransition;
            }
        }

        return null;
    }

    public function findActivityAtLevelOfSubprocess(?string $activityId): ?ActivityImpl
    {
        if (!$this->isSubProcessScope()) {
            throw new ProcessEngineException("This is not a sub process scope.");
        }
        $activity = $this->findActivity($activityId);
        if ($activity === null || $activity->getLevelOfSubprocessScope() != $this) {
            return null;
        }
        return $activity;
    }

    /** searches for the activity locally */
    public function getChildActivity(?string $activityId): ?ActivityImpl
    {
        if (array_key_exists($activityId, $this->namedFlowActivities)) {
            return $this->namedFlowActivities[$activityId];
        }
        return null;
    }

    /**
     * The key identifies the activity which is referenced but not read yet.
     * The value is the error callback, which is called if the activity is not
     * read till the end of parsing.
     */
    protected $BACKLOG = [];

    /**
     * Returns the backlog error callback's.
     *
     * @return array the callback's
     */
    public function getBacklogErrorCallbacks(): array
    {
        return array_values($this->BACKLOG);
    }

    /**
     * Returns true if the backlog is empty.
     *
     * @return bool - true if empty, false otherwise
     */
    public function isBacklogEmpty(): bool
    {
        return empty($this->BACKLOG);
    }

    /**
     * Add's the given activity reference and the error callback to the backlog.
     *
     * @param activityRef the activity reference which is not read until now
     * @param callback the error callback which should called if activity will not be read
     */
    public function addToBacklog(?string $activityRef, BacklogErrorCallbackInterface $callback): void
    {
        $this->BACKLOG[$activityRef] = $callback;
    }

    public function createActivity(?string $activityId = null): ActivityImpl
    {
        $activity = new ActivityImpl($activityId, $this->processDefinition);
        if ($activityId !== null) {
            if ($this->processDefinition->findActivity($activityId) !== null) {
                throw new PvmException("duplicate activity id '" . $activityId . "'");
            }
            if (array_key_exists($activityId, $this->BACKLOG)) {
                unset($this->BACKLOG[$activityId]);
            }
            $this->namedFlowActivities[$activityId] = $activity;
        }
        $activity->flowScope = $this;
        $this->flowActivities[] = $activity;

        return $activity;
    }

    public function isAncestorFlowScopeOf(ScopeImpl $other): bool
    {
        $otherAncestor = $other->getFlowScope();
        while ($otherAncestor !== null) {
            if ($this == $otherAncestor) {
                return true;
            } else {
                $otherAncestor = $otherAncestor->getFlowScope();
            }
        }

        return false;
    }

    public function contains(ActivityImpl $activity): bool
    {
        if (array_key_exists($activity->getId(), $this->namedFlowActivities)) {
            return true;
        }
        foreach ($this->flowActivities as $nestedActivity) {
            if ($this->nestedActivity->contains($activity)) {
                return true;
            }
        }
        return false;
    }

    // event listeners //////////////////////////////////////////////////////////

    public function getExecutionListeners(?string $eventName = null): array
    {
        return parent::getListeners($eventName);
    }

    public function addExecutionListener(?string $eventName, ExecutionListenerInterface $executionListener, ?int $index = -1): void
    {
        parent::addListener($eventName, $executionListener, $index);
    }

    // getters and setters //////////////////////////////////////////////////////
    public function getActivities(): array
    {
        return $this->flowActivities;
    }

    public function getEventActivities(): array
    {
        return $this->eventActivities;
    }

    public function isSubProcessScope(): bool
    {
        return $this->isSubProcessScope;
    }

    public function setSubProcessScope(bool $isSubProcessScope): void
    {
        $this->isSubProcessScope = $isSubProcessScope;
    }

    public function getProcessDefinition(): ProcessDefinitionImpl
    {
        return $this->processDefinition;
    }
}
