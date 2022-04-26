<?php

namespace Jabe\Engine\Impl\Pvm\Process;

use Jabe\Engine\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmException,
    PvmScopeInterface,
    PvmTransitionInterface
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;

class ActivityImpl extends ScopeImpl implements PvmActivityInterface, HasDIBoundsInterface
{
    protected $outgoingTransitions = [];
    protected $namedOutgoingTransitions = [];
    protected $incomingTransitions = [];

    /** the inner behavior of an activity. For activities which are flow scopes,
     * this must be a CompositeActivityBehavior. */
    protected $activityBehavior;

    /** The start behavior for this activity. */
    protected $activityStartBehavior = ActivityStartBehavior::DEFAULT;

    protected $eventScope;
    protected $flowScope;

    protected $isScope = false;

    protected $isAsyncBefore;
    protected $isAsyncAfter;

    public function __construct(string $id, ProcessDefinitionImpl $processDefinition)
    {
        parent::__construct($id, $processDefinition);
    }

    public function createOutgoingTransition(?string $transitionId = null): TransitionImpl
    {
        $transition = new TransitionImpl($transitionId, $processDefinition);
        $transition->setSource($this);
        $this->outgoingTransitions[] = $transition;

        if ($transitionId != null) {
            if (array_key_exists($transitionId, $this->namedOutgoingTransitions)) {
                throw new PvmException("activity '" . $this->id . " has duplicate transition '" . $transitionId . "'");
            }
            $this->namedOutgoingTransitions[$transitionId] = $transition;
        }

        return $transition;
    }

    public function findOutgoingTransition(string $transitionId): ?TransitionImpl
    {
        if (array_key_exists($transitionId, $this->namedOutgoingTransitions)) {
            return $this->namedOutgoingTransitions[$transitionId];
        }
        return null;
    }

    public function __toString()
    {
        return "Activity(" . $this->id . ")";
    }

    // restricted setters ///////////////////////////////////////////////////////
    protected function setOutgoingTransitions(array $outgoingTransitions): void
    {
        $this->outgoingTransitions = $outgoingTransitions;
    }

    protected function setIncomingTransitions(array $incomingTransitions): void
    {
        $this->incomingTransitions = $incomingTransitions;
    }

    public function addIncomingTransition(TransitionImpl $transition): void
    {
        $this->incomingTransitions[] = $transition;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getOutgoingTransitions(): array
    {
        return $this->outgoingTransitions;
    }

    public function getActivityBehavior(): ?ActivityBehaviorInterface
    {
        return $this->activityBehavior;
    }

    public function setActivityBehavior(ActivityBehaviorInterface $activityBehavior): void
    {
        $this->activityBehavior = $activityBehavior;
    }

    public function getActivityStartBehavior(): string
    {
        return $this->activityStartBehavior;
    }

    public function setActivityStartBehavior(atring $activityStartBehavior): void
    {
        $this->activityStartBehavior = $activityStartBehavior;
    }

    public function getIncomingTransitions(): array
    {
        return $this->incomingTransitions;
    }

    public function isScope(): bool
    {
        return $this->isScope;
    }

    public function setScope(bool $isScope): void
    {
        $this->isScope = $isScope;
    }

    public function isAsyncBefore(): bool
    {
        return $this->isAsyncBefore;
    }

    public function setAsyncBefore(bool $isAsyncBefore, ?bool $exclusive = true): void
    {
        if ($this->delegateAsyncBeforeUpdate != null) {
            $this->delegateAsyncBeforeUpdate->updateAsyncBefore($isAsyncBefore, $exclusive);
        }
        $this->isAsyncBefore = $isAsyncBefore;
    }

    public function isAsyncAfter(): bool
    {
        return $this->isAsyncAfter;
    }

    public function setAsyncAfter(bool $isAsyncAfter, ?bool $exclusive = true): void
    {
        if ($this->delegateAsyncAfterUpdate != null) {
            $this->delegateAsyncAfterUpdate->updateAsyncAfter($isAsyncAfter, $exclusive);
        }
        $this->isAsyncAfter = $isAsyncAfter;
    }

    public function getActivityId(): string
    {
        return parent::getId();
    }

    public function getFlowScope(): ?ScopeImpl
    {
        return $this->flowScope;
    }

    public function getEventScope(): ?ScopeImpl
    {
        return $this->eventScope;
    }

    public function setEventScope(ScopeImpl $eventScope): void
    {
        if ($this->eventScope != null) {
            foreach ($this->eventScope->eventActivities as $key => $activity) {
                if ($activity == $this) {
                    unset($this->eventScope->eventActivities[$key]);
                }
            }
        }

        $this->eventScope = $eventScope;

        if ($eventScope != null) {
            $this->eventScope->eventActivities[] = $this;
        }
    }

    public function getLevelOfSubprocessScope(): ?PvmScopeInterface
    {
        $levelOfSubprocessScope = $this->getFlowScope();
        while (!$levelOfSubprocessScope->isSubProcessScope()) {
            // cast always possible since process definition is always a sub process scope
            $levelOfSubprocessScope = $levelOfSubprocessScope->getFlowScope();
        }
        return $levelOfSubprocessScope;
    }

    // Graphical information ///////////////////////////////////////////

    protected $x = -1;
    protected $y = -1;
    protected $width = -1;
    protected $height = -1;

    public function getX(): int
    {
        return $this->x;
    }

    public function setX(int $x): void
    {
        $this->x = $x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setY(int $y): void
    {
        $this->y = $y;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function getParentFlowScopeActivity(): ?ActivityImpl
    {
        $flowScope = $this->getFlowScope();
        if ($flowScope != $this->getProcessDefinition()) {
            return $flowScope;
        }
        return null;
    }

    /**
     * Indicates whether activity is for compensation.
     *
     * @return bool - true if this activity is for compensation.
     */
    public function isCompensationHandler(): bool
    {
        $isForCompensation = $this->getProperty(BpmnParse::PROPERTYNAME_IS_FOR_COMPENSATION);
        return $isForCompensation == true;
    }

    /**
     * Find the compensation handler of this activity.
     *
     * @return the compensation handler or <code>null</code>, if this activity has no compensation handler.
     */
    public function findCompensationHandler(): ?ActivityImpl
    {
        $compensationHandlerId = $this->getProperty(BpmnParse::PROPERTYNAME_COMPENSATION_HANDLER_ID);
        if ($compensationHandlerId != null) {
            return $this->getProcessDefinition()->findActivity($compensationHandlerId);
        }
        return null;
    }

    /**
     * Indicates whether activity is a multi instance activity.
     *
     * @return bool - true if this activity is a multi instance activity.
     */
    public function isMultiInstance(): bool
    {
        $isMultiInstance = $this->getProperty(BpmnParse::PROPERTYNAME_IS_MULTI_INSTANCE);
        return $isMultiInstance == true;
    }

    public function isTriggeredByEvent(): bool
    {
        $isTriggeredByEvent = $this->getProperties()->get(BpmnProperties::triggeredByEvent());
        return $isTriggeredByEvent == true;
    }

    //============================================================================
    //===============================DELEGATES====================================
    //============================================================================
    /**
     * The delegate for the async before attribute update.
     */
    protected $delegateAsyncBeforeUpdate;
    /**
     * The delegate for the async after attribute update.
     */
    protected $delegateAsyncAfterUpdate;

    public function getDelegateAsyncBeforeUpdate(): AsyncBeforeUpdateInterface
    {
        return $this->delegateAsyncBeforeUpdate;
    }

    public function setDelegateAsyncBeforeUpdate(AsyncBeforeUpdateInterface $delegateAsyncBeforeUpdate): void
    {
        $this->delegateAsyncBeforeUpdate = $delegateAsyncBeforeUpdate;
    }

    public function getDelegateAsyncAfterUpdate(): AsyncAfterUpdateInterface
    {
        return $this->delegateAsyncAfterUpdate;
    }

    public function setDelegateAsyncAfterUpdate(AsyncAfterUpdateInterface $delegateAsyncAfterUpdate): void
    {
        $this->delegateAsyncAfterUpdate = $delegateAsyncAfterUpdate;
    }
}
