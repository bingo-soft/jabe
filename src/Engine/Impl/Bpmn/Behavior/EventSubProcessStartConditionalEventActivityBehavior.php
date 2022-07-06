<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Bpmn\Parser\ConditionalEventDefinition;
use Jabe\Engine\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class EventSubProcessStartConditionalEventActivityBehavior extends EventSubProcessStartEventActivityBehavior implements ConditionalEventBehaviorInterface
{
    protected $conditionalEvent;

    public function __construct(ConditionalEventDefinition $conditionalEvent)
    {
        $this->conditionalEvent = $conditionalEvent;
    }

    public function getConditionalEventDefinition(): ConditionalEventDefinition
    {
        return $this->conditionalEvent;
    }

    public function leaveOnSatisfiedCondition(EventSubscriptionEntity $eventSubscription, VariableEvent $variableEvent): void
    {
        $execution = $eventSubscription->getExecution();

        if (
            $execution !== null &&
            !$execution->isEnded() &&
            $execution->isScope() &&
            $conditionalEvent->tryEvaluate($variableEvent, $execution)
        ) {
            $activity = $eventSubscription->getActivity();
            $activity = $activity->getFlowScope();
            $execution->executeEventHandlerActivity($activity);
        }
    }
}
