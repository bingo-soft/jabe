<?php

namespace Jabe\Impl\Runtime;

use Jabe\Impl\{
    ExecutionQueryImpl,
    ProcessEngineLogger
};
use Jabe\Impl\Bpmn\Parser\EventSubscriptionDeclaration;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    EventSubscriptionManager,
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Runtime\ExecutionInterface;

class DefaultCorrelationHandler implements CorrelationHandlerInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function correlateMessage(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): ?CorrelationHandlerResult
    {
        // first try to correlate to execution
        $correlations = $this->correlateMessageToExecutions($commandContext, $messageName, $correlationSet);

        if (count($correlations) > 1) {
            //throw LOG.exceptionCorrelateMessageToSingleExecution(messageName, correlations.size(), correlationSet);
            throw new \Exception("exceptionCorrelateMessageToSingleExecution");
        } elseif (count($correlations) == 1) {
            return $correlations[0];
        } elseif ($correlationSet->isExecutionsOnly()) {
            // no correlation to an execution found
            return null;
        }

        // then try to correlate to process definition
        $correlations = $this->correlateStartMessages($commandContext, $messageName, $correlationSet);

        if (count($correlations) > 1) {
            //throw LOG.exceptionCorrelateMessageToSingleProcessDefinition(messageName, correlations.size(), correlationSet);
            throw new \Exception("exceptionCorrelateMessageToSingleProcessDefinition");
        } elseif (count($correlations) == 1) {
            return $correlations[0];
        } else {
            return null;
        }
    }

    public function correlateMessages(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): array
    {
        $results = [];

        // first collect correlations to executions
        $results = array_merge($results, $this->correlateMessageToExecutions($commandContext, $messageName, $correlationSet));
        // now collect correlations to process definition, if enabled
        if (!$correlationSet->isExecutionsOnly()) {
            $results = array_merge($results, $this->correlateStartMessages($commandContext, $messageName, $correlationSet));
        }

        return $results;
    }

    protected function correlateMessageToExecutions(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): array
    {
        $query = new ExecutionQueryImpl();

        $correlationKeys = $correlationSet->getCorrelationKeys();
        if (!empty($correlationKeys)) {
            foreach ($correlationKeys as $key => $value) {
                $query->processVariableValueEquals($key, $value);
            }
        }

        $localCorrelationKeys = $correlationSet->getLocalCorrelationKeys();
        if (!empty($localCorrelationKeys)) {
            foreach ($localCorrelationKeys as $key => $value) {
                $query->variableValueEquals($key, $value);
            }
        }

        $businessKey = $correlationSet->getBusinessKey();
        if (!empty($businessKey)) {
            $query->processInstanceBusinessKey($businessKey);
        }

        $processInstanceId = $correlationSet->getProcessInstanceId();
        if (!empty($processInstanceId)) {
            $query->processInstanceId($processInstanceId);
        }

        if (!empty($messageName)) {
            $query->messageEventSubscriptionName($messageName);
        } else {
            $query->messageEventSubscription();
        }

        if ($correlationSet->isTenantIdSet) {
            $tenantId = $correlationSet->getTenantId();
            if (!empty($tenantId)) {
                $query->tenantIdIn($tenantId);
            } else {
                $query->withoutTenantId();
            }
        }

        // restrict to active executions
        $query->active();

        $matchingExecutions = $query->evaluateExpressionsAndExecuteList($commandContext, null);

        $result = [];

        foreach ($matchingExecutions as $matchingExecution) {
            $correlationResult = CorrelationHandlerResult::matchedExecution($matchingExecution);
            if (!$commandContext->getDbEntityManager()->isDeleted($correlationResult->getExecutionEntity())) {
                $result[] = $correlationResult;
            }
        }

        return $result;
    }

    public function correlateStartMessages(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): array
    {
        if ($messageName === null) {
            // ignore empty message name
            return [];
        }
        if ($correlationSet->getProcessDefinitionId() === null) {
            return $this->correlateStartMessageByEventSubscription($commandContext, $messageName, $correlationSet);
        } else {
            $correlationResult = $this->correlateStartMessageByProcessDefinitionId($commandContext, $messageName, $correlationSet->getProcessDefinitionId());
            if ($correlationResult !== null) {
                return [ $correlationResult ];
            } else {
                return [];
            }
        }
    }

    protected function correlateStartMessageByEventSubscription(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): array
    {
        $results = [];
        $deploymentCache = $commandContext->getProcessEngineConfiguration()->getDeploymentCache();
        $messageEventSubscriptions = $this->findMessageStartEventSubscriptions($commandContext, $messageName, correlationSet);
        foreach ($messageEventSubscriptions as $messageEventSubscription) {
            if ($messageEventSubscription->getConfiguration() !== null) {
                $processDefinitionId = $messageEventSubscription->getConfiguration();
                $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);
                // only an active process definition will be returned
                if ($processDefinition !== null && !$processDefinition->isSuspended()) {
                    $result = CorrelationHandlerResult::matchedProcessDefinition($processDefinition, $messageEventSubscription->getActivityId());
                    $results[] = $result;
                } else {
                    //LOG.couldNotFindProcessDefinitionForEventSubscription(messageEventSubscription, processDefinitionId);
                }
            }
        }
        return $results;
    }

    protected function findMessageStartEventSubscriptions(CommandContext $commandContext, ?string $messageName, CorrelationSet $correlationSet): ?array
    {
        $eventSubscriptionManager = $commandContext->getEventSubscriptionManager();

        if ($correlationSet->isTenantIdSet) {
            $eventSubscription = $eventSubscriptionManager->findMessageStartEventSubscriptionByNameAndTenantId($messageName, $correlationSet->getTenantId());
            if ($eventSubscription !== null) {
                return [ $eventSubscription ];
            } else {
                return [];
            }
        } else {
            return $eventSubscriptionManager->findMessageStartEventSubscriptionByName($messageName);
        }
    }

    protected function correlateStartMessageByProcessDefinitionId(CommandContext $commandContext, ?string $messageName, ?string $processDefinitionId): ?CorrelationHandlerResult
    {
        $deploymentCache = $commandContext->getProcessEngineConfiguration()->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);
        // only an active process definition will be returned
        if ($processDefinition !== null && !$processDefinition->isSuspended()) {
            $startActivityId = $this->findStartActivityIdByMessage($processDefinition, $messageName);
            if ($startActivityId !== null) {
                return CorrelationHandlerResult::matchedProcessDefinition($processDefinition, $startActivityId);
            }
        }
        return null;
    }

    protected function findStartActivityIdByMessage(ProcessDefinitionEntity $processDefinition, ?string $messageName): ?string
    {
        foreach (EventSubscriptionDeclaration::getDeclarationsForScope($processDefinition) as $declaration) {
            if ($this->isMessageStartEventWithName($declaration, $messageName)) {
                return $declaration->getActivityId();
            }
        }
        return null;
    }

    protected function isMessageStartEventWithName(EventSubscriptionDeclaration $declaration, ?string $messageName): bool
    {
        return EventType::message()->name() == $declaration->getEventType() &&
            $declaration->isStartEvent()
            && $messageName == $declaration->getUnresolvedEventName();
    }
}
