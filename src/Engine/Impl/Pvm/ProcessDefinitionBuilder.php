<?php

namespace BpmPlatform\Engine\Impl\Pvm;

use BpmPlatform\Engine\Delegate\ExecutionListenerInterface;
use BpmPlatform\Engine\Impl\Core\Model\CoreModelElement;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityBehaviorInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ActivityStartBehavior,
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};

class ProcessDefinitionBuilder
{
    protected $processDefinition;
    protected $scopeStack = [];
    protected $processElement;
    protected $transition;
    protected $unresolvedTransitions = [];

    public function __construct(?string $processDefinitionId = null)
    {
        $this->processDefinition = new ProcessDefinitionImpl($processDefinitionId);
        $this->processElement = $this->processDefinition;
        $this->scopeStack[] = $this->processDefinition;
    }

    public function createActivity(string $id): ProcessDefinitionBuilder
    {
        $activity = $this->scopeStack[0]->createActivity($id);
        array_unshift($this->scopeStack, $activity);
        $this->processElement = $activity;

        $this->transition = null;

        return $this;
    }

    public function attachedTo(string $id, bool $isInterrupting): ProcessDefinitionBuilder
    {
        $activity = $this->getActivity();
        $activity->setEventScope($this->processDefinition->findActivity($id));

        if ($isInterrupting) {
            $activity->setActivityStartBehavior(ActivityStartBehavior::INTERRUPT_EVENT_SCOPE);
        } else {
            $activity->setActivityStartBehavior(ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE);
        }

        return $this;
    }

    public function endActivity(): ProcessDefinitionBuilder
    {
        array_shift($this->scopeStack);
        $this->processElement = count($this->scopeStack) ? $this->scopeStack[0] : null;

        $this->transition = null;

        return $this;
    }

    public function initial(): ProcessDefinitionBuilder
    {
        $this->processDefinition->setInitial($this->getActivity());
        return $this;
    }

    public function startTransition(string $destinationActivityId, ?string $transitionId = null): ProcessDefinitionBuilder
    {
        if ($destinationActivityId == null) {
            throw new PvmException("destinationActivityId is null");
        }
        $activity = $this->getActivity();
        $this->transition = $activity->createOutgoingTransition($transitionId);

        $this->unresolvedTransitions[] = [$transition, $destinationActivityId];
        $this->processElement = $this->transition;
        return $this;
    }

    public function endTransition(): ProcessDefinitionBuilder
    {
        $this->processElement = array_shift($this->scopeStack);
        $this->transition = null;
        return $this;
    }

    public function transition(string $destinationActivityId, ?string $transitionId = null): ProcessDefinitionBuilder
    {
        $this->startTransition($destinationActivityId, $transitionId);
        $this->endTransition();
        return $this;
    }

    public function behavior(ActivityBehaviorInterface $activityBehaviour): ProcessDefinitionBuilder
    {
        $this->getActivity()->setActivityBehavior($activityBehaviour);
        return $this;
    }

    public function property(string $name, $value): ProcessDefinitionBuilder
    {
        $this->processElement->setProperty($name, $value);
        return $this;
    }

    public function buildProcessDefinition(): PvmProcessDefinition
    {
        foreach ($this->unresolvedTransitions as $unresolvedTransition) {
            $transition = $unresolvedTransition[0];
            $destinationActivityName = $unresolvedTransition[1];
            $destination = $processDefinition->findActivity($destinationActivityName);
            if ($destination == null) {
                throw new \Exception("destination '" . $destinationActivityName . "' not found.  (referenced from transition in '" . $transition->getSource()->getId() . "')");
            }
            $transition->setDestination($destination);
        }
        return $this->processDefinition;
    }

    protected function getActivity(): ActivityImpl
    {
        return $this->scopeStack[0];
    }

    public function scope(): ProcessDefinitionBuilder
    {
        $this->getActivity()->setScope(true);
        return $this;
    }

    public function executionListener(?string $eventName, ExecutionListenerInterface $executionListener): ProcessDefinitionBuilder
    {
        if ($eventName != null) {
            if ($this->transition == null) {
                $this->scopeStack[0]->addExecutionListener($eventName, $executionListener);
            } else {
                $this->transition->addExecutionListener($executionListener);
            }
        } else {
            if ($this->transition != null) {
                $this->transition->addExecutionListener($executionListener);
            } else {
                throw new PvmException("not in a transition scope");
            }
        }
        return $this;
    }
}
