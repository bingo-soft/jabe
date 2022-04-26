<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
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
        return $this;
    }

    public function messageEventDefinition(?string $id = null): MessageEventDefinitionBuilder
    {
        $messageEventDefinition = $this->createEmptyMessageEventDefinition();
        if ($id != null) {
            $messageEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($messageEventDefinition);
        return new MessageEventDefinitionBuilder($this->modelInstance, $messageEventDefinition);
    }

    public function signal(string $signalName): AbstractThrowEventBuilder
    {
        $signalEventDefinition = $this->createSignalEventDefinition($signalName);
        $this->element->addEventDefinition($signalEventDefinition);
        return $this;
    }

    public function signalEventDefinition(string $signalName): SignalEventDefinitionBuilder
    {
        $signalEventDefinition = $this->createSignalEventDefinition($signalName);
        $this->element->addEventDefinition($signalEventDefinition);
        return new SignalEventDefinitionBuilder($this->modelInstance, $signalEventDefinition);
    }

    public function escalation(string $escalationCode): AbstractThrowEventBuilder
    {
        $escalationEventDefinition = $this->createEscalationEventDefinition($escalationCode);
        $this->element->addEventDefinition($escalationEventDefinition);
        return $this;
    }

    public function compensateEventDefinition(?string $id = null): CompensateEventDefinitionBuilder
    {
        $eventDefinition = $this->createInstance(CompensateEventDefinitionInterface::class);
        if ($id != null) {
            $eventDefinition->setId($id);
        }
        $this->element->addEventDefinition($eventDefinition);
        return new CompensateEventDefinitionBuilder($this->modelInstance, $eventDefinition);
    }
}
