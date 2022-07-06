<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Delegate\DelegateExecutionInterface;
use Jabe\Engine\Impl\ConditionInterface;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;

class ConditionalEventDefinition extends EventSubscriptionDeclaration
{
    protected $conditionAsString;
    protected $condition;
    protected $interrupting;
    protected $variableName;
    protected $variableEvents = [];
    protected $conditionalActivity;

    public function __construct(ConditionInterface $condition, ActivityImpl $conditionalActivity)
    {
        parent::__construct(null, EventType::conditional());
        $this->activityId = $conditionalActivity->getActivityId();
        $this->conditionalActivity = $conditionalActivity;
        $this->condition = $condition;
    }

    public function getConditionalActivity(): ActivityImpl
    {
        return $this->conditionalActivity;
    }

    public function setConditionalActivity(ActivityImpl $conditionalActivity): void
    {
        $this->conditionalActivity = $conditionalActivity;
    }

    public function isInterrupting(): bool
    {
        return $this->interrupting;
    }

    public function setInterrupting(bool $interrupting): void
    {
        $this->interrupting = $interrupting;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function setVariableName(string $variableName): void
    {
        $this->variableName = $variableName;
    }

    public function getVariableEvents(): array
    {
        return $this->variableEvents;
    }

    public function setVariableEvents(array $variableEvents): void
    {
        $this->variableEvents = $variableEvents;
    }

    public function getConditionAsString(): string
    {
        return $this->conditionAsString;
    }

    public function setConditionAsString(string $conditionAsString): void
    {
        $this->conditionAsString = $conditionAsString;
    }

    public function shouldEvaluateForVariableEvent(VariableEvent $event): bool
    {
        return
        ($this->variableName === null || $event->getVariableInstance()->getName() == $this->variableName)
                                                &&
        (empty($this->variableEvents) || in_array($event->getEventName(), $variableEvents));
    }

    public function evaluate(DelegateExecutionInterface $execution): bool
    {
        if ($this->condition !== null) {
            return $this->condition->evaluate($execution, $execution);
        }
        throw new \Exception("Conditional event must have a condition!");
    }

    public function tryEvaluate(?VariableEvent $variableEvent, DelegateExecutionInterface $execution): bool
    {
        if ($variableEvent !== null) {
            $should = $this->shouldEvaluateForVariableEvent($variableEvent);
            if ($this->condition !== null) {
                return $should && $this->condition->tryEvaluate($execution, $execution);
            }
        } elseif ($this->condition !== null) {
            return $this->condition->tryEvaluate($execution, $execution);
        }
        throw new \Exception("Conditional event must have a condition!");
    }
}
