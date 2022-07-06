<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Engine\Impl\Core\Model\CallableElement;
use Jabe\Engine\Impl\El\{
    ExpressionInterface,
    StartProcessVariableScope
};
use Jabe\Engine\Impl\Event\EventType;
use Jabe\Engine\Impl\JobExecutor\EventSubscriptionJobDeclaration;
use Jabe\Engine\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Engine\Impl\Pvm\PvmScopeInterface;
use Jabe\Engine\Impl\Pvm\Runtime\LegacyBehavior;

class EventSubscriptionDeclaration
{
    protected $eventType;
    protected $eventName;
    protected $eventPayload;
    protected $async;
    protected $activityId = null;
    protected $eventScopeActivityId = null;
    protected $isStartEvent;
    protected $jobDeclaration = null;

    public function __construct(ExpressionInterface $eventExpression, EventType $eventType, CallableElement $eventPayload = null)
    {
        $this->eventType = $eventType;
        $this->eventName = $eventExpression;
        $this->eventPayload = $eventPayload;
    }

    public static function getDeclarationsForScope(?PvmScopeInterface $scope): array
    {
        if ($scope === null) {
            return [];
        }

        return $scope->getProperties()->get(BpmnProperties::eventSubscriptionDeclarations());
    }

    /**
     * Returns the name of the event without evaluating the possible expression that it might contain.
     */
    public function getUnresolvedEventName(): string
    {
        return $this->eventName->getExpressionText();
    }

    public function hasEventName(): bool
    {
        return !( $this->eventName === null || "" == trim($this->getUnresolvedEventName()) );
    }

    public function isEventNameLiteralText(): bool
    {
        return $this->eventName->isLiteralText();
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getEventScopeActivityId(): string
    {
        return $this->eventScopeActivityId;
    }

    public function setEventScopeActivityId(string $eventScopeActivityId): void
    {
        $this->eventScopeActivityId = $eventScopeActivityId;
    }

    public function isStartEvent(): bool
    {
        return $this->isStartEvent;
    }

    public function setStartEvent(bool $isStartEvent): void
    {
        $this->isStartEvent = $isStartEvent;
    }

    public function getEventType(): string
    {
        return $this->eventType->name();
    }

    public function getEventPayload(): CallableElement
    {
        return $this->eventPayload;
    }

    public function setJobDeclaration(EventSubscriptionJobDeclaration $jobDeclaration): void
    {
        $this->jobDeclaration = $jobDeclaration;
    }

    public function createSubscriptionForStartEvent(ProcessDefinitionEntity $processDefinition): EventSubscriptionEntity
    {
        $eventSubscriptionEntity = new EventSubscriptionEntity($this->eventType);

        $scopeForExpression = StartProcessVariableScope::getSharedInstance();
        $eventName = $this->resolveExpressionOfEventName($scopeForExpression);
        $eventSubscriptionEntity->setEventName($eventName);
        $eventSubscriptionEntity->setActivityId($this->activityId);
        $eventSubscriptionEntity->setConfiguration($processDefinition->getId());
        $eventSubscriptionEntity->setTenantId($processDefinition->getTenantId());

        return $eventSubscriptionEntity;
    }

    /**
     * Creates and inserts a subscription entity depending on the message type of this declaration.
     */
    public function createSubscriptionForExecution(ExecutionEntity $execution): EventSubscriptionEntity
    {
        $eventSubscriptionEntity = new EventSubscriptionEntity($execution, $this->eventType);

        $eventName = $this->resolveExpressionOfEventName($execution);
        $eventSubscriptionEntity->setEventName($eventName);
        if ($this->activityId !== null) {
            $activity = $execution->getProcessDefinition()->findActivity($this->activityId);
            $eventSubscriptionEntity->setActivity($activity);
        }

        $eventSubscriptionEntity->insert();
        LegacyBehavior::removeLegacySubscriptionOnParent($execution, $eventSubscriptionEntity);

        return $eventSubscriptionEntity;
    }

    /**
     * Resolves the event name within the given scope.
     */
    public function resolveExpressionOfEventName(VariableScopeInterface $scope): ?string
    {
        if ($this->isExpressionAvailable()) {
            return $this->eventName->getValue($scope);
        } else {
            return null;
        }
    }

    protected function isExpressionAvailable(): bool
    {
        return $this->eventName !== null;
    }

    public function updateSubscription(EventSubscriptionEntity $eventSubscription): void
    {
        $eventName = $this->resolveExpressionOfEventName($eventSubscription->getExecution());
        $eventSubscription->setEventName($this->eventName);
        $eventSubscription->setActivityId($this->activityId);
    }
}
