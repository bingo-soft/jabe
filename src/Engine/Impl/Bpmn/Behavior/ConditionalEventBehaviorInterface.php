<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Bpmn\Parser\ConditionalEventDefinition;
use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Persistence\Entity\EventSubscriptionEntity;

interface ConditionalEventBehaviorInterface
{
    /**
     * Returns the current conditional event definition.
     *
     * @return the conditional event definition
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
