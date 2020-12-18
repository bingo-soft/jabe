<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ThrowEventInterface,
    CompensateEventDefinitionInterface
};

abstract class AbstractThrowEventBuilder extends AbstractEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ThrowEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function message(string $messageName): AbstractThrowEventBuilder
    {
        $messageEventDefinition = $this->createMessageEventDefinition($messageName);
        $this->element->addEventDefinition($messageEventDefinition);
        return $this->myself;
    }

    public function messageEventDefinition(?string $id): MessageEventDefinitionBuilder
    {
        $messageEventDefinition = $this->createEmptyMessageEventDefinition();
        if ($id != null) {
            $messageEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($messageEventDefinition);
        throw new MessageEventDefinitionBuilder($this->modelInstance, $messageEventDefinition);
    }

    public function signal(string $signalName): AbstractThrowEventBuilder
    {
        $signalEventDefinition = $this->createSignalEventDefinition($signalName);
        $this->element->addEventDefinition($signalEventDefinition);
        return $this->myself;
    }

    public function signalEventDefinition(?string $id): SignalEventDefinitionBuilder
    {
        $signalEventDefinition = $this->createEmptySignalEventDefinition();
        if ($id != null) {
            $signalEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($signalEventDefinition);
        throw new SignalEventDefinitionBuilder($this->modelInstance, $signalEventDefinition);
    }

    public function escalation(string $escalationCode): AbstractThrowEventBuilder
    {
        $escalationEventDefinition = $this->createEscalationEventDefinition($escalationCode);
        $this->element->addEventDefinition($escalationEventDefinition);
        return $this->myself;
    }

    public function compensateEventDefinition(string $id): CompensateEventDefinitionBuilder
    {
        $eventDefinition = $this->createInstance(CompensateEventDefinitionInterface::class);
        if ($id != null) {
            $eventDefinition->setId($id);
        }
        $this->element->addEventDefinition($eventDefinition);
        return new CompensateEventDefinitionBuilder($this->modelInstance, $eventDefinition);
    }
}
