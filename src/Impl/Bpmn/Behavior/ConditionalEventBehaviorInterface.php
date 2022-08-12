<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Parser\ConditionalEventDefinition;
use Jabe\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Impl\Persistence\Entity\EventSubscriptionEntity;

interface ConditionalEventBehaviorInterface
{
    /**
     * Returns the current conditional event definition.
     *
     * @return ConditionalEventDefinition the conditional event definition
     */
    public function getConditionalEventDefinition(): ConditionalEventDefinition;

    /**
     * Checks the condition, on satisfaction the activity is leaved.
     *
     * @param eventSubscription the event subscription which contains all necessary informations
     * @param variableEvent the variableEvent to evaluate the condition
     */
    public function leaveOnSatisfiedCondition(EventSubscriptionEntity $eventSubscription, VariableEvent $variableEvent): void;
}
