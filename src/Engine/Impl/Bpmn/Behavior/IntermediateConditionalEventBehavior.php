<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Bpmn\Parser\ConditionalEventDefinition;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class IntermediateConditionalEventBehavior extends IntermediateCatchEventActivityBehavior implements ConditionalEventBehaviorInterface
{
    protected $conditionalEvent;

    public function __construct(ConditionalEventDefinition $conditionalEvent, bool $isAfterEventBasedGateway)
    {
        parent::__construct($isAfterEventBasedGateway);
        $this->conditionalEvent = $conditionalEvent;
    }

    public function getConditionalEventDefinition(): ConditionalEventDefinition
    {
        return $this->conditionalEvent;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        if ($this->isAfterEventBasedGateway || $this->conditionalEvent->tryEvaluate($execution)) {
            $this->leave($execution);
        }
    }

    public function leaveOnSatisfiedCondition(EventSubscriptionEntity $eventSubscription, VariableEvent $variableEvent): void
    {
        $execution = $eventSubscription->getExecution();

        if (
            $execution !== null &&
            !$execution->isEnded() &&
            $variableEvent !== null &&
            $this->conditionalEvent->tryEvaluate($variableEvent, $execution) &&
            $execution->isActive() &&
            $execution->isScope()
        ) {
            if ($this->isAfterEventBasedGateway) {
                $activity = $eventSubscription->getActivity();
                $execution->executeEventHandlerActivity($activity);
            } else {
                $this->leave($execution);
            }
        }
    }
}
