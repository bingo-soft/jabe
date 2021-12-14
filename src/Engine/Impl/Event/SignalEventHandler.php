<?php

namespace BpmPlatform\Engine\Impl\Event;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cmd\CommandLogger;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ProcessDefinitionEntity
};
use BpmPlatform\Engine\Impl\Pvm\PvmProcessInstanceInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SignalEventHandler extends EventHandlerImpl
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function __construct()
    {
        parent::__construct(EventType::signal());
    }

    protected function handleStartEvent(
        EventSubscriptionEntity $eventSubscription,
        array $payload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void {
        $processDefinitionId = $eventSubscription->getConfiguration();
        EnsureUtil::ensureNotNull(
            "Configuration of signal start event subscription '" . $eventSubscription->getId() . "' contains no process definition id.",
            $processDefinitionId
        );

        $deploymentCache = Context::getProcessEngineConfiguration()->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);
        if ($processDefinition == null || $processDefinition->isSuspended()) {
            // ignore event subscription
            //LOG.debugIgnoringEventSubscription(eventSubscription, processDefinitionId);
        } else {
            $signalStartEvent = $processDefinition->findActivity($eventSubscription->getActivityId());
            $processInstance = $processDefinition->createProcessInstance($businessKey, $signalStartEvent);
            $processInstance->start($payload);
        }
    }

    public function handleEvent(
        EventSubscriptionEntity $eventSubscription,
        $payload,
        $localPayload,
        ?string $businessKey,
        CommandContext $commandContext
    ): void {
        if ($eventSubscription->getExecutionId() != null) {
            $this->handleIntermediateEvent($eventSubscription, $payload, $localPayload, $commandContext);
        } else {
            $this->handleStartEvent($eventSubscription, $payload, $businessKey, $commandContext);
        }
    }
}
