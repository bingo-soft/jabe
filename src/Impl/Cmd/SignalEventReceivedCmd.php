<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\{
    ProcessEngineLogger,
    SignalEventReceivedBuilderImpl
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\EventSubscriptionEntity;
use Jabe\Impl\Util\EnsureUtil;

class SignalEventReceivedCmd implements CommandInterface
{
    //protected final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $builder;

    public function __construct(SignalEventReceivedBuilderImpl $builder)
    {
        $this->builder = $builder;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $signalName = $this->builder->getSignalName();
        $executionId = $this->builder->getExecutionId();

        if ($executionId === null) {
            $this->sendSignal($commandContext, $signalName);
        } else {
            $this->sendSignalToExecution($commandContext, $signalName, $executionId);
        }
        return null;
    }

    protected function sendSignal(CommandContext $commandContext, ?string $signalName): void
    {
        $signalEventSubscriptions = $this->findSignalEventSubscriptions($commandContext, $signalName);

        $catchSignalEventSubscription = $this->filterIntermediateSubscriptions($signalEventSubscriptions);
        $startSignalEventSubscriptions = $this->filterStartSubscriptions($signalEventSubscriptions);
        $processDefinitions = $this->getProcessDefinitionsOfSubscriptions($startSignalEventSubscriptions);

        $this->checkAuthorizationOfCatchSignals($commandContext, $catchSignalEventSubscription);
        $this->checkAuthorizationOfStartSignals($commandContext, $startSignalEventSubscriptions, $processDefinitions);

        $this->notifyExecutions($catchSignalEventSubscription);
        $this->startProcessInstances($startSignalEventSubscriptions, $processDefinitions);
    }

    protected function findSignalEventSubscriptions(CommandContext $commandContext, ?string $signalName): array
    {
        $eventSubscriptionManager = $commandContext->getEventSubscriptionManager();

        if ($this->builder->isTenantIdSet()) {
            return $eventSubscriptionManager->findSignalEventSubscriptionsByEventNameAndTenantId($signalName, $this->builder->getTenantId());
        } else {
            return $eventSubscriptionManager->findSignalEventSubscriptionsByEventName($signalName);
        }
    }

    protected function getProcessDefinitionsOfSubscriptions(array $startSignalEventSubscriptions): array
    {
        $deploymentCache = Context::getProcessEngineConfiguration()->getDeploymentCache();

        $processDefinitions = [];

        foreach ($startSignalEventSubscriptions as $eventSubscription) {
            $processDefinitionId = $eventSubscription->getConfiguration();
            EnsureUtil::ensureNotNull(
                "Configuration of signal start event subscription '" . $eventSubscription->getId() . "' contains no process definition id.",
                'processDefinitionId',
                $processDefinitionId
            );

            $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);
            if ($processDefinition !== null && !$processDefinition->isSuspended()) {
                $processDefinitions[$eventSubscription->getId()] = $processDefinition;
            }
        }

        return $processDefinitions;
    }

    protected function sendSignalToExecution(CommandContext $commandContext, ?string $signalName, ?string $executionId): void
    {
        $executionManager = $commandContext->getExecutionManager();
        $execution = $executionManager->findExecutionById($executionId);
        EnsureUtil::ensureNotNull("Cannot find execution with id '" . $executionId . "'", "execution", $execution);

        $eventSubscriptionManager = $commandContext->getEventSubscriptionManager();
        $signalEvents = $eventSubscriptionManager->findSignalEventSubscriptionsByNameAndExecution($signalName, $executionId);
        EnsureUtil::ensureNotEmpty("Execution '" . $executionId . "' has not subscribed to a signal event with name '" . $signalName . "'.", $signalEvents);

        $this->checkAuthorizationOfCatchSignals($commandContext, $signalEvents);
        $this->notifyExecutions($signalEvents);
    }

    protected function checkAuthorizationOfCatchSignals(CommandContext $commandContext, array $catchSignalEventSubscription): void
    {
        // check authorization for each fetched signal event
        foreach ($catchSignalEventSubscription as $event) {
            $processInstanceId = $event->getProcessInstanceId();
            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkUpdateProcessInstanceById($processInstanceId);
            }
        }
    }

    private function checkAuthorizationOfStartSignals(
        CommandContext $commandContext,
        array $startSignalEventSubscriptions,
        array $processDefinitions
    ): void {
        // check authorization for process definition
        foreach ($startSignalEventSubscriptions as $signalStartEventSubscription) {
            $key = $signalStartEventSubscription->getId();
            $processDefinition = null;
            if (array_key_exists($key, $processDefinitions)) {
                $processDefinition = $processDefinitions[$key];
                foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                    $checker->checkCreateProcessInstance($processDefinition);
                }
            }
        }
    }

    private function notifyExecutions(array $catchSignalEventSubscription): void
    {
        foreach ($catchSignalEventSubscription as $signalEventSubscriptionEntity) {
            if ($this->isActiveEventSubscription($signalEventSubscriptionEntity)) {
                $signalEventSubscriptionEntity->eventReceived($this->builder->getVariables(), false);
            }
        }
    }

    private function isActiveEventSubscription(EventSubscriptionEntity $signalEventSubscriptionEntity): bool
    {
        $execution = $signalEventSubscriptionEntity->getExecution();
        return !$execution->isEnded() && !$execution->isCanceled();
    }

    private function startProcessInstances(array $startSignalEventSubscriptions, array $processDefinitions): void
    {
        foreach ($startSignalEventSubscriptions as $signalStartEventSubscription) {
            $key = $signalStartEventSubscription->getId();
            $processDefinition = null;
            if (array_key_exists($key, $processDefinitions)) {
                $processDefinition = $processDefinitions[$key];
                $signalStartEvent = $processDefinition->findActivity($signalStartEventSubscription->getActivityId());
                $processInstance = $processDefinition->createProcessInstanceForInitial($signalStartEvent);
                $processInstance->start($this->builder->getVariables());
            }
        }
    }

    protected function filterIntermediateSubscriptions(array $subscriptions): array
    {
        $result = [];

        foreach ($subscriptions as $subscription) {
            if ($subscription->getExecutionId() !== null) {
                $result[] = $subscription;
            }
        }

        return $result;
    }

    protected function filterStartSubscriptions(array $subscriptions): array
    {
        $result = [];

        foreach ($subscriptions as $subscription) {
            if ($subscription->getExecutionId() === null) {
                $result[] = $subscription;
            }
        }

        return $result;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
