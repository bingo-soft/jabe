<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Bpmn\Parser\{
    BpmnParse,
    EventSubscriptionDeclaration
};
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\{
    EventSubscriptionEntity,
    ExecutionEntity,
    JobEntity,
    MessageEntity,
    ProcessDefinitionEntity
};
use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Util\EnsureUtil;

class EventSubscriptionJobDeclaration extends JobDeclaration
{
    protected $eventSubscriptionDeclaration;

    public function __construct(EventSubscriptionDeclaration $eventSubscriptionDeclaration)
    {
        parent::__construct(ProcessEventJobHandler::TYPE);
        EnsureUtil::ensureNotNull("eventSubscriptionDeclaration", "eventSubscriptionDeclaration", $eventSubscriptionDeclaration);
        $this->eventSubscriptionDeclaration = $eventSubscriptionDeclaration;
    }

    protected function newJobInstance($eventSubscription = null): JobEntity
    {
        $message = new MessageEntity();

        // initialize job
        $message->setActivityId($eventSubscription->getActivityId());
        $message->setExecutionId($eventSubscription->getExecutionId());
        $message->setProcessInstanceId($eventSubscription->getProcessInstanceId());

        $processDefinition = $eventSubscription->getProcessDefinition();

        if ($processDefinition !== null) {
            $message->setProcessDefinitionId($processDefinition->getId());
            $message->setProcessDefinitionKey($processDefinition->getKey());
        }

        // TODO: support payload
        // if(payload !== null) {
        //   message.setEventPayload(payload);
        // }

        return $message;
    }

    public function getEventType(): string
    {
        return $this->eventSubscriptionDeclaration->getEventType();
    }

    public function getEventName(): string
    {
        return $this->eventSubscriptionDeclaration->getUnresolvedEventName();
    }

    public function getActivityId(): string
    {
        return $this->eventSubscriptionDeclaration->getActivityId();
    }

    protected function resolveExecution(/*EventSubscriptionEntity*/$context): ?ExecutionEntity
    {
        return $context->getExecution();
    }

    protected function resolveJobHandlerConfiguration(/*EventSubscriptionEntity*/$context): JobHandlerConfigurationInterface
    {
        return new EventSubscriptionJobConfiguration($context->getId());
    }

    public static function getDeclarationsForActivity(PvmActivityInterface $activity): array
    {
        $result = $activity->getProperty(BpmnParse::PROPERTYNAME_EVENT_SUBSCRIPTION_JOB_DECLARATION);
        if ($result !== null) {
            return $result;
        } else {
            return [];
        }
    }

    /**
     * Assumes that an activity has at most one declaration of a certain eventType.
     */
    public static function findDeclarationForSubscription(EventSubscriptionEntity $eventSubscription): ?EventSubscriptionJobDeclaration
    {
        $declarations = self::getDeclarationsForActivity($eventSubscription->getActivity());

        foreach ($declarations as $declaration) {
            if ($declaration->getEventType() == $eventSubscription->getEventType()) {
                return $declaration;
            }
        }

        return null;
    }
}
