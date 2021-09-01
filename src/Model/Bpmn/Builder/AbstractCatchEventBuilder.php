<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    CatchEventInterface,
    CompensateEventDefinitionInterface,
    ConditionalEventDefinitionInterface
};

abstract class AbstractCatchEventBuilder extends AbstractEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CatchEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function parallelMultiple(): AbstractCatchEventBuilder
    {
        $this->element->isParallelMultiple();
        return $this;
    }

    public function message(string $message): AbstractCatchEventBuilder
    {
        $messageEventDefinition = $this->createMessageEventDefinition($messageName);
        $this->element->addEventDefinition($messageEventDefinition);
        return $this;
    }

    public function signal(string $signalName): AbstractCatchEventBuilder
    {
        $signalEventDefinition = $this->createSignalEventDefinition($signalName);
        $this->element->addEventDefinition($signalEventDefinition);
        return $this;
    }

    public function timerWithDate(string $timerDate): AbstractCatchEventBuilder
    {
        $this->element->addEventDefinition($this->createTimeDate($timerDate));
        return $this;
    }

    public function timerWithDuration(string $timerDuration): AbstractCatchEventBuilder
    {
        $this->element->addEventDefinition($this->createTimeDuration($timerDuration));
        return $this;
    }

    public function timerWithCycle(string $timerCycle): AbstractCatchEventBuilder
    {
        $this->element->addEventDefinition($this->createTimeCycle($timerCycle));
        return $this;
    }

    public function compensateEventDefinition(?string $id): CompensateEventDefinitionBuilder
    {
        $eventDefinition = $this->createInstance(CompensateEventDefinitionInterface::class);
        if ($id != null) {
            $eventDefinition->setId($id);
        }
        $this->element->addEventDefinition($eventDefinition);
        return new CompensateEventDefinitionBuilder($this->modelInstance, $eventDefinition);
    }

    public function conditionalEventDefinition(?string $id): ConditionalEventDefinitionBuilder
    {
        $eventDefinition = $this->createInstance(ConditionalEventDefinitionInterface::class);
        if ($id != null) {
            $eventDefinition->setId($id);
        }
        $this->element->addEventDefinition($eventDefinition);
        return new ConditionalEventDefinitionBuilder($this->modelInstance, $eventDefinition);
    }

    public function condition(): AbstractCatchEventBuilder
    {
        $this->conditionalEventDefinition()->condition($condition);
        return $this;
    }
}
