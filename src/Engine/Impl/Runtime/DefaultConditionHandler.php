<?php

namespace Jabe\Engine\Impl\Runtime;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Engine\Impl\Bpmn\Parser\{
    ConditionalEventDefinition,
    EventSubscriptionDeclaration
};
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    EventSubscriptionManager,
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Engine\Impl\Pvm\Process\ActivityImpl;

class DefaultConditionHandler implements ConditionHandlerInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function evaluateStartCondition(CommandContext $commandContext, ConditionSet $conditionSet): array
    {
        if ($conditionSet->getProcessDefinitionId() === null) {
            return $this->evaluateConditionStartByEventSubscription($commandContext, $conditionSet);
        } else {
            return $this->evaluateConditionStartByProcessDefinitionId($commandContext, $conditionSet, $conditionSet->getProcessDefinitionId());
        }
    }

    protected function evaluateConditionStartByEventSubscription(CommandContext $commandContext, ConditionSet $conditionSet): array
    {
        $subscriptions = $this->findConditionalStartEventSubscriptions($commandContext, $conditionSet);
        if (empty($subscriptions)) {
            //throw LOG.exceptionWhenEvaluatingConditionalStartEvent();
        }
        $results = [];
        foreach ($subscriptions as $subscription) {
            $processDefinition = $subscription->getProcessDefinition();
            if (!$processDefinition->isSuspended()) {
                $activity = $subscription->getActivity();

                if ($this->evaluateCondition($conditionSet, $activity)) {
                    $results[] = new ConditionHandlerResult($processDefinition, $activity);
                }
            }
        }

        return $results;
    }

    protected function findConditionalStartEventSubscriptions(CommandContext $commandContext, ConditionSet $conditionSet): array
    {
        $eventSubscriptionManager = $commandContext->getEventSubscriptionManager();

        if ($conditionSet->isTenantIdSet()) {
            return $eventSubscriptionManager->findConditionalStartEventSubscriptionByTenantId($conditionSet->getTenantId());
        } else {
            return $eventSubscriptionManager->findConditionalStartEventSubscription();
        }
    }

    protected function evaluateConditionStartByProcessDefinitionId(
        CommandContext $commandContext,
        ConditionSet $conditionSet,
        string $processDefinitionId
    ): array {
        $deploymentCache = $commandContext->getProcessEngineConfiguration()->getDeploymentCache();
        $processDefinition = $deploymentCache->findDeployedProcessDefinitionById($processDefinitionId);

        $results = [];

        if ($processDefinition !== null && !$processDefinition->isSuspended()) {
            $activities = $this->findConditionalStartEventActivities($processDefinition);
            if (empty($activities)) {
                //throw LOG.exceptionWhenEvaluatingConditionalStartEventByProcessDefinition(processDefinitionId);
            }
            foreach ($activities as $activity) {
                if ($this->evaluateCondition($conditionSet, $activity)) {
                    $results[] = new ConditionHandlerResult($processDefinition, $activity);
                }
            }
        }
        return $results;
    }

    protected function findConditionalStartEventActivities(ProcessDefinitionEntity $processDefinition): array
    {
        $activities = [];
        //TODO. Check it out!
        foreach (array_values(ConditionalEventDefinition::getDeclarationsForScope($processDefinition)) as $declaration) {
            if ($this->isConditionStartEvent($declaration)) {
                $activities[] = $declaration->getConditionalActivity();
            }
        }
        return $activities;
    }

    protected function isConditionStartEvent(EventSubscriptionDeclaration $declaration): bool
    {
        return EventType::conditional()->name() == $declaration->getEventType() && $declaration->isStartEvent();
    }

    protected function evaluateCondition(ConditionSet $conditionSet, ActivityImpl $activity): bool
    {
        $temporaryExecution = new ExecutionEntity();
        if (!empty($conditionSet->getVariables())) {
            $temporaryExecution->initializeVariableStore($conditionSet->getVariables());
        }
        $temporaryExecution->setProcessDefinition($activity->getProcessDefinition());

        $conditionalEventDefinition = $activity->getProperties()->get(BpmnProperties::conditionalEventDefinition());
        if (empty($conditionalEventDefinition->getVariableName()) || array_key_exists($conditionalEventDefinition->getVariableName(), $conditionSet->getVariables())) {
            return $conditionalEventDefinition->tryEvaluate($temporaryExecution);
        } else {
            return false;
        }
    }
}
