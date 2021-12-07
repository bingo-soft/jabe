<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Bpmn\Parser\ConditionalEventDefinition;
use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

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
            $execution != null &&
            !$execution->isEnded() &&
            $variableEvent != null &&
            $conditionalEvent->tryEvaluate($variableEvent, $execution) &&
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
