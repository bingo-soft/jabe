<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Parser\EventSubscriptionDeclaration;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    EventSubscriptionManager,
    ExecutionEntity
};
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Variable\VariableMapInterface;

class ThrowSignalEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    //protected final static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $signalDefinition;

    public function __construct(EventSubscriptionDeclaration $signalDefinition)
    {
        $this->signalDefinition = $signalDefinition;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $businessKey = $this->signalDefinition->getEventPayload()->getBusinessKey($execution);
        $variableMap = $this->signalDefinition->getEventPayload()->getInputVariables($execution);

        $eventName = $this->signalDefinition->resolveExpressionOfEventName($execution);
        // trigger all event subscriptions for the signal (start and intermediate)
        $signalEventSubscriptions = $this->findSignalEventSubscriptions($eventName, $execution->getTenantId());

        foreach ($signalEventSubscriptions as $signalEventSubscription) {
            if ($this->isActiveEventSubscription($signalEventSubscription)) {
                $signalEventSubscription->eventReceived($variableMap, null, $businessKey, $signalDefinition->isAsync());
            }
        }
        $this->leave($execution);
    }

    protected function findSignalEventSubscriptions(string $signalName, ?string $tenantId): array
    {
        $eventSubscriptionManager = Context::getCommandContext()->getEventSubscriptionManager();

        if ($tenantId != null) {
            return $eventSubscriptionManager
                ->findSignalEventSubscriptionsByEventNameAndTenantIdIncludeWithoutTenantId($signalName, $tenantId);
        } else {
            // find event subscriptions without tenant id
            return $eventSubscriptionManager->findSignalEventSubscriptionsByEventNameAndTenantId($signalName, null);
        }
    }

    protected function isActiveEventSubscription(EventSubscriptionEntity $signalEventSubscriptionEntity): bool
    {
        return $this->isStartEventSubscription($signalEventSubscriptionEntity)
            || $this->isActiveIntermediateEventSubscription($signalEventSubscriptionEntity);
    }

    protected function isStartEventSubscription(EventSubscriptionEntity $signalEventSubscriptionEntity): bool
    {
        return $signalEventSubscriptionEntity->getExecutionId() == null;
    }

    protected function isActiveIntermediateEventSubscription(EventSubscriptionEntity $signalEventSubscriptionEntity): bool
    {
        $execution = $signalEventSubscriptionEntity->getExecution();
        return $execution != null && !$execution->isEnded() && !$execution->isCanceled();
    }
}
